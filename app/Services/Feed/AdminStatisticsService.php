<?php

declare(strict_types=1);

namespace App\Services\Feed;

use App\Models\FeedPost;
use App\Models\FeedComment;
use App\Models\FeedLike;
use App\Models\FeedShare;
use App\Models\FeedSavedPost;
use App\Models\FeedFollow;
use App\Models\FeedReport;
use App\Models\FeedCategory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminStatisticsService
{
    /**
     * Get comprehensive feed statistics
     */
    public function getStatistics(?string $startDate = null, ?string $endDate = null): array
    {
        $start = $startDate ? Carbon::parse($startDate) : now()->subDays(30);
        $end = $endDate ? Carbon::parse($endDate) : now();

        return [
            'overview' => $this->getOverviewStats($start, $end),
            'engagement' => $this->getEngagementStats($start, $end),
            'content' => $this->getContentStats($start, $end),
            'users' => $this->getUserStats($start, $end),
            'moderation' => $this->getModerationStats($start, $end),
            'trending' => $this->getTrendingStats($start, $end),
        ];
    }

    private function getOverviewStats(Carbon $start, Carbon $end): array
    {
        return [
            'total_posts' => FeedPost::count(),
            'total_comments' => FeedComment::count(),
            'total_likes' => FeedLike::count(),
            'total_shares' => FeedShare::count(),
            'total_saves' => FeedSavedPost::count(),
            'total_follows' => FeedFollow::count(),
            'active_categories' => FeedCategory::active()->count(),
            'period_posts' => FeedPost::whereBetween('created_at', [$start, $end])->count(),
            'period_comments' => FeedComment::whereBetween('created_at', [$start, $end])->count(),
        ];
    }

    private function getEngagementStats(Carbon $start, Carbon $end): array
    {
        $posts = FeedPost::whereBetween('created_at', [$start, $end])->get();

        return [
            'avg_likes_per_post' => $posts->avg('likes_count'),
            'avg_comments_per_post' => $posts->avg('comments_count'),
            'avg_shares_per_post' => $posts->avg('shares_count'),
            'avg_saves_per_post' => $posts->avg('saves_count'),
            'avg_views_per_post' => $posts->avg('views_count'),
            'engagement_rate' => $this->calculateEngagementRate($posts),
        ];
    }

    private function getContentStats(Carbon $start, Carbon $end): array
    {
        return [
            'posts_by_type' => FeedPost::whereBetween('created_at', [$start, $end])
                ->select('post_type', DB::raw('count(*) as count'))
                ->groupBy('post_type')
                ->pluck('count', 'post_type')
                ->toArray(),
            'posts_by_category' => FeedPost::whereBetween('created_at', [$start, $end])
                ->join('feed_categories', 'feed_posts.category_id', '=', 'feed_categories.id')
                ->select('feed_categories.name', DB::raw('count(*) as count'))
                ->groupBy('feed_categories.name')
                ->pluck('count', 'name')
                ->toArray(),
            'pinned_posts' => FeedPost::pinned()->count(),
            'featured_posts' => FeedPost::featured()->count(),
            'flagged_posts' => FeedPost::where('is_flagged', true)->count(),
        ];
    }

    private function getUserStats(Carbon $start, Carbon $end): array
    {
        return [
            'active_posters' => FeedPost::whereBetween('created_at', [$start, $end])
                ->distinct('user_id')->count('user_id'),
            'active_commenters' => FeedComment::whereBetween('created_at', [$start, $end])
                ->distinct('user_id')->count('user_id'),
            'new_follows' => FeedFollow::whereBetween('created_at', [$start, $end])->count(),
            'top_posters' => User::select('users.id', 'users.first_name', 'users.last_name')
                ->selectRaw('COUNT(feed_posts.id) as post_count')
                ->join('feed_posts', 'users.id', '=', 'feed_posts.user_id')
                ->whereBetween('feed_posts.created_at', [$start, $end])
                ->groupBy('users.id', 'users.first_name', 'users.last_name')
                ->orderByDesc('post_count')
                ->limit(10)
                ->get()
                ->toArray(),
        ];
    }

    private function getModerationStats(Carbon $start, Carbon $end): array
    {
        return [
            'total_reports' => FeedReport::whereBetween('created_at', [$start, $end])->count(),
            'pending_reports' => FeedReport::pending()->count(),
            'resolved_reports' => FeedReport::resolved()->count(),
            'reports_by_reason' => FeedReport::whereBetween('created_at', [$start, $end])
                ->select('reason', DB::raw('count(*) as count'))
                ->groupBy('reason')
                ->pluck('count', 'reason')
                ->toArray(),
            'hidden_posts' => FeedPost::where('status', 'hidden')->count(),
            'removed_posts' => FeedPost::where('status', 'removed')->count(),
        ];
    }

    private function getTrendingStats(Carbon $start, Carbon $end): array
    {
        // Top hashtags
        $hashtags = FeedPost::whereBetween('created_at', [$start, $end])
            ->whereNotNull('hashtags')
            ->pluck('hashtags')
            ->flatten()
            ->countBy()
            ->sortDesc()
            ->take(20)
            ->toArray();

        // Top posts by engagement
        $topPosts = FeedPost::whereBetween('created_at', [$start, $end])
            ->with('user')
            ->orderByRaw('(likes_count + comments_count + shares_count) DESC')
            ->limit(10)
            ->get()
            ->map(fn($post) => [
                'id' => $post->id,
                'author' => $post->user->first_name . ' ' . $post->user->last_name,
                'engagement' => $post->likes_count + $post->comments_count + $post->shares_count,
                'preview' => Str::limit($post->content, 100),
            ])
            ->toArray();

        return [
            'trending_hashtags' => $hashtags,
            'top_posts' => $topPosts,
        ];
    }

    private function calculateEngagementRate($posts): float
    {
        if ($posts->isEmpty()) return 0.0;

        $totalEngagement = $posts->sum(fn($p) => 
            $p->likes_count + $p->comments_count + $p->shares_count + $p->saves_count
        );

        $totalViews = $posts->sum('views_count');

        if ($totalViews === 0) return 0.0;

        return round(($totalEngagement / $totalViews) * 100, 2);
    }
}