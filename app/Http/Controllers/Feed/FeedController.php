<?php

declare(strict_types=1);

namespace App\Http\Controllers\Feed;

use App\Services\Feed\FeedService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeedController
{
    public function __construct(
        private FeedService $feedService
    ) {}

    /**
     * GET /api/v1/feed
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $categoryId = $request->get('category_id');
        $perPage = (int) $request->get('per_page', 20);
        $page = (int) $request->get('page', 1);

        try {
            $posts = $this->feedService->getFeed($user->id, $categoryId, $perPage, $page);

            return response()->json([
                'success' => true,
                'data' => $posts->map(fn($post) => $this->formatPost($post, $user->id)),
                'meta' => [
                    'current_page' => $posts->currentPage(),
                    'last_page' => $posts->lastPage(),
                    'per_page' => $posts->perPage(),
                    'total' => $posts->total(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/v1/feed/trending
     */
    public function trending(Request $request): JsonResponse
    {
        $limit = (int) $request->get('limit', 10);

        try {
            $posts = $this->feedService->getTrendingPosts($limit);

            return response()->json([
                'success' => true,
                'data' => $posts->map(fn($post) => $this->formatPost($post, Auth::id())),
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/v1/feed/category/{categoryId}
     */
    public function byCategory(Request $request, string $categoryId): JsonResponse
    {
        $perPage = (int) $request->get('per_page', 20);

        try {
            $posts = $this->feedService->getCategoryPosts($categoryId, $perPage);

            return response()->json([
                'success' => true,
                'data' => $posts->map(fn($post) => $this->formatPost($post, Auth::id())),
                'meta' => [
                    'current_page' => $posts->currentPage(),
                    'last_page' => $posts->lastPage(),
                    'per_page' => $posts->perPage(),
                    'total' => $posts->total(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/v1/feed/hashtag/{hashtag}
     */
    public function byHashtag(Request $request, string $hashtag): JsonResponse
    {
        $perPage = (int) $request->get('per_page', 20);

        try {
            $posts = $this->feedService->getHashtagPosts('#' . ltrim($hashtag, '#'), $perPage);

            return response()->json([
                'success' => true,
                'data' => $posts->map(fn($post) => $this->formatPost($post, Auth::id())),
                'meta' => [
                    'current_page' => $posts->currentPage(),
                    'last_page' => $posts->lastPage(),
                    'per_page' => $posts->perPage(),
                    'total' => $posts->total(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/v1/feed/search
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate(['q' => 'required|string|min:2|max:255']);
        $perPage = (int) $request->get('per_page', 20);

        try {
            $posts = $this->feedService->searchPosts($request->q, $perPage);

            return response()->json([
                'success' => true,
                'data' => $posts->map(fn($post) => $this->formatPost($post, Auth::id())),
                'meta' => [
                    'current_page' => $posts->currentPage(),
                    'last_page' => $posts->lastPage(),
                    'per_page' => $posts->perPage(),
                    'total' => $posts->total(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/v1/feed/saved
     */
    public function saved(Request $request): JsonResponse
    {
        $user = Auth::user();
        $perPage = (int) $request->get('per_page', 20);

        try {
            $posts = $this->feedService->getSavedPosts($user->id, $perPage);

            return response()->json([
                'success' => true,
                'data' => $posts->map(fn($post) => $this->formatPost($post, $user->id)),
                'meta' => [
                    'current_page' => $posts->currentPage(),
                    'last_page' => $posts->lastPage(),
                    'per_page' => $posts->perPage(),
                    'total' => $posts->total(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Format post for API response
     */
    private function formatPost($post, ?string $userId = null): array
    {
        return [
            'id' => $post->id,
            'post_type' => $post->post_type,
            'content' => $post->content,
            'hashtags' => $post->hashtags,
            'mentions' => $post->mentions,
            'location' => $post->location,
            'status' => $post->status,
            'is_pinned' => $post->is_pinned,
            'is_featured' => $post->is_featured,
            'user' => [
                'id' => $post->user->id,
                'name' => $post->user->first_name . ' ' . $post->user->last_name,
                'username' => $post->user->username ?? null,
                'profile_picture' => $post->user->profile_picture,
                'is_verified' => $post->user->is_verified ?? false,
            ],
            'category' => $post->category ? [
                'id' => $post->category->id,
                'name' => $post->category->name,
                'icon' => $post->category->icon,
                'color' => $post->category->color,
            ] : null,
            'media' => $post->media->map(fn($m) => [
                'id' => $m->id,
                'type' => $m->media_type,
                'url' => $m->full_media_url,
                'thumbnail_url' => $m->full_thumbnail_url,
                'alt_text' => $m->alt_text,
                'duration' => $m->duration,
            ]),
            'product' => $post->product ? [
                'id' => $post->product->id,
                'name' => $post->product->name,
                'price' => $post->product->price,
                'image' => $post->product->images[0] ?? null,
            ] : null,
            'stats' => [
                'likes' => $post->likes_count,
                'comments' => $post->comments_count,
                'shares' => $post->shares_count,
                'saves' => $post->saves_count,
                'views' => $post->views_count,
            ],
            'is_liked' => $userId ? $post->isLikedBy($userId) : false,
            'is_saved' => $userId ? $post->isSavedBy($userId) : false,
            'published_at' => $post->published_at?->toISOString(),
            'created_at' => $post->created_at->toISOString(),
        ];
    }
}