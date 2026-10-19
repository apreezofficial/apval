<?php

declare(strict_types=1);

namespace App\Http\Controllers\Feed;

use App\Services\Feed\PostService;
use App\Services\Feed\FeedService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostController
{
    public function __construct(
        private PostService $postService,
        private FeedService $feedService
    ) {}

    /**
     * GET /api/v1/posts/{id}
     */
    public function show(string $id): JsonResponse
    {
        $user = Auth::user();

        try {
            $post = $this->postService->getPost($id, $user->id);

            return response()->json([
                'success' => true,
                'data' => $this->formatPost($post, $user->id),
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        }
    }

    /**
     * POST /api/v1/posts
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'content' => 'nullable|string|max:5000',
            'category_id' => 'nullable|uuid|exists:feed_categories,id',
            'post_type' => 'nullable|in:normal,image,video,product,event',
            'media' => 'nullable|array|max:10',
            'media.*' => 'file|mimes:jpeg,png,jpg,gif,mp4,mov,avi|max:51200',
            'hashtags' => 'nullable|array',
            'mentions' => 'nullable|array',
            'product_id' => 'nullable|uuid|exists:products,id',
            'event_id' => 'nullable|uuid',
            'location' => 'nullable|array',
            'location.latitude' => 'numeric|between:-90,90',
            'location.longitude' => 'numeric|between:-180,180',
            'location.name' => 'nullable|string|max:255',
        ]);

        $user = Auth::user();

        try {
            $post = $this->postService->createPost(
                userId: $user->id,
                content: $request->content,
                categoryId: $request->category_id,
                postType: $request->post_type ?? 'normal',
                media: $request->file('media') ?? [],
                hashtags: $request->hashtags,
                mentions: $request->mentions,
                productId: $request->product_id,
                eventId: $request->event_id,
                location: $request->location,
            );

            return response()->json([
                'success' => true,
                'message' => 'Post created successfully',
                'data' => $this->formatPost($post, $user->id),
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * PUT /api/v1/posts/{id}
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'content' => 'nullable|string|max:5000',
            'category_id' => 'nullable|uuid|exists:feed_categories,id',
            'hashtags' => 'nullable|array',
            'location' => 'nullable|array',
        ]);

        $user = Auth::user();

        try {
            $post = $this->postService->updatePost(
                $id,
                $user->id,
                $request->only(['content', 'category_id', 'hashtags', 'location'])
            );

            return response()->json([
                'success' => true,
                'message' => 'Post updated successfully',
                'data' => $this->formatPost($post, $user->id),
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * DELETE /api/v1/posts/{id}
     */
    public function destroy(string $id): JsonResponse
    {
        $user = Auth::user();

        try {
            $this->postService->deletePost($id, $user->id);

            return response()->json([
                'success' => true,
                'message' => 'Post deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * GET /api/v1/posts/user/{userId}
     */
    public function userPosts(Request $request, string $userId): JsonResponse
    {
        $perPage = (int) $request->get('per_page', 20);

        try {
            $posts = $this->feedService->getUserPosts($userId, $perPage);

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