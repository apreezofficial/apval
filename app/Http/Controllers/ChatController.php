<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ConversationParticipant;
use App\Services\Chat\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController
{
    public function __construct(private ChatService $chatService) {}

    /**
     * GET /api/chatroom/conversations
     * Supports ?tab=all|direct|communities|marketplace|support and ?search=keyword
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $tab = $request->input('tab', 'all');
        $search = $request->input('search');
        $perPage = (int) $request->input('per_page', 20);

        $conversations = $this->chatService->getUserConversations($user, $tab, $search, $perPage);

        return response()->json([
            'status' => 'success',
            'data' => $conversations,
        ]);
    }

    /**
     * POST /api/chatroom/conversations/direct
     */
    public function startConversation(Request $request): JsonResponse
    {
        $request->validate([
            'recipient_id' => ['required', 'string', 'exists:users,id'],
        ]);

        $conversation = $this->chatService->getOrCreateDirectConversation(
            $request->user(),
            $request->recipient_id
        );

        return response()->json([
            'status' => 'success',
            'data' => $conversation,
        ], 201);
    }

    /**
     * POST /api/chatroom/conversations/marketplace
     */
    public function marketplaceChat(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => ['required', 'string', 'exists:products,id'],
        ]);

        $conversation = $this->chatService->getOrCreateMarketplaceConversation(
            $request->user(),
            $request->product_id
        );

        return response()->json([
            'status' => 'success',
            'data' => $conversation,
        ], 201);
    }

    /**
     * POST /api/chatroom/conversations/order
     */
    public function orderChat(Request $request): JsonResponse
    {
        $request->validate([
            'order_id' => ['required', 'string', 'exists:orders,id'],
        ]);

        $conversation = $this->chatService->getOrCreateOrderConversation(
            $request->user(),
            $request->order_id
        );

        return response()->json([
            'status' => 'success',
            'data' => $conversation,
        ], 201);
    }

    /**
     * POST /api/chatroom/conversations/support
     */
    public function supportChat(Request $request): JsonResponse
    {
        $conversation = $this->chatService->getOrCreateSupportConversation($request->user());

        return response()->json([
            'status' => 'success',
            'data' => $conversation,
        ], 201);
    }

    /**
     * GET /api/chatroom/conversations/{id}/messages
     */
    public function messages(Request $request, string $id): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 30);
        $messages = $this->chatService->getMessages($request->user(), $id, $perPage);

        return response()->json([
            'status' => 'success',
            'data' => $messages,
        ]);
    }

    /**
     * POST /api/chatroom/conversations/{id}/messages
     */
    public function sendMessage(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'body' => ['nullable', 'string', 'max:5000'],
            'type' => ['nullable', 'string', 'in:text,image,video,voice,document,product_card,order_card,payment_request,location'],
            'media_url' => ['nullable', 'url'],
            'media_duration' => ['nullable', 'integer', 'min:1'],
            'file_name' => ['nullable', 'string', 'max:255'],
            'file_size' => ['nullable', 'integer'],
            'product_id' => ['nullable', 'string', 'exists:products,id'],
            'order_id' => ['nullable', 'string', 'exists:orders,id'],
            'location_data' => ['nullable', 'array'],
            'reply_to_id' => ['nullable', 'string', 'exists:messages,id'],
            'metadata' => ['nullable', 'array'],
        ]);

        $message = $this->chatService->sendMessage(
            $request->user(),
            $id,
            $request->all()
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Message sent.',
            'data' => $message,
        ], 201);
    }

    /**
     * POST /api/chatroom/messages/{messageId}/react
     */
    public function react(Request $request, string $messageId): JsonResponse
    {
        $request->validate([
            'emoji' => ['required', 'string', 'max:16'],
        ]);

        $result = $this->chatService->toggleReaction($request->user(), $messageId, $request->emoji);

        return response()->json([
            'status' => 'success',
            'data' => $result,
        ]);
    }

    /**
     * POST /api/chatroom/messages/{messageId}/pin
     */
    public function pin(Request $request, string $messageId): JsonResponse
    {
        $request->validate([
            'pin' => ['nullable', 'boolean'],
        ]);

        $pin = $request->input('pin', true);
        $message = $this->chatService->pinMessage($request->user(), $messageId, (bool) $pin);

        return response()->json([
            'status' => 'success',
            'message' => $pin ? 'Message pinned.' : 'Message unpinned.',
            'data' => $message,
        ]);
    }

    /**
     * DELETE /api/chatroom/messages/{messageId}
     */
    public function destroyMessage(Request $request, string $messageId): JsonResponse
    {
        $this->chatService->deleteMessage($request->user(), $messageId);

        return response()->json([
            'status' => 'success',
            'message' => 'Message deleted.',
        ]);
    }

    /**
     * POST /api/chatroom/conversations/{id}/read
     */
    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $participant = ConversationParticipant::where('conversation_id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $participant->update(['last_read_at' => now()]);

        return response()->json([
            'status' => 'success',
            'message' => 'Conversation marked as read.',
        ]);
    }

    /**
     * POST /api/chatroom/block
     */
    public function block(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => ['required', 'string', 'exists:users,id'],
        ]);

        $this->chatService->blockUser($request->user(), $request->user_id);

        return response()->json([
            'status' => 'success',
            'message' => 'User blocked.',
        ]);
    }

    /**
     * POST /api/chatroom/unblock
     */
    public function unblock(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => ['required', 'string', 'exists:users,id'],
        ]);

        $this->chatService->unblockUser($request->user(), $request->user_id);

        return response()->json([
            'status' => 'success',
            'message' => 'User unblocked.',
        ]);
    }

    /**
     * POST /api/chatroom/report
     */
    public function report(Request $request): JsonResponse
    {
        $request->validate([
            'reported_user_id' => ['nullable', 'string', 'exists:users,id'],
            'conversation_id' => ['nullable', 'string', 'exists:conversations,id'],
            'message_id' => ['nullable', 'string', 'exists:messages,id'],
            'reason' => ['required', 'string', 'in:spam,scam,fraud,harassment,fake_product,offensive,illegal,other'],
            'details' => ['nullable', 'string', 'max:1000'],
        ]);

        $report = $this->chatService->submitReport($request->user(), $request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Report submitted. Our moderation team will investigate.',
            'data' => $report,
        ], 201);
    }
}
