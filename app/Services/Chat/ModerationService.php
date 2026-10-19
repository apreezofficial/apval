<?php

declare(strict_types=1);

namespace App\Services\Chat;

use App\Models\ChatReport;
use App\Models\ChatBlockedUser;
use App\Models\ChatMessage;
use Illuminate\Support\Str;

class ModerationService
{
    /**
     * Report a message
     */
    public function reportMessage(
        string $messageId,
        string $reporterId,
        string $reason,
        ?string $description = null
    ): ChatReport {
        $message = ChatMessage::findOrFail($messageId);

        return ChatReport::create([
            'id' => (string) Str::uuid(),
            'reporter_id' => $reporterId,
            'target_type' => ChatReport::TARGET_MESSAGE,
            'target_id' => $messageId,
            'conversation_id' => $message->conversation_id,
            'message_id' => $messageId,
            'reason' => $reason,
            'description' => $description,
            'status' => ChatReport::STATUS_PENDING,
        ]);
    }

    /**
     * Report a user
     */
    public function reportUser(
        string $targetUserId,
        string $reporterId,
        string $reason,
        ?string $description = null,
        ?string $conversationId = null
    ): ChatReport {
        return ChatReport::create([
            'id' => (string) Str::uuid(),
            'reporter_id' => $reporterId,
            'target_type' => ChatReport::TARGET_USER,
            'target_id' => $targetUserId,
            'conversation_id' => $conversationId,
            'reason' => $reason,
            'description' => $description,
            'status' => ChatReport::STATUS_PENDING,
        ]);
    }

    /**
     * Report a community
     */
    public function reportCommunity(
        string $communityId,
        string $reporterId,
        string $reason,
        ?string $description = null
    ): ChatReport {
        return ChatReport::create([
            'id' => (string) Str::uuid(),
            'reporter_id' => $reporterId,
            'target_type' => ChatReport::TARGET_COMMUNITY,
            'target_id' => $communityId,
            'reason' => $reason,
            'description' => $description,
            'status' => ChatReport::STATUS_PENDING,
        ]);
    }

    /**
     * Block a user
     */
    public function blockUser(string $userId, string $blockedUserId, ?string $reason = null): ChatBlockedUser
    {
        if ($userId === $blockedUserId) {
            throw new \Exception('You cannot block yourself');
        }

        return ChatBlockedUser::block($userId, $blockedUserId, $reason);
    }

    /**
     * Unblock a user
     */
    public function unblockUser(string $userId, string $blockedUserId): bool
    {
        return ChatBlockedUser::unblock($userId, $blockedUserId);
    }

    /**
     * Get user's blocked users
     */
    public function getBlockedUsers(string $userId)
    {
        return ChatBlockedUser::blockedBy($userId)
            ->with(['blockedUser'])
            ->get();
    }

    /**
     * Check if user is blocked by another user
     */
    public function isBlocked(string $userId, string $blockedUserId): bool
    {
        return ChatBlockedUser::isBlocked($userId, $blockedUserId);
    }

    /**
     * Get pending reports (admin only)
     */
    public function getPendingReports(int $perPage = 20)
    {
        return ChatReport::pending()
            ->with(['reporter', 'message', 'conversation'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Review a report (admin only)
     */
    public function reviewReport(
        string $reportId,
        string $reviewerId,
        string $status,
        ?string $notes = null
    ): ChatReport {
        $report = ChatReport::findOrFail($reportId);

        if (!in_array($status, [ChatReport::STATUS_REVIEWING, ChatReport::STATUS_RESOLVED, ChatReport::STATUS_DISMISSED])) {
            throw new \Exception('Invalid status');
        }

        if ($status === ChatReport::STATUS_RESOLVED) {
            $report->resolve($reviewerId, $notes ?? 'Report resolved');
        } elseif ($status === ChatReport::STATUS_DISMISSED) {
            $report->dismiss($reviewerId, $notes ?? 'Report dismissed');
        } else {
            $report->markAsReviewing($reviewerId);
        }

        return $report->fresh();
    }

    /**
     * Get all reports (admin only)
     */
    public function getAllReports(?string $status = null, int $perPage = 20)
    {
        $query = ChatReport::with(['reporter', 'message', 'conversation'])
            ->orderBy('created_at', 'desc');

        if ($status) {
            $query->where('status', $status);
        }

        return $query->paginate($perPage);
    }
}