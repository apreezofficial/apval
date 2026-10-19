<?php

declare(strict_types=1);

namespace App\Services\Chat;

use App\Exceptions\Domain\InvalidRequestException;
use App\Models\ChatReport;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Message;
use App\Models\MessageReaction;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\UserBlock;
use App\Services\AI\AgrinovaOrchestrator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ChatService
{
    public function __construct(
        private ?AgrinovaOrchestrator $agrinova = null
    ) {}

    /**
     * Get paginated conversations for a user, segmented by tabs (all, direct, communities, marketplace, support)
     */
    public function getUserConversations(User $user, string $tab = 'all', ?string $search = null, int $perPage = 20): LengthAwarePaginator
    {
        $query = Conversation::with([
            'participants.kycRecord',
            'community',
            'product.images',
            'order',
            'latestMessage.sender'
        ])
        ->whereHas('participantRecords', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        });

        // Tab filter
        match ($tab) {
            'direct'      => $query->where('type', 'direct')->where(function ($q) {
                $q->whereNull('category')->orWhere('category', 'direct');
            }),
            'communities', 'community' => $query->where(function ($q) {
                $q->where('type', 'community')->orWhere('category', 'community');
            }),
            'marketplace' => $query->where('category', 'marketplace'),
            'order'       => $query->where('category', 'order'),
            'support'     => $query->where(function ($q) {
                $q->where('type', 'support')->orWhere('category', 'support');
            }),
            default       => null,
        };

        // Search filter
        if ($search) {
            $term = '%' . $search . '%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                  ->orWhereHas('participants', function ($pq) use ($term) {
                      $pq->where('first_name', 'like', $term)
                         ->orWhere('last_name', 'like', $term);
                  })
                  ->orWhereHas('messages', function ($mq) use ($term) {
                      $mq->where('body', 'like', $term);
                  });
            });
        }

        $conversations = $query->orderByDesc(
            DB::raw('COALESCE(conversations.last_message_at, conversations.updated_at)')
        )->paginate($perPage);

        // Append custom presentation attributes
        $conversations->getCollection()->transform(function (Conversation $conv) use ($user) {
            $myParticipant = $conv->participantRecords->firstWhere('user_id', $user->id);
            $lastRead = $myParticipant?->last_read_at;

            $unreadCount = Message::where('conversation_id', $conv->id)
                ->when($lastRead, function ($q) use ($lastRead) {
                    $q->where('created_at', '>', $lastRead);
                })
                ->where('sender_id', '!=', $user->id)
                ->where('is_deleted', false)
                ->count();

            $otherUser = $conv->participants->firstWhere('id', '!=', $user->id);
            $displayName = $conv->name;
            $displayImage = null;
            $isVerified = false;

            if ($conv->community) {
                $displayName = $conv->community->name;
                $displayImage = $conv->community->image_url;
            } elseif ($otherUser) {
                $displayName = trim($otherUser->first_name . ' ' . $otherUser->last_name);
                $displayImage = $otherUser->profile_photo_url ?? null;
                $isVerified = (bool) ($otherUser->kycRecord?->bvn_verified ?? false);
            }

            $conv->setAttribute('display_name', $displayName ?: 'Conversation');
            $conv->setAttribute('display_image', $displayImage);
            $conv->setAttribute('is_verified', $isVerified);
            $conv->setAttribute('unread_count', $unreadCount);

            return $conv;
        });

        return $conversations;
    }

    /**
     * Start or retrieve an existing direct conversation with another user.
     */
    public function getOrCreateDirectConversation(User $user, string $recipientId): Conversation
    {
        if ($user->id === $recipientId) {
            throw new InvalidRequestException('You cannot start a conversation with yourself.');
        }

        User::findOrFail($recipientId);
        $this->assertNotBlocked($user->id, $recipientId);

        return DB::transaction(function () use ($user, $recipientId) {
            $existingId = ConversationParticipant::where('user_id', $user->id)
                ->whereHas('conversation', function ($query) {
                    $query->where('type', 'direct')->where(function ($cq) {
                        $cq->whereNull('category')->orWhere('category', 'direct');
                    });
                })
                ->pluck('conversation_id')
                ->first(function ($convId) use ($recipientId) {
                    return ConversationParticipant::where('conversation_id', $convId)
                        ->where('user_id', $recipientId)
                        ->exists();
                });

            if ($existingId) {
                return Conversation::with(['participants', 'latestMessage'])->findOrFail($existingId);
            }

            $conversation = Conversation::create([
                'id' => (string) Str::uuid(),
                'type' => 'direct',
                'category' => 'direct',
                'created_by' => $user->id,
            ]);

            ConversationParticipant::create([
                'id' => (string) Str::uuid(),
                'conversation_id' => $conversation->id,
                'user_id' => $user->id,
                'role' => 'owner',
            ]);

            ConversationParticipant::create([
                'id' => (string) Str::uuid(),
                'conversation_id' => $conversation->id,
                'user_id' => $recipientId,
                'role' => 'member',
            ]);

            return $conversation->load(['participants', 'latestMessage']);
        });
    }

    /**
     * Start or retrieve a marketplace conversation between a buyer and seller regarding a specific product.
     */
    public function getOrCreateMarketplaceConversation(User $buyer, string $productId): Conversation
    {
        $product = Product::with('vendor')->findOrFail($productId);
        $sellerId = $product->vendor_id;

        if ($buyer->id === $sellerId) {
            throw new InvalidRequestException('You cannot start a chat about your own product.');
        }

        $this->assertNotBlocked($buyer->id, $sellerId);

        return DB::transaction(function () use ($buyer, $sellerId, $product) {
            $existing = Conversation::where('category', 'marketplace')
                ->where('product_id', $product->id)
                ->whereHas('participantRecords', function ($q) use ($buyer) {
                    $q->where('user_id', $buyer->id);
                })
                ->first();

            if ($existing) {
                return $existing->load(['participants', 'product.images', 'latestMessage']);
            }

            $conversation = Conversation::create([
                'id' => (string) Str::uuid(),
                'type' => 'direct',
                'category' => 'marketplace',
                'name' => 'Inquiry: ' . $product->name,
                'product_id' => $product->id,
                'created_by' => $buyer->id,
            ]);

            ConversationParticipant::create([
                'id' => (string) Str::uuid(),
                'conversation_id' => $conversation->id,
                'user_id' => $buyer->id,
                'role' => 'owner',
            ]);

            ConversationParticipant::create([
                'id' => (string) Str::uuid(),
                'conversation_id' => $conversation->id,
                'user_id' => $sellerId,
                'role' => 'member',
            ]);

            // Auto-inject a Product Card message into the chat
            $this->sendMessage($buyer, $conversation->id, [
                'type' => 'product_card',
                'product_id' => $product->id,
                'body' => "Hello! I am interested in {$product->name}.",
            ]);

            return $conversation->load(['participants', 'product.images', 'latestMessage']);
        });
    }

    /**
     * Start or retrieve an order-specific conversation.
     */
    public function getOrCreateOrderConversation(User $user, string $orderId): Conversation
    {
        $order = Order::with(['items.product'])->findOrFail($orderId);

        // Only buyer or vendor of the order can initiate
        $isBuyer = ($order->user_id === $user->id);
        $vendorIds = $order->items->pluck('vendor_id')->unique()->all();
        $isVendor = in_array($user->id, $vendorIds, true);

        if (!$isBuyer && !$isVendor && !in_array($user->role ?? '', ['admin', 'super_admin'], true)) {
            throw new InvalidRequestException('You are not authorized to access this order chat.');
        }

        return DB::transaction(function () use ($user, $order, $vendorIds) {
            $existing = Conversation::where('category', 'order')
                ->where('order_id', $order->id)
                ->first();

            if ($existing) {
                return $existing->load(['participants', 'order', 'latestMessage']);
            }

            $conversation = Conversation::create([
                'id' => (string) Str::uuid(),
                'type' => 'group',
                'category' => 'order',
                'name' => 'Order #' . substr($order->id, 0, 8),
                'order_id' => $order->id,
                'created_by' => $user->id,
            ]);

            // Add buyer
            ConversationParticipant::create([
                'id' => (string) Str::uuid(),
                'conversation_id' => $conversation->id,
                'user_id' => $order->user_id,
                'role' => 'member',
            ]);

            // Add vendor(s)
            foreach ($vendorIds as $vId) {
                if ($vId && $vId !== $order->user_id) {
                    ConversationParticipant::firstOrCreate(
                        ['conversation_id' => $conversation->id, 'user_id' => $vId],
                        ['id' => (string) Str::uuid(), 'role' => 'member']
                    );
                }
            }

            // Auto-inject Order Card
            $this->sendMessage($user, $conversation->id, [
                'type' => 'order_card',
                'order_id' => $order->id,
                'body' => "Order #{$order->id} discussion started.",
            ]);

            return $conversation->load(['participants', 'order', 'latestMessage']);
        });
    }

    /**
     * Start or retrieve AgreonPay Customer Support conversation.
     */
    public function getOrCreateSupportConversation(User $user): Conversation
    {
        return DB::transaction(function () use ($user) {
            $existing = Conversation::where('category', 'support')
                ->whereHas('participantRecords', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                })
                ->first();

            if ($existing) {
                return $existing->load(['participants', 'latestMessage']);
            }

            $conversation = Conversation::create([
                'id' => (string) Str::uuid(),
                'type' => 'support',
                'category' => 'support',
                'name' => 'AgreonPay Official Support',
                'created_by' => $user->id,
            ]);

            ConversationParticipant::create([
                'id' => (string) Str::uuid(),
                'conversation_id' => $conversation->id,
                'user_id' => $user->id,
                'role' => 'member',
            ]);

            // Welcome greeting from Support
            Message::create([
                'id' => (string) Str::uuid(),
                'conversation_id' => $conversation->id,
                'sender_id' => $user->id,
                'type' => 'text',
                'body' => 'Welcome to AgreonPay Official Support! How can we assist you today?',
                'metadata' => ['is_system_greeting' => true],
            ]);

            return $conversation->load(['participants', 'latestMessage']);
        });
    }

    /**
     * Send a rich message inside any conversation.
     */
    public function sendMessage(User $sender, string $conversationId, array $payload): Message
    {
        return DB::transaction(function () use ($sender, $conversationId, $payload) {
            $conversation = Conversation::findOrFail($conversationId);

            // Ensure sender is participant or admin
            $participant = ConversationParticipant::where('conversation_id', $conversationId)
                ->where('user_id', $sender->id)
                ->first();

            if (!$participant) {
                throw new InvalidRequestException('You are not a member of this conversation.');
            }

            if ($participant->is_muted && ($participant->muted_until === null || $participant->muted_until->isFuture())) {
                throw new InvalidRequestException('You are currently muted in this community.');
            }

            if ($conversation->is_announcement_only && !in_array($participant->role, ['admin', 'moderator', 'owner'], true)) {
                throw new InvalidRequestException('Only community admins can send messages in this announcement channel.');
            }

            // Anti-scam warning detection
            $metadata = $payload['metadata'] ?? [];
            $body = $payload['body'] ?? '';
            $scamWarning = $this->detectScamRisk($body);
            if ($scamWarning) {
                $metadata['safety_alert'] = [
                    'title' => 'Stay Safe on AgreonPay',
                    'message' => 'Never transfer money outside AgreonPay. Protect your transactions with AgreonPay Escrow.'
                ];
            }

            $message = Message::create([
                'id' => (string) Str::uuid(),
                'conversation_id' => $conversation->id,
                'sender_id' => $sender->id,
                'body' => $body,
                'type' => $payload['type'] ?? 'text',
                'media_url' => $payload['media_url'] ?? null,
                'media_duration' => $payload['media_duration'] ?? null,
                'file_name' => $payload['file_name'] ?? null,
                'file_size' => $payload['file_size'] ?? null,
                'product_id' => $payload['product_id'] ?? null,
                'order_id' => $payload['order_id'] ?? null,
                'location_data' => $payload['location_data'] ?? null,
                'reply_to_id' => $payload['reply_to_id'] ?? null,
                'metadata' => !empty($metadata) ? $metadata : null,
                'delivered_at' => now(),
            ]);

            // Update conversation and participant read timestamps
            $conversation->update(['last_message_at' => now()]);
            $participant->update(['last_read_at' => now()]);

            // Trigger Agrinova AI in chat if mentioned
            if ($body && str_contains(strtolower($body), '@agrinova')) {
                $this->handleAgrinovaMention($sender, $conversation, $body);
            }

            return $message->load(['sender', 'replyTo.sender', 'product', 'order', 'reactions']);
        });
    }

    /**
     * Toggle emoji reaction on a message (heart, thumbs up, etc.)
     */
    public function toggleReaction(User $user, string $messageId, string $emoji): array
    {
        $message = Message::findOrFail($messageId);

        // Ensure user is participant
        ConversationParticipant::where('conversation_id', $message->conversation_id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $existing = MessageReaction::where('message_id', $messageId)
            ->where('user_id', $user->id)
            ->where('emoji', $emoji)
            ->first();

        if ($existing) {
            $existing->delete();
            $action = 'removed';
        } else {
            MessageReaction::create([
                'id' => (string) Str::uuid(),
                'message_id' => $messageId,
                'user_id' => $user->id,
                'emoji' => $emoji,
            ]);
            $action = 'added';
        }

        $reactions = MessageReaction::where('message_id', $messageId)
            ->select('emoji', DB::raw('count(*) as count'))
            ->groupBy('emoji')
            ->get();

        return [
            'action' => $action,
            'emoji' => $emoji,
            'summary' => $reactions,
        ];
    }

    /**
     * Pin or unpin a message.
     */
    public function pinMessage(User $user, string $messageId, bool $pin = true): Message
    {
        $message = Message::findOrFail($messageId);
        $participant = ConversationParticipant::where('conversation_id', $message->conversation_id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        // If in community, only admin/moderator can pin
        if ($message->conversation->community_id) {
            if (!in_array($participant->role, ['admin', 'moderator', 'owner'], true)) {
                throw new InvalidRequestException('Only admins or moderators can pin messages.');
            }
        }

        $message->update([
            'is_pinned' => $pin,
            'pinned_at' => $pin ? now() : null,
            'pinned_by' => $pin ? $user->id : null,
        ]);

        return $message->load('pinnedBy');
    }

    /**
     * Delete/soft-delete a message.
     */
    public function deleteMessage(User $user, string $messageId): void
    {
        $message = Message::findOrFail($messageId);

        // User can delete their own message; admin can delete any message
        $isSender = ($message->sender_id === $user->id);
        $participant = ConversationParticipant::where('conversation_id', $message->conversation_id)
            ->where('user_id', $user->id)
            ->first();

        $isAdmin = in_array($participant?->role ?? '', ['admin', 'moderator', 'owner'], true);

        if (!$isSender && !$isAdmin) {
            throw new InvalidRequestException('You are not authorized to delete this message.');
        }

        $message->update([
            'is_deleted' => true,
            'body' => 'This message was deleted.',
        ]);
        $message->delete();
    }

    /**
     * Block another user in chat.
     */
    public function blockUser(User $blocker, string $targetUserId): UserBlock
    {
        if ($blocker->id === $targetUserId) {
            throw new InvalidRequestException('You cannot block yourself.');
        }

        User::findOrFail($targetUserId);

        return UserBlock::firstOrCreate([
            'blocker_id' => $blocker->id,
            'blocked_id' => $targetUserId,
        ], [
            'id' => (string) Str::uuid(),
        ]);
    }

    /**
     * Unblock a user.
     */
    public function unblockUser(User $blocker, string $targetUserId): void
    {
        UserBlock::where('blocker_id', $blocker->id)
            ->where('blocked_id', $targetUserId)
            ->delete();
    }

    /**
     * Report a message, user, or conversation.
     */
    public function submitReport(User $reporter, array $data): ChatReport
    {
        return ChatReport::create([
            'id' => (string) Str::uuid(),
            'reporter_id' => $reporter->id,
            'reported_user_id' => $data['reported_user_id'] ?? null,
            'conversation_id' => $data['conversation_id'] ?? null,
            'message_id' => $data['message_id'] ?? null,
            'reason' => $data['reason'],
            'details' => $data['details'] ?? null,
            'status' => 'pending',
        ]);
    }

    /**
     * Get paginated messages for a conversation.
     */
    public function getMessages(User $user, string $conversationId, int $perPage = 30): LengthAwarePaginator
    {
        $participant = ConversationParticipant::where('conversation_id', $conversationId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        // Update last_read_at
        $participant->update(['last_read_at' => now()]);

        return Message::with([
            'sender:id,first_name,last_name,role',
            'sender.kycRecord:id,user_id,bvn_verified',
            'replyTo.sender:id,first_name,last_name',
            'product.images',
            'order',
            'reactions.user:id,first_name,last_name'
        ])
        ->where('conversation_id', $conversationId)
        ->latest()
        ->paginate($perPage);
    }

    /**
     * Anti-scam keyword detector.
     */
    private function detectScamRisk(string $text): bool
    {
        $scamPatterns = [
            'send money outside',
            'pay outside',
            'direct to my account',
            'personal bank account',
            'whatsapp me to pay',
            'telegram me to pay',
            'dont use escrow',
            'pay outside agreonpay',
        ];

        $lower = strtolower($text);
        foreach ($scamPatterns as $pattern) {
            if (str_contains($lower, $pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Handle @Agrinova AI assistant in agricultural conversations.
     */
    private function handleAgrinovaMention(User $sender, Conversation $conversation, string $body): void
    {
        try {
            $orchestrator = $this->agrinova ?? app(AgrinovaOrchestrator::class);
            $cleanPrompt = trim(str_ireplace('@agrinova', '', $body));

            if (!empty($cleanPrompt)) {
                $response = $orchestrator->handleChat($sender, $cleanPrompt, null, [
                    'source' => 'chatroom',
                    'conversation_id' => $conversation->id,
                ]);

                $botReply = $response['reply'] ?? null;
                if ($botReply) {
                    Message::create([
                        'id' => (string) Str::uuid(),
                        'conversation_id' => $conversation->id,
                        'sender_id' => $sender->id, // or system bot account
                        'type' => 'text',
                        'body' => "🤖 **Agrinova AI**:\n" . $botReply,
                        'metadata' => [
                            'is_agrinova_response' => true,
                            'tool_calls' => $response['tool_calls'] ?? [],
                        ],
                        'delivered_at' => now(),
                    ]);
                }
            }
        } catch (\Throwable $e) {
            // Non-blocking fallback for AI
        }
    }

    /**
     * Assert two users have not blocked each other.
     */
    private function assertNotBlocked(string $userA, string $userB): void
    {
        $blocked = UserBlock::where(function ($q) use ($userA, $userB) {
            $q->where('blocker_id', $userA)->where('blocked_id', $userB);
        })->orWhere(function ($q) use ($userA, $userB) {
            $q->where('blocker_id', $userB)->where('blocked_id', $userA);
        })->exists();

        if ($blocked) {
            throw new InvalidRequestException('Communication between these accounts is restricted.');
        }
    }
}
