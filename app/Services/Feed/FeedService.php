<?php

declare(strict_types=1);

namespace App\Services\Feed;

use App\Models\FeedPost;
use App\Models\FeedFollow;
use App\Models\FeedSponsoredPost;
use Illuminate\Support\Facades\DB;

class FeedService
{
    const DEFAULT_PER_PAGE = 20;

    /**
     * Get feed for a user (implements the feed algorithm)
     */
    public function getFeed(
        string $userId,
        ?string $categoryId = null,
        int $perPage = self::DEFAULT_PER_PAGE,
        int $page = 1
    ) {
        // Build the feed query with ranking algorithm
        $query = FeedPost::with(['user', 'category', 'media', 'product'])
            ->published()
            ->notFlagged();

        // Filter by category if provided
        if ($categoryId) {
            $query->byCategory($categoryId);
        }

        // Apply ranking algorithm
        $posts = $this->applyRankingAlgorithm($query, $userId, $perPage, $page);

        return $posts;
    }

    /**
     * Apply the feed ranking algorithm
     * Priority: Pinned → Featured → Followed → Recent → Popular → Sponsored
     */
    private function applyRankingAlgorithm($query, string $userId, int $perPage, int $page)
    {
        // Get IDs of users the current user follows
        $followedUserIds = FeedFollow::followingBy($userId)->pluck('following_id')->toArray();

        return $query
            ->select('feed_posts.*')
            ->selectRaw('
                CASE 
                    WHEN is_pinned = 1 THEN 1000
                    WHEN is_featured = 1 THEN 900
                    WHEN user_id IN (?) THEN 800
                    ELSE 0
                END as priority_score
            ', [implode(',', $followedUserIds)])
            ->orderByRaw('priority_score DESC, published_at DESC')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Get posts by a specific user
     */
    public function getUserPosts(string $userId, int $perPage = self::DEFAULT_PER_PAGE)
    {
        return FeedPost::with(['user', 'category', 'media', 'product'])
            ->byUser($userId)
            ->published()
            ->orderBy('published_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get posts by category
     */
    public function getCategoryPosts(string $categoryId, int $perPage = self::DEFAULT_PER_PAGE)
    {
        return FeedPost::with(['user', 'category', 'media', 'product'])
            ->byCategory($categoryId)
            ->published()
            ->orderBy('published_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get posts by hashtag
     */
    public function getHashtagPosts(string $hashtag, int $perPage = self::DEFAULT_PER_PAGE)
    {
        return FeedPost::with(['user', 'category', 'media', 'product'])
            ->withHashtag($hashtag)
            ->published()
            ->orderBy('published_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get saved posts for a user
     */
    public function getSavedPosts(string $userId, int $perPage = self::DEFAULT_PER_PAGE)
    {
        return FeedPost::with(['user', 'category', 'media', 'product'])
            ->whereHas('saves', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->orderBy('feed_posts.published_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Search posts
     */
    public function searchPosts(string $query, int $perPage = self::DEFAULT_PER_PAGE)
    {
        return FeedPost::with(['user', 'category', 'media', 'product'])
            ->published()
            ->where(function ($q) use ($query) {
                $q->where('content', 'LIKE', "%{$query}%")
                  ->orWhereHas('user', function ($uq) use ($query) {
                      $uq->where('first_name', 'LIKE', "%{$query}%")
                         ->orWhere('last_name', 'LIKE', "%{$query}%");
                  })
                  ->orWhereJsonContains('hashtags', $query);
            })
            ->orderBy('published_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get trending posts (most liked/commented in last 7 days)
     */
    public function getTrendingPosts(int $limit = 10)
    {
        return FeedPost::with(['user', 'category', 'media'])
            ->published()
            ->where('published_at', '>=', now()->subDays(7))
            ->orderByRaw('(likes_count + comments_count + shares_count) DESC')
            ->limit($limit)
            ->get();
    }

    /**
     * Get sponsored posts for a user's feed
     */
    public function getSponsoredPosts(string $userId, int $limit = 5)
    {
        return FeedSponsoredPost::with(['post.user', 'post.category', 'post.media'])
            ->active()
            ->whereHas('post', function ($q) {
                $q->published();
            })
            ->inRandomOrder()
            ->limit($limit)
            ->get()
            ->pluck('post');
    }
}