<?php

declare(strict_types=1);

namespace App\Services\Feed;

use App\Models\FeedPost;
use App\Models\FeedComment;
use App\Models\FeedLike;
use App\Models\FeedCommentLike;
use App\Models\FeedShare;
use App\Models\FeedSavedPost;
use App\Models\FeedFollow;
use App\Models\FeedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InteractionService
{
    /**
     * Like a post
     */
    public function likePost(string $postId, string $userId): FeedLike
    {
        $post = FeedPost::findOrFail($postId);

        $like = $post->like($userId);

        // Create notification
        if ($post->user_id !== $userId) {
            $this->createNotification(
                $post->user_id,
                $userId,
                'post_liked',
                'post',
                $postId,
                'liked your post'
            );
        }

        return $like;
    }

    /**
     * Unlike a post
     */
    public function unlikePost(string $postId, string $userId): bool
    {
        $post = FeedPost::findOrFail($postId);
        return $post->unlike($userId);
    }

    /**
     * Add a comment to a post
     */
    public function addComment(
        string $postId,
        string $userId,
        string $content,
        ?string $parentId = null
    ): FeedComment {
        $post = FeedPost::findOrFail($postId);

        // Validate parent comment exists if replying
        if ($parentId) {
            $parentComment = FeedComment::findOrFail($parentId);
            if ($parentComment->post_id !== $postId) {
                throw new \Exception('Parent comment does not belong to this post');
            }
        }

        $comment = FeedComment::create([
            'id' => (string) Str::uuid(),
            'post_id' => $postId,
            'user_id' => $userId,
            'parent_id' => $parentId,
            'content' => $content,
            'status' => 'published',
        ]);

        // Create notification for post owner
        if ($post->user_id !== $userId) {
            $this->createNotification(
                $post->user_id,
                $userId,
                'post_commented',
                'post',
                $postId,
                'commented on your post'
            );
        }

        // Create notification for parent comment owner if replying
        if ($parentId) {
            $parentComment = FeedComment::find($parentId);
            if ($parentComment && $parentComment->user_id !== $userId) {
                $this->createNotification(
                    $parentComment->user_id,
                    $userId,
                    'comment_replied',
                    'comment',
                    $parentId,
                    'replied to your comment'
                );
            }
        }

        return $comment->load('user');
    }

    /**
     * Update a comment
     */
    public function updateComment(string $commentId, string $userId, string $content): FeedComment
    {
        $comment = FeedComment::findOrFail($commentId);

        if ($comment->user_id !== $userId) {
            throw new \Exception('You can only edit your own comments');
        }

        $comment->update(['content' => $content]);

        return $comment->fresh();
    }

    /**
     * Delete a comment
     */
    public function deleteComment(string $commentId, string $userId): void
    {
        $comment = FeedComment::findOrFail($commentId);

        if ($comment->user_id !== $userId) {
            throw new \Exception('You can only delete your own comments');
        }

        $comment->delete();
    }

    /**
     * Like a comment
     */
    public function likeComment(string $commentId, string $userId): FeedCommentLike
    {
        $comment = FeedComment::findOrFail($commentId);

        $like = $comment->like($userId);

        // Create notification
        if ($comment->user_id !== $userId) {
            $this->createNotification(
                $comment->user_id,
                $userId,
                'comment_liked',
                'comment',
                $commentId,
                'liked your comment'
            );
        }

        return $like;
    }

    /**
     * Unlike a comment
     */
    public function unlikeComment(string $commentId, string $userId): bool
    {
        $comment = FeedComment::findOrFail($commentId);
        return $comment->unlike($userId);
    }

    /**
     * Share a post
     */
    public function sharePost(
        string $postId,
        string $userId,
        string $shareType = 'internal',
        ?string $caption = null
    ): FeedShare {
        $post = FeedPost::findOrFail($postId);

        $share = FeedShare::create([
            'id' => (string) Str::uuid(),
            'user_id' => $userId,
            'post_id' => $postId,
            'share_type' => $shareType,
            'caption' => $caption,
            'created_at' => now(),
        ]);

        // Create notification
        if ($post->user_id !== $userId) {
            $this->createNotification(
                $post->user_id,
                $userId,
                'post_shared',
                'post',
                $postId,
                'shared your post'
            );
        }

        return $share;
    }

    /**
     * Save a post
     */
    public function savePost(string $postId, string $userId, ?string $folderName = null): FeedSavedPost
    {
        $post = FeedPost::findOrFail($postId);
        return $post->save_post($userId, $folderName);
    }

    /**
     * Unsave a post
     */
    public function unsavePost(string $postId, string $userId): bool
    {
        $post = FeedPost::findOrFail($postId);
        return $post->unsave($userId);
    }

    /**
     * Follow a user
     */
    public function followUser(string $followerId, string $followingId): FeedFollow
    {
        if ($followerId === $followingId) {
            throw new \Exception('You cannot follow yourself');
        }

        $follow = FeedFollow::follow($followerId, $followingId);

        // Create notification
        $this->createNotification(
            $followingId,
            $followerId,
            'new_follower',
            'user',
            $followerId,
            'started following you'
        );

        return $follow;
    }

    /**
     * Unfollow a user
     */
    public function unfollowUser(string $followerId, string $followingId): bool
    {
        return FeedFollow::unfollow($followerId, $followingId);
    }

    /**
     * Check if user is following another user
     */
    public function isFollowing(string $followerId, string $followingId): bool
    {
        return FeedFollow::isFollowing($followerId, $followingId);
    }

    /**
     * Get followers count
     */
    public function getFollowerCount(string $userId): int
    {
        return FeedFollow::getFollowerCount($userId);
    }

    /**
     * Get following count
     */
    public function getFollowingCount(string $userId): int
    {
        return FeedFollow::getFollowingCount($userId);
    }

    /**
     * Create a notification
     */
    private function createNotification(
        string $userId,
        string $senderId,
        string $type,
        string $referenceType,
        string $referenceId,
        string $message
    ): void {
        FeedNotification::create([
            'id' => (string) Str::uuid(),
            'user_id' => $userId,
            'sender_id' => $senderId,
            'type' => $type,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'message' => $message,
            'is_read' => false,
        ]);
    }
}