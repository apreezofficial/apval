<?php

declare(strict_types=1);

namespace App\Http\Controllers\Chat;
use App\Services\Chat\AgrinovaChatService;
use App\Http\Requests\Chat\SendTextMessageRequest;
use App\Http\Requests\Chat\SendMediaMessageRequest;
use App\Http\Requests\Chat\ShareProductRequest;
use App\Models\ChatMessage;
use App\Services\Chat\MessageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Events\Chat\TypingStarted;
use App\Events\Chat\TypingStopped;
use App\Events\Chat\MessageSent;
use App\Events\Chat\MessageRead;

class MessageController
{
    
    public function __construct(
        private MessageService $messageService,
        private AgrinovaChatService $agrinovaChatService
    ) {}

    public function index(Request $request, string $conversationId): JsonResponse
    {
        $user = Auth::user();
        $perPage = $request->get('per_page', 50);
        $beforeMessageId = $request->get('before_message_id');

        try {
            $messages = $this->messageService->getConversationMessages(
                $conversationId,
                $user->id,
                (int) $perPage,
                $beforeMessageId
            );

            return response()->json([
                'success' => true,
                'data' => $messages->map(fn($message) => $this->formatMessage($message)),
                'meta' => [
                    'current_page' => $messages->currentPage(),
                    'last_page' => $messages->lastPage(),
                    'per_page' => $messages->perPage(),
                    'total' => $messages->total(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Start typing indicator
     * POST /api/v1/chat/conversations/{conversationId}/messages/typing
     */
    public function startTyping(string $conversationId): JsonResponse
    {
        $user = Auth::user();

        event(new TypingStarted(
            $conversationId,
            $user->id,
            $user->first_name . ' ' . $user->last_name
        ));

        return response()->json(['success' => true]);
    }

    /**
     * Stop typing indicator
     * POST /api/v1/chat/conversations/{conversationId}/messages/stop-typing
     */
    public function stopTyping(string $conversationId): JsonResponse
    {
        $user = Auth::user();

        event(new TypingStopped($conversationId, $user->id));

        return response()->json(['success' => true]);
    }

    public function sendText(SendTextMessageRequest $request, string $conversationId): JsonResponse
    {
        $user = Auth::user();

        try {
            $message = $this->messageService->sendTextMessage(
                $conversationId,
                $user->id,
                $request->content,
                $request->reply_to_message_id
            );

            // 🔴 BROADCAST REAL-TIME EVENT
            event(new MessageSent($message));

            return response()->json([
                'success' => true,
                'message' => 'Message sent',
                'data' => $this->formatMessage($message),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function sendMedia(SendMediaMessageRequest $request, string $conversationId): JsonResponse
    {
        $user = Auth::user();

        try {
            $message = $this->messageService->sendMediaMessage(
                $conversationId,
                $user->id,
                $request->file('file'),
                $request->type,
                $request->content,
                $request->reply_to_message_id
            );

            return response()->json([
                'success' => true,
                'message' => 'Media sent',
                'data' => $this->formatMessage($message),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function shareProduct(ShareProductRequest $request, string $conversationId): JsonResponse
    {
        $user = Auth::user();

        try {
            $message = $this->messageService->shareProductCard(
                $conversationId,
                $user->id,
                $request->product_id,
                $request->content,
                $request->reply_to_message_id
            );

            return response()->json([
                'success' => true,
                'message' => 'Product shared',
                'data' => $this->formatMessage($message),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function shareOrder(Request $request, string $conversationId): JsonResponse
    {
        $request->validate([
            'order_id' => 'required|uuid|exists:orders,id',
            'content' => 'nullable|string|max:5000',
            'reply_to_message_id' => 'nullable|uuid|exists:chat_messages,id',
        ]);

        $user = Auth::user();

        try {
            $message = $this->messageService->shareOrderCard(
                $conversationId,
                $user->id,
                $request->order_id,
                $request->content,
                $request->reply_to_message_id
            );

            return response()->json([
                'success' => true,
                'message' => 'Order shared',
                'data' => $this->formatMessage($message),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function sendLocation(Request $request, string $conversationId): JsonResponse
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'location_name' => 'nullable|string|max:255',
            'reply_to_message_id' => 'nullable|uuid|exists:chat_messages,id',
        ]);

        $user = Auth::user();

        try {
            $message = $this->messageService->sendLocationMessage(
                $conversationId,
                $user->id,
                (float) $request->latitude,
                (float) $request->longitude,
                $request->location_name,
                $request->reply_to_message_id
            );

            return response()->json([
                'success' => true,
                'message' => 'Location shared',
                'data' => $this->formatMessage($message),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function markAsRead(string $conversationId): JsonResponse
    {
        $user = Auth::user();

        try {
            $this->messageService->markMessagesAsRead($conversationId, $user->id);

            // 🔴 BROADCAST READ RECEIPT
            event(new MessageRead(
                $conversationId,
                $user->id,
                $user->first_name . ' ' . $user->last_name
            ));

            return response()->json([
                'success' => true,
                'message' => 'Messages marked as read',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
 
    public function addReaction(Request $request, string $messageId): JsonResponse
    {
        $request->validate([
            'emoji' => 'required|string|max:10',
        ]);

        $user = Auth::user();

        try {
            $this->messageService->addReaction($messageId, $user->id, $request->emoji);

            return response()->json([
                'success' => true,
                'message' => 'Reaction added',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function removeReaction(string $messageId): JsonResponse
    {
        $user = Auth::user();

        try {
            $this->messageService->removeReaction($messageId, $user->id);

            return response()->json([
                'success' => true,
                'message' => 'Reaction removed',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function edit(Request $request, string $messageId): JsonResponse
    {
        $request->validate([
            'content' => 'required|string|max:5000',
        ]);

        $user = Auth::user();

        try {
            $message = $this->messageService->editMessage(
                $messageId,
                $user->id,
                $request->content
            );

            return response()->json([
                'success' => true,
                'message' => 'Message edited',
                'data' => $this->formatMessage($message),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function destroy(string $messageId): JsonResponse
    {
        $user = Auth::user();

        try {
            $this->messageService->deleteMessage($messageId, $user->id);

            return response()->json([
                'success' => true,
                'message' => 'Message deleted',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function forward(Request $request, string $messageId): JsonResponse
    {
        $request->validate([
            'target_conversation_id' => 'required|uuid|exists:chat_conversations,id',
        ]);

        $user = Auth::user();

        try {
            $forwardedMessage = $this->messageService->forwardMessage(
                $messageId,
                $user->id,
                $request->target_conversation_id
            );

            return response()->json([
                'success' => true,
                'message' => 'Message forwarded',
                'data' => $this->formatMessage($forwardedMessage),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    private function formatMessage(ChatMessage $message): array
    {
        return [
            'id' => $message->id,
            'conversation_id' => $message->conversation_id,
            'sender_id' => $message->sender_id,
            'sender_name' => $message->sender->first_name . ' ' . $message->sender->last_name,
            'sender_profile_picture' => $message->sender->profile_picture,
            'type' => $message->type,
            'content' => $message->content,
            'media_url' => $message->media_url ? asset('storage/' . $message->media_url) : null,
            'media_type' => $message->media_type,
            'media_duration' => $message->media_duration,
            'product' => $message->product ? [
                'id' => $message->product->id,
                'name' => $message->product->name,
                'price' => $message->product->price,
                'image' => $message->product->images[0] ?? null,
            ] : null,
            'order' => $message->order ? [
                'id' => $message->order->id,
                'status' => $message->order->status,
                'total_amount' => $message->order->total_amount,
            ] : null,
            'location' => $message->latitude ? [
                'latitude' => (float) $message->latitude,
                'longitude' => (float) $message->longitude,
                'name' => $message->location_name,
            ] : null,
            'reply_to' => $message->replyTo ? [
                'id' => $message->replyTo->id,
                'sender_name' => $message->replyTo->sender->first_name . ' ' . $message->replyTo->sender->last_name,
                'content' => $message->replyTo->preview,
            ] : null,
            'reactions' => $message->reactions ?? [],
            'created_at' => $message->created_at->toISOString(),
            'delivered_at' => $message->delivered_at?->toISOString(),
            'read_at' => $message->read_at?->toISOString(),
            'is_edited' => $message->is_edited,
            'is_deleted' => $message->is_deleted,
            'flagged_suspicious' => $message->flagged_suspicious,
        ];
    }

    
    public function triggerAgrinova(Request $request, string $conversationId): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'reply_to_message_id' => 'nullable|uuid|exists:chat_messages,id',
        ]);

        $user = Auth::user();

        try {
            // 1. Save the user's question as a normal text message
            $userMessage = $this->messageService->sendTextMessage(
                $conversationId,
                $user->id,
                "@Agrinova " . $request->message,
                $request->reply_to_message_id
            );

            // 2. Trigger the AI to generate and save its response
            $aiMessage = $this->agrinovaChatService->handleMention(
                $conversationId,
                $request->message,
                $userMessage->id
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'user_message' => $this->formatMessage($userMessage),
                    'ai_response' => $this->formatMessage($aiMessage),
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}