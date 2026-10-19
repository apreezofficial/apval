<?php

declare(strict_types=1);

namespace App\Services\Feed;

use App\Models\FeedPost;
use App\Models\FeedCategory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PostService
{
    const MAX_CONTENT_LENGTH = 5000;

    public function __construct(
        private MediaUploadService $mediaUploadService
    ) {}

    /**
     * Create a new post
     */
    public function createPost(
        string $userId,
        ?string $content,
        ?string $categoryId,
        string $postType = 'normal',
        array $media = [],
        ?array $hashtags = null,
        ?array $mentions = null,
        ?string $productId = null,
        ?string $eventId = null,
        ?array $location = null,
        bool $publishImmediately = true
    ): FeedPost {
        return DB::transaction(function () use (
            $userId, $content, $categoryId, $postType, $media,
            $hashtags, $mentions, $productId, $eventId, $location, $publishImmediately
        ) {
            // Validate content length
            if ($content && strlen($content) > self::MAX_CONTENT_LENGTH) {
                throw new \Exception('Post content exceeds maximum length of ' . self::MAX_CONTENT_LENGTH . ' characters');
            }

            // Auto-extract hashtags if not provided
            if ($hashtags === null && $content) {
                $hashtags = $this->extractHashtags($content);
            }

            // Auto-extract mentions if not provided
            if ($mentions === null && $content) {
                $mentions = $this->extractMentions($content);
            }

            // Determine post type based on media if not specified
            if ($postType === 'normal' && !empty($media)) {
                $firstMedia = $media[0] ?? null;
                if ($firstMedia instanceof UploadedFile) {
                    $mimeType = $firstMedia->getMimeType();
                    if (str_starts_with($mimeType, 'video/')) {
                        $postType = 'video';
                    } elseif (str_starts_with($mimeType, 'image/')) {
                        $postType = 'image';
                    }
                }
            }

            // Create the post
            $post = FeedPost::create([
                'id' => (string) Str::uuid(),
                'user_id' => $userId,
                'category_id' => $categoryId,
                'post_type' => $postType,
                'content' => $content,
                'hashtags' => $hashtags,
                'mentions' => $mentions,
                'product_id' => $productId,
                'event_id' => $eventId,
                'location' => $location,
                'status' => $publishImmediately ? 'published' : 'draft',
                'published_at' => $publishImmediately ? now() : null,
            ]);

            // Upload and attach media using the MediaUploadService
            if (!empty($media)) {
                $this->mediaUploadService->processMediaForPost($post->id, $media);
            }

            // Increment category post count
            if ($categoryId) {
                $category = FeedCategory::find($categoryId);
                if ($category) {
                    $category->incrementPostCount();
                }
            }

            // Create notifications for mentions
            if (!empty($mentions)) {
                $this->notifyMentionedUsers($post, $mentions);
            }

            return $post->load(['user', 'category', 'media']);
        });
    }

    /**
     * Update an existing post
     */
    public function updatePost(
        string $postId,
        string $userId,
        array $data
    ): FeedPost {
        $post = FeedPost::findOrFail($postId);

        // Check ownership
        if ($post->user_id !== $userId) {
            throw new \Exception('You can only edit your own posts');
        }

        // Validate content length
        if (isset($data['content']) && strlen($data['content']) > self::MAX_CONTENT_LENGTH) {
            throw new \Exception('Post content exceeds maximum length');
        }

        // Update post
        $post->update($data);

        // Re-extract hashtags if content changed
        if (isset($data['content'])) {
            $post->update([
                'hashtags' => $this->extractHashtags($data['content']),
                'mentions' => $this->extractMentions($data['content']),
            ]);
        }

        return $post->fresh()->load(['user', 'category', 'media']);
    }

    /**
     * Delete a post (soft delete)
     */
    public function deletePost(string $postId, string $userId): void
    {
        $post = FeedPost::findOrFail($postId);

        // Check ownership
        if ($post->user_id !== $userId) {
            throw new \Exception('You can only delete your own posts');
        }

        DB::transaction(function () use ($post) {
            // Delete media files via the MediaUploadService
            foreach ($post->media as $media) {
                $this->mediaUploadService->deleteMedia($media);
            }

            // Decrement category post count
            if ($post->category) {
                $post->category->decrementPostCount();
            }

            // Soft delete the post
            $post->delete();
        });
    }

    /**
     * Get a single post with all relationships
     */
    public function getPost(string $postId, ?string $userId = null): FeedPost
    {
        $post = FeedPost::with(['user', 'category', 'media', 'product'])
            ->findOrFail($postId);

        // Increment view count
        $post->incrementViewCount();

        return $post;
    }

    /**
     * Extract hashtags from content
     */
    private function extractHashtags(string $content): array
    {
        preg_match_all('/#(\w+)/', $content, $matches);
        return array_map(fn($tag) => '#' . $tag, $matches[1] ?? []);
    }

    /**
     * Extract mentions from content
     */
    private function extractMentions(string $content): array
    {
        preg_match_all('/@(\w+)/', $content, $matches);
        // TODO: Convert usernames to user IDs
        return $matches[1] ?? [];
    }

    /**
     * Notify mentioned users
     */
    private function notifyMentionedUsers(FeedPost $post, array $mentions): void
    {
        // This will be handled by the FeedNotificationService
        // Example: app(FeedNotificationService::class)->notifyMentions($post->id, $mentions, $post->user_id);
    }
}