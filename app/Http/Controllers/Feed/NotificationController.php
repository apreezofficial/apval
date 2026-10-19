<?php

declare(strict_types=1);

namespace App\Http\Controllers\Feed;

use App\Models\FeedNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController
{
    /**
     * GET /api/v1/feed/notifications
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $perPage = (int) $request->get('per_page', 20);

        $notifications = FeedNotification::with('sender')
            ->forUser($user->id)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        $unreadCount = FeedNotification::forUser($user->id)->unread()->count();

        return response()->json([
            'success' => true,
            'data' => $notifications->map(fn($n) => [
                'id' => $n->id,
                'type' => $n->type,
                'message' => $n->message,
                'sender' => $n->sender ? [
                    'id' => $n->sender->id,
                    'name' => $n->sender->first_name . ' ' . $n->sender->last_name,
                    'profile_picture' => $n->sender->profile_picture,
                ] : null,
                'reference_type' => $n->reference_type,
                'reference_id' => $n->reference_id,
                'is_read' => $n->is_read,
                'created_at' => $n->created_at->toISOString(),
            ]),
            'meta' => [
                'unread_count' => $unreadCount,
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'total' => $notifications->total(),
            ],
        ]);
    }

    /**
     * POST /api/v1/feed/notifications/{id}/read
     */
    public function markAsRead(string $notificationId): JsonResponse
    {
        $user = Auth::user();
        $notification = FeedNotification::where('id', $notificationId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
        ]);
    }

    /**
     * POST /api/v1/feed/notifications/read-all
     */
    public function markAllAsRead(): JsonResponse
    {
        $user = Auth::user();
        $count = FeedNotification::markAllAsRead($user->id);

        return response()->json([
            'success' => true,
            'message' => "{$count} notifications marked as read",
        ]);
    }

    /**
     * GET /api/v1/feed/notifications/unread-count
     */
    public function unreadCount(): JsonResponse
    {
        $user = Auth::user();
        $count = FeedNotification::forUser($user->id)->unread()->count();

        return response()->json([
            'success' => true,
            'data' => ['unread_count' => $count],
        ]);
    }
}