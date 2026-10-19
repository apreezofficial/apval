<?php

declare(strict_types=1);

namespace App\Services\Feed;

use App\Models\FeedPost;
use App\Models\FeedComment;
use App\Models\FeedReport;
use App\Models\FeedCategory;
use Illuminate\Support\Facades\DB;

class AdminFeedService
{
    /**
     * Pin a post
     */
    public function pinPost(string $postId): void
    {
        $post = FeedPost::findOrFail($postId);
        $post->pin();
    }

    /**
     * Unpin a post
     */
    public function unpinPost(string $postId): void
    {
        $post = FeedPost::findOrFail($postId);
        $post->unpin();
    }

    /**
     * Feature a post
     */
    public function featurePost(string $postId): void
    {
        $post = FeedPost::findOrFail($postId);
        $post->feature();
    }

    /**
     * Unfeature a post
     */
    public function unfeaturePost(string $postId): void
    {
        $post = FeedPost::findOrFail($postId);
        $post->unfeature();
    }

    /**
     * Hide a post
     */
    public function hidePost(string $postId): void
    {
        $post = FeedPost::findOrFail($postId);
        $post->hide();
    }

    /**
     * Publish a post
     */
    public function publishPost(string $postId): void
    {
        $post = FeedPost::findOrFail($postId);
        $post->publish();
    }

    /**
     * Delete a post (admin can delete any post)
     */
    public function deletePost(string $postId): void
    {
        $post = FeedPost::findOrFail($postId);
        $post->delete();
    }

    /**
     * Hide a comment
     */
    public function hideComment(string $commentId): void
    {
        $comment = FeedComment::findOrFail($commentId);
        $comment->hide();
    }

    /**
     * Remove a comment
     */
    public function removeComment(string $commentId): void
    {
        $comment = FeedComment::findOrFail($commentId);
        $comment->remove();
    }

    /**
     * Get all reports
     */
    public function getReports(?string $status = null, int $perPage = 20)
    {
        $query = FeedReport::with(['reporter', 'target'])->orderBy('created_at', 'desc');

        if ($status) {
            $query->where('status', $status);
        }

        return $query->paginate($perPage);
    }

    /**
     * Get pending reports
     */
    public function getPendingReports(int $perPage = 20)
    {
        return FeedReport::with(['reporter', 'target'])
            ->pending()
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Resolve a report
     */
    public function resolveReport(string $reportId, string $reviewerId, string $notes): void
    {
        $report = FeedReport::findOrFail($reportId);
        $report->resolve($reviewerId, $notes);

        // Optionally hide/remove the reported content
        if ($report->target_type === 'post') {
            $this->hidePost($report->target_id);
        } elseif ($report->target_type === 'comment') {
            $this->hideComment($report->target_id);
        }
    }

    /**
     * Dismiss a report
     */
    public function dismissReport(string $reportId, string $reviewerId, string $notes): void
    {
        $report = FeedReport::findOrFail($reportId);
        $report->dismiss($reviewerId, $notes);
    }

    /**
     * Create a category
     */
    public function createCategory(array $data): FeedCategory
    {
        return FeedCategory::create($data);
    }

    /**
     * Update a category
     */
    public function updateCategory(string $categoryId, array $data): FeedCategory
    {
        $category = FeedCategory::findOrFail($categoryId);
        $category->update($data);
        return $category->fresh();
    }

    /**
     * Delete a category
     */
    public function deleteCategory(string $categoryId): void
    {
        $category = FeedCategory::findOrFail($categoryId);
        $category->delete();
    }

    /**
     * Get feed statistics
     */
    public function getStatistics(): array
    {
        return [
            'total_posts' => FeedPost::count(),
            'published_posts' => FeedPost::published()->count(),
            'pinned_posts' => FeedPost::pinned()->count(),
            'featured_posts' => FeedPost::featured()->count(),
            'flagged_posts' => FeedPost::where('is_flagged', true)->count(),
            'total_comments' => FeedComment::count(),
            'pending_reports' => FeedReport::pending()->count(),
            'total_categories' => FeedCategory::active()->count(),
        ];
    }
}