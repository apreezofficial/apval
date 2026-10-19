<?php

declare(strict_types=1);

namespace App\Services\Chat;

use App\Models\ChatConversation;
use App\Models\ChatConversationParticipant;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ConversationService
{
    /**
     * Create or get a direct message conversation between two users
     */
    public function getOrCreateDirectConversation(string $userId, string $recipientId): ChatConversation
    {
        // Check if conversation already exists
        $existingConversation = ChatConversation::where('type', 'direct')
            ->whereHas('participants', function ($q) use ($userId) {
                $q->where('user_id', $userId)->whereNull('left_at');
            })
            ->whereHas('participants', function ($q) use ($recipientId) {
                $q->where('user_id', $recipientId)->whereNull('left_at');
            })
            ->first();

        if ($existingConversation) {
            return $existingConversation;
        }

        // Create new conversation
        return DB::transaction(function () use ($userId, $recipientId) {
            $conversation = ChatConversation::create([
                'id' => (string) Str::uuid(),
                'type' => 'direct',
            ]);

            // Add both participants
            $conversation->participants()->create([
                'user_id' => $userId,
                'role' => 'member',
                'joined_at' => now(),
            ]);

            $conversation->participants()->create([
                'user_id' => $recipientId,
                'role' => 'member',
                'joined_at' => now(),
            ]);

            return $conversation;
        });
    }

    /**
     * Create a marketplace conversation (buyer-seller chat from product page)
     */
    public function getOrCreateMarketplaceConversation(
        string $buyerId,
        string $sellerId,
        string $productId
    ): ChatConversation {
        // Check if conversation exists for this buyer-seller-product combo
        $existingConversation = ChatConversation::where('type', 'marketplace')
            ->where('related_product_id', $productId)
            ->whereHas('participants', function ($q) use ($buyerId) {
                $q->where('user_id', $buyerId)->whereNull('left_at');
            })
            ->whereHas('participants', function ($q) use ($sellerId) {
                $q->where('user_id', $sellerId)->whereNull('left_at');
            })
            ->first();

        if ($existingConversation) {
            return $existingConversation;
        }

        // Create new marketplace conversation
        return DB::transaction(function () use ($buyerId, $sellerId, $productId) {
            $product = Product::findOrFail($productId);
            
            $conversation = ChatConversation::create([
                'id' => (string) Str::uuid(),
                'type' => 'marketplace',
                'related_product_id' => $productId,
                'title' => "Product Inquiry: {$product->name}",
            ]);

            $conversation->participants()->create([
                'user_id' => $buyerId,
                'role' => 'member',
                'joined_at' => now(),
            ]);

            $conversation->participants()->create([
                'user_id' => $sellerId,
                'role' => 'member',
                'joined_at' => now(),
            ]);

            return $conversation;
        });
    }

    /**
     * Create an order-related conversation
     */
    public function createOrderConversation(Order $order): ChatConversation
    {
        return DB::transaction(function () use ($order) {
            $conversation = ChatConversation::create([
                'id' => (string) Str::uuid(),
                'type' => 'order',
                'related_order_id' => $order->id,
                'title' => "Order #{$order->id}",
            ]);

            // Add buyer
            $conversation->participants()->create([
                'user_id' => $order->user_id,
                'role' => 'member',
                'joined_at' => now(),
            ]);

            // Add all vendors from order items
            $vendorIds = $order->items()->pluck('vendor_id')->unique();
            foreach ($vendorIds as $vendorId) {
                $conversation->participants()->create([
                    'user_id' => $vendorId,
                    'role' => 'member',
                    'joined_at' => now(),
                ]);
            }

            return $conversation;
        });
    }

    /**
     * Create a support conversation
     */
    public function createSupportConversation(string $userId): ChatConversation
    {
        return DB::transaction(function () use ($userId) {
            $conversation = ChatConversation::create([
                'id' => (string) Str::uuid(),
                'type' => 'support',
                'title' => 'Support Chat',
            ]);

            $conversation->participants()->create([
                'user_id' => $userId,
                'role' => 'member',
                'joined_at' => now(),
            ]);

            // Add support system user (you'll need to create this)
            // For now, we'll skip this or use a placeholder
            // $conversation->participants()->create([
            //     'user_id' => config('agreonpay.support_user_id'),
            //     'role' => 'admin',
            //     'joined_at' => now(),
            // ]);

            return $conversation;
        });
    }

    /**
     * Create a group conversation
     */
    public function createGroupConversation(
        string $creatorId,
        string $title,
        array $participantIds,
        ?string $description = null
    ): ChatConversation {
        return DB::transaction(function () use ($creatorId, $title, $participantIds, $description) {
            $conversation = ChatConversation::create([
                'id' => (string) Str::uuid(),
                'type' => 'group',
                'title' => $title,
                'description' => $description,
            ]);

            // Add creator as admin
            $conversation->participants()->create([
                'user_id' => $creatorId,
                'role' => 'admin',
                'joined_at' => now(),
            ]);

            // Add other participants
            foreach ($participantIds as $participantId) {
                if ($participantId !== $creatorId) {
                    $conversation->participants()->create([
                        'user_id' => $participantId,
                        'role' => 'member',
                        'joined_at' => now(),
                    ]);
                }
            }

            return $conversation;
        });
    }

    /**
     * Get user's conversations with pagination
     */
    public function getUserConversations(string $userId, string $type = 'all', int $perPage = 20)
    {
        $query = ChatConversation::with(['participants.user', 'latestMessage'])
            ->withParticipant($userId)
            ->orderBy('last_message_at', 'desc')
            ->orderBy('created_at', 'desc');

        if ($type !== 'all') {
            $query->where('type', $type);
        }

        return $query->paginate($perPage);
    }

    /**
     * Get conversation details (with authorization check)
     */
    public function getConversation(string $conversationId, string $userId): ?ChatConversation
    {
        return ChatConversation::with(['participants.user', 'latestMessage.sender'])
            ->where('id', $conversationId)
            ->whereHas('participants', function ($q) use ($userId) {
                $q->where('user_id', $userId)->whereNull('left_at');
            })
            ->first();
    }

    /**
     * Add participant to conversation
     */
    public function addParticipant(
        string $conversationId,
        string $userId,
        string $addedByUserId,
        string $role = 'member'
    ): ChatConversationParticipant {
        $conversation = ChatConversation::findOrFail($conversationId);

        // Check if user has permission to add participants
        $adderParticipant = $conversation->participants()
            ->where('user_id', $addedByUserId)
            ->whereNull('left_at')
            ->first();

        if (!$adderParticipant || !$adderParticipant->isAdmin()) {
            throw new \Exception('You do not have permission to add participants');
        }

        // Check if already a participant
        if ($conversation->hasParticipant($userId)) {
            throw new \Exception('User is already a participant');
        }

        return $conversation->addParticipant($userId, $role);
    }

    /**
     * Remove participant from conversation
     */
    public function removeParticipant(
        string $conversationId,
        string $userId,
        string $removedByUserId
    ): void {
        $conversation = ChatConversation::findOrFail($conversationId);

        // Check if user has permission to remove participants
        $removerParticipant = $conversation->participants()
            ->where('user_id', $removedByUserId)
            ->whereNull('left_at')
            ->first();

        if (!$removerParticipant || !$removerParticipant->isAdmin()) {
            throw new \Exception('You do not have permission to remove participants');
        }

        $participant = $conversation->participants()
            ->where('user_id', $userId)
            ->whereNull('left_at')
            ->first();

        if ($participant) {
            $participant->leave();
        }
    }

    /**
     * Leave conversation
     */
    public function leaveConversation(string $conversationId, string $userId): void
    {
        $conversation = ChatConversation::findOrFail($conversationId);
        $participant = $conversation->participants()
            ->where('user_id', $userId)
            ->whereNull('left_at')
            ->first();

        if ($participant) {
            $participant->leave();
        }
    }

    /**
     * Archive conversation
     */
    public function archiveConversation(string $conversationId, string $userId): void
    {
        $conversation = ChatConversation::findOrFail($conversationId);
        
        if (!$conversation->hasParticipant($userId)) {
            throw new \Exception('You are not a participant of this conversation');
        }

        $conversation->update(['is_archived' => true]);
    }

    /**
     * Unarchive conversation
     */
    public function unarchiveConversation(string $conversationId, string $userId): void
    {
        $conversation = ChatConversation::findOrFail($conversationId);
        
        if (!$conversation->hasParticipant($userId)) {
            throw new \Exception('You are not a participant of this conversation');
        }

        $conversation->update(['is_archived' => false]);
    }
}