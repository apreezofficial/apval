<?php

declare(strict_types=1);

namespace App\Http\Controllers\Chat;

use App\Models\ChatConversation;
use App\Services\Chat\ConversationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ConversationController
{
    public function __construct(
        private ConversationService $conversationService
    ) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'success' => false,
                    'message' => 'Unauthenticated',
                ], 401);
            }

            $type = $request->get('type', 'all');
            $perPage = (int) $request->get('per_page', 20);

            $conversations = $this->conversationService->getUserConversations($user->id, $type, $perPage);

            $data = $conversations->map(function ($conversation) use ($user) {
                $participant = $conversation->participants->firstWhere('user_id', $user->id);
                $otherParticipant = $conversation->getOtherParticipant($user->id);

                $unread = 0;
                try {
                    $unread = $participant ? $participant->getUnreadCount() : 0;
                } catch (\Throwable $e) {
                    $unread = 0;
                }

                $isoDate = null;
                if ($conversation->last_message_at) {
                    $isoDate = $conversation->last_message_at instanceof \DateTimeInterface
                        ? $conversation->last_message_at->toIso8601String()
                        : (string) $conversation->last_message_at;
                }

                return [
                    'id' => $conversation->id,
                    'type' => $conversation->type,
                    'title' => $conversation->title ?? $otherParticipant?->user?->first_name ?? 'Conversation',
                    'avatar_url' => $conversation->avatar_url ?? $otherParticipant?->user?->profile_picture,
                    'last_message' => $conversation->last_message_preview,
                    'last_message_at' => $isoDate,
                    'unread_count' => $unread,
                    'is_locked' => (bool) $conversation->is_locked,
                    'is_archived' => (bool) $conversation->is_archived,
                    'participants' => $conversation->participants->map(function ($p) {
                        return [
                            'id' => $p->user_id,
                            'first_name' => $p->user?->first_name ?? 'User',
                            'last_name' => $p->user?->last_name ?? '',
                            'profile_picture' => $p->user?->profile_picture,
                        ];
                    })->values(),
                ];
            })->values();

            return response()->json([
                'status' => 'success',
                'success' => true,
                'data' => $data,
                'meta' => [
                    'current_page' => $conversations->currentPage(),
                    'last_page' => $conversations->lastPage(),
                    'per_page' => $conversations->perPage(),
                    'total' => $conversations->total(),
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('Chat conversations fetch error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 'success',
                'success' => true,
                'data' => [],
                'meta' => [
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => (int) $request->get('per_page', 20),
                    'total' => 0,
                ],
            ]);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $user = Auth::user();
            $conversation = $this->conversationService->getConversation($id, $user->id);

            if (!$conversation) {
                return response()->json([
                    'status' => 'error',
                    'success' => false,
                    'message' => 'Conversation not found or access denied',
                ], 404);
            }

            $participant = $conversation->participants->firstWhere('user_id', $user->id);

            $unread = 0;
            try {
                $unread = $participant ? $participant->getUnreadCount() : 0;
            } catch (\Throwable $e) {
                $unread = 0;
            }

            $isoDate = null;
            if ($conversation->last_message_at) {
                $isoDate = $conversation->last_message_at instanceof \DateTimeInterface
                    ? $conversation->last_message_at->toIso8601String()
                    : (string) $conversation->last_message_at;
            }

            return response()->json([
                'status' => 'success',
                'success' => true,
                'data' => [
                    'id' => $conversation->id,
                    'type' => $conversation->type,
                    'title' => $conversation->title,
                    'description' => $conversation->description,
                    'avatar_url' => $conversation->avatar_url,
                    'last_message' => $conversation->last_message_preview,
                    'last_message_at' => $isoDate,
                    'unread_count' => $unread,
                    'is_locked' => (bool) $conversation->is_locked,
                    'is_archived' => (bool) $conversation->is_archived,
                    'participants' => $conversation->participants->map(function ($p) {
                        return [
                            'id' => $p->user_id,
                            'first_name' => $p->user?->first_name ?? 'User',
                            'last_name' => $p->user?->last_name ?? '',
                            'profile_picture' => $p->user?->profile_picture,
                        ];
                    })->values(),
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('Show conversation error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'success' => false,
                'message' => 'Failed to load conversation details',
            ], 500);
        }
    }

    public function startDirect(Request $request): JsonResponse
    {
        $request->validate(['recipient_id' => 'required|uuid|exists:users,id']);
        $user = Auth::user();

        if ($user->id === $request->recipient_id) {
            return response()->json(['success' => false, 'message' => 'You cannot start a conversation with yourself'], 400);
        }

        try {
            $conversation = $this->conversationService->getOrCreateDirectConversation($user->id, $request->recipient_id);
            return response()->json(['success' => true, 'message' => 'Conversation started', 'data' => ['conversation_id' => $conversation->id]], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function startMarketplace(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|uuid|exists:products,id',
            'seller_id' => 'required|uuid|exists:users,id',
        ]);
        $user = Auth::user();

        try {
            $conversation = $this->conversationService->getOrCreateMarketplaceConversation($user->id, $request->seller_id, $request->product_id);
            return response()->json(['success' => true, 'message' => 'Marketplace conversation started', 'data' => ['conversation_id' => $conversation->id]], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function createGroup(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'participant_ids' => 'required|array|min:1|max:100',
            'participant_ids.*' => 'uuid|exists:users,id',
            'description' => 'nullable|string|max:1000',
        ]);
        $user = Auth::user();

        try {
            $conversation = $this->conversationService->createGroupConversation($user->id, $request->title, $request->participant_ids, $request->description);
            return response()->json(['success' => true, 'message' => 'Group conversation created', 'data' => ['conversation_id' => $conversation->id]], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function leave(string $id): JsonResponse
    {
        $user = Auth::user();
        try {
            $this->conversationService->leaveConversation($id, $user->id);
            return response()->json(['success' => true, 'message' => 'You have left the conversation']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
}