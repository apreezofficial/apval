<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationFeedController
{
    /**
     * Get paginated list of user notifications and unread count.
     * GET /api/notifications
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = (int) $request->query('per_page', 20);

        $notifications = $user->notifications()->paginate($perPage);
        $unreadCount = $user->unreadNotifications()->count();

        return response()->json([
            'status' => 'success',
            'data'   => [
                'unread_count'  => $unreadCount,
                'notifications' => $notifications,
            ],
        ]);
    }

    /**
     * Mark a single notification as read.
     * POST /api/notifications/{id}/read
     */
    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $notification = $user->notifications()->findOrFail($id);

        $notification->markAsRead();

        return response()->json([
            'status'  => 'success',
            'message' => 'Notification marked as read.',
            'data'    => $notification,
        ]);
    }

    /**
     * Mark all notifications for the user as read.
     * POST /api/notifications/read-all
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->unreadNotifications->markAsRead();

        return response()->json([
            'status'  => 'success',
            'message' => 'All notifications marked as read.',
        ]);
    }
}
