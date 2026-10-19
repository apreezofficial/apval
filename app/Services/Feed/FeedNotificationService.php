<?php

declare(strict_types=1);

namespace App\Services\Feed;

use App\Models\FeedNotification;
use App\Models\User;
use Illuminate\Support\Str;

class FeedNotificationService
{
    /**
     * Notify post owner of a like
     */
    public function notifyPostLiked(string $postId, string $likerId): void
    {
        $post = \App\Models\FeedPost::find($postId);
        if (!$post || $post->user_id === $likerId) return;

        $liker = User::find($likerId);
        if (!$liker) return;

        $this->createNotification(
            userId: $post->user_id,
            senderId: $likerId,
            type: 'post_liked',
            referenceType: 'post',
            referenceId: $postId,
            message: "{$liker->first_name} {$liker->last_name} liked your post"
        );
    }

    /**
     * Notify post owner of a comment
     */
    public function notifyPostCommented(string $postId, string $commenterId, string $commentId): void
    {
        $post = \App\Models\FeedPost::find($postId);
        if (!$post || $post->user_id === $commenterId) return;

        $commenter = User::find($commenterId);
        if (!$commenter) return;

        $this->createNotification(
            userId: $post->user_id,
            senderId: $commenterId,
            type: 'post_commented',
            referenceType: 'comment',
            referenceId: $commentId,
            message: "{$commenter->first_name} {$commenter->last_name} commented on your post"
        );
    }

    /**
     * Notify parent comment owner of a reply
     */
    public function notifyCommentReplied(string $parentCommentId, string $replierId, string $replyId): void
    {
        $parentComment = \App\Models\FeedComment::find($parentCommentId);
        if (!$parentComment || $parentComment->user_id === $replierId) return;

        $replier = User::find($replierId);
        if (!$replier) return;

        $this->createNotification(
            userId: $parentComment->user_id,
            senderId: $replierId,
            type: 'comment_replied',
            referenceType: 'comment',
            referenceId: $replyId,
            message: "{$replier->first_name} {$replier->last_name} replied to your comment"
        );
    }

    /**
     * Notify post owner of a share
     */
    public function notifyPostShared(string $postId, string $sharerId): void
    {
        $post = \App\Models\FeedPost::find($postId);
        if (!$post || $post->user_id === $sharerId) return;

        $sharer = User::find($sharerId);
        if (!$sharer) return;

        $this->createNotification(
            userId: $post->user_id,
            senderId: $sharerId,
            type: 'post_shared',
            referenceType: 'post',
            referenceId: $postId,
            message: "{$sharer->first_name} {$sharer->last_name} shared your post"
        );
    }

    /**
     * Notify user of a new follower
     */
    public function notifyNewFollower(string $followerId, string $followingId): void
    {
        $follower = User::find($followerId);
        if (!$follower) return;

        $this->createNotification(
            userId: $followingId,
            senderId: $followerId,
            type: 'new_follower',
            referenceType: 'user',
            referenceId: $followerId,
            message: "{$follower->first_name} {$follower->last_name} started following you"
        );
    }

    /**
     * Notify mentioned users
     */
    public function notifyMentions(string $postId, array $mentionedUserIds, string $senderId): void
    {
        $sender = User::find($senderId);
        if (!$sender) return;

        foreach ($mentionedUserIds as $userId) {
            if ($userId === $senderId) continue;

            $this->createNotification(
                userId: $userId,
                senderId: $senderId,
                type: 'mention',
                referenceType: 'post',
                referenceId: $postId,
                message: "{$sender->first_name} {$sender->last_name} mentioned you in a post"
            );
        }
    }

    /**
     * Notify user their post was featured by admin
     */
    public function notifyPostFeatured(string $postId, string $userId): void
    {
        $this->createNotification(
            userId: $userId,
            senderId: null,
            type: 'post_featured',
            referenceType: 'post',
            referenceId: $postId,
            message: "Your post has been featured by AgreonPay!"
        );
    }

    /**
     * Send admin announcement to all users
     */
    public function sendAdminAnnouncement(string $message, ?string $referenceId = null): int
    {
        $users = User::where('status', 'active')->get();
        $count = 0;

        foreach ($users as $user) {
            $this->createNotification(
                userId: $user->id,
                senderId: null,
                type: 'admin_announcement',
                referenceType: 'post',
                referenceId: $referenceId,
                message: $message
            );
            $count++;
        }

        return $count;
    }

    /**
     * Create a notification
     */
    private function createNotification(
        string $userId,
        ?string $senderId,
        string $type,
        string $referenceType,
        ?string $referenceId,
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