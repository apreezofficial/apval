<?php

declare(strict_types=1);

namespace App\Http\Controllers\Feed;

use App\Services\Feed\InteractionService;
use App\Models\FeedComment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InteractionController
{
    public function __construct(
        private InteractionService $interactionService
    ) {}

    // ==========================================
    // LIKES
    // ==========================================

    /**
     * POST /api/v1/posts/{id}/like
     */
    public function likePost(string $postId): JsonResponse
    {
        $user = Auth::user();

        try {
            $this->interactionService->likePost($postId, $user->id);

            return response()->json([
                'success' => true,
                'message' => 'Post liked',
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * DELETE /api/v1/posts/{id}/like
     */
    public function unlikePost(string $postId): JsonResponse
    {
        $user = Auth::user();

        try {
            $this->interactionService->unlikePost($postId, $user->id);

            return response()->json([
                'success' => true,
                'message' => 'Post unliked',
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * POST /api/v1/comments/{id}/like
     */
    public function likeComment(string $commentId): JsonResponse
    {
        $user = Auth::user();

        try {
            $this->interactionService->likeComment($commentId, $user->id);

            return response()->json([
                'success' => true,
                'message' => 'Comment liked',
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * DELETE /api/v1/comments/{id}/like
     */
    public function unlikeComment(string $commentId): JsonResponse
    {
        $user = Auth::user();

        try {
            $this->interactionService->unlikeComment($commentId, $user->id);

            return response()->json([
                'success' => true,
                'message' => 'Comment unliked',
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    // ==========================================
    // COMMENTS
    // ==========================================

    /**
     * GET /api/v1/posts/{id}/comments
     */
    public function getComments(Request $request, string $postId): JsonResponse
    {
        $perPage = (int) $request->get('per_page', 20);
        $userId = Auth::id();

        try {
            $comments = FeedComment::with(['user', 'replies.user'])
                ->where('post_id', $postId)
                ->whereNull('parent_id')
                ->published()
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $comments->map(fn($comment) => $this->formatComment($comment, $userId)),
                'meta' => [
                    'current_page' => $comments->currentPage(),
                    'last_page' => $comments->lastPage(),
                    'per_page' => $comments->perPage(),
                    'total' => $comments->total(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/v1/posts/{id}/comments
     */
    public function addComment(Request $request, string $postId): JsonResponse
    {
        $request->validate([
            'content' => 'required|string|max:2000',
            'parent_id' => 'nullable|uuid|exists:feed_comments,id',
        ]);

        $user = Auth::user();

        try {
            $comment = $this->interactionService->addComment(
                $postId,
                $user->id,
                $request->content,
                $request->parent_id
            );

            return response()->json([
                'success' => true,
                'message' => 'Comment added',
                'data' => $this->formatComment($comment, $user->id),
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * PUT /api/v1/comments/{id}
     */
    public function updateComment(Request $request, string $commentId): JsonResponse
    {
        $request->validate(['content' => 'required|string|max:2000']);
        $user = Auth::user();

        try {
            $comment = $this->interactionService->updateComment(
                $commentId,
                $user->id,
                $request->content
            );

            return response()->json([
                'success' => true,
                'message' => 'Comment updated',
                'data' => $this->formatComment($comment, $user->id),
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * DELETE /api/v1/comments/{id}
     */
    public function deleteComment(string $commentId): JsonResponse
    {
        $user = Auth::user();

        try {
            $this->interactionService->deleteComment($commentId, $user->id);

            return response()->json([
                'success' => true,
                'message' => 'Comment deleted',
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    // ==========================================
    // SHARES
    // ==========================================

    /**
     * POST /api/v1/posts/{id}/share
     */
    public function sharePost(Request $request, string $postId): JsonResponse
    {
        $request->validate([
            'share_type' => 'nullable|in:internal,whatsapp,twitter,facebook,telegram,link_copy,other',
            'caption' => 'nullable|string|max:1000',
        ]);

        $user = Auth::user();

        try {
            $this->interactionService->sharePost(
                $postId,
                $user->id,
                $request->share_type ?? 'internal',
                $request->caption
            );

            return response()->json([
                'success' => true,
                'message' => 'Post shared',
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    // ==========================================
    // SAVES / BOOKMARKS
    // ==========================================

    /**
     * POST /api/v1/posts/{id}/save
     */
    public function savePost(Request $request, string $postId): JsonResponse
    {
        $request->validate(['folder_name' => 'nullable|string|max:100']);
        $user = Auth::user();

        try {
            $this->interactionService->savePost($postId, $user->id, $request->folder_name);

            return response()->json([
                'success' => true,
                'message' => 'Post saved',
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * DELETE /api/v1/posts/{id}/save
     */
    public function unsavePost(string $postId): JsonResponse
    {
        $user = Auth::user();

        try {
            $this->interactionService->unsavePost($postId, $user->id);

            return response()->json([
                'success' => true,
                'message' => 'Post unsaved',
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    // ==========================================
    // FOLLOWS
    // ==========================================

    /**
     * POST /api/v1/users/{id}/follow
     */
    public function followUser(string $userId): JsonResponse
    {
        $user = Auth::user();

        try {
            $this->interactionService->followUser($user->id, $userId);

            return response()->json([
                'success' => true,
                'message' => 'User followed',
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * DELETE /api/v1/users/{id}/follow
     */
    public function unfollowUser(string $userId): JsonResponse
    {
        $user = Auth::user();

        try {
            $this->interactionService->unfollowUser($user->id, $userId);

            return response()->json([
                'success' => true,
                'message' => 'User unfollowed',
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * GET /api/v1/users/{id}/followers
     */
    public function followers(string $userId): JsonResponse
    {
        $count = $this->interactionService->getFollowerCount($userId);
        $isFollowing = $this->interactionService->isFollowing(Auth::id(), $userId);

        return response()->json([
            'success' => true,
            'data' => [
                'followers_count' => $count,
                'is_following' => $isFollowing,
            ],
        ]);
    }

    /**
     * Format comment for API response
     */
    private function formatComment($comment, ?string $userId = null): array
    {
        return [
            'id' => $comment->id,
            'content' => $comment->content,
            'status' => $comment->status,
            'user' => [
                'id' => $comment->user->id,
                'name' => $comment->user->first_name . ' ' . $comment->user->last_name,
                'profile_picture' => $comment->user->profile_picture,
            ],
            'stats' => [
                'likes' => $comment->likes_count,
                'replies' => $comment->replies_count,
            ],
            'replies' => $comment->replies->map(fn($reply) => [
                'id' => $reply->id,
                'content' => $reply->content,
                'user' => [
                    'id' => $reply->user->id,
                    'name' => $reply->user->first_name . ' ' . $reply->user->last_name,
                    'profile_picture' => $reply->user->profile_picture,
                ],
                'likes_count' => $reply->likes_count,
                'created_at' => $reply->created_at->toISOString(),
            ]),
            'is_liked' => $userId ? $comment->isLikedBy($userId) : false,
            'created_at' => $comment->created_at->toISOString(),
        ];
    }
}