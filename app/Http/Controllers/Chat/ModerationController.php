<?php

declare(strict_types=1);

namespace App\Http\Controllers\Chat;

use App\Http\Requests\Chat\ReportRequest;
use App\Services\Chat\ModerationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ModerationController
{
    public function __construct(
        private ModerationService $moderationService
    ) {}

    /**
     * Report a message
     * POST /api/v1/chat/reports/message
     */
    public function reportMessage(ReportRequest $request): JsonResponse
    {
        $request->validate([
            'message_id' => 'required|uuid|exists:chat_messages,id',
        ]);

        $user = Auth::user();

        try {
            $report = $this->moderationService->reportMessage(
                $request->message_id,
                $user->id,
                $request->reason,
                $request->description
            );

            return response()->json([
                'success' => true,
                'message' => 'Report submitted',
                'data' => [
                    'report_id' => $report->id,
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Report a user
     * POST /api/v1/chat/reports/user
     */
    public function reportUser(ReportRequest $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|uuid|exists:users,id',
            'conversation_id' => 'nullable|uuid|exists:chat_conversations,id',
        ]);

        $user = Auth::user();

        try {
            $report = $this->moderationService->reportUser(
                $request->user_id,
                $user->id,
                $request->reason,
                $request->description,
                $request->conversation_id
            );

            return response()->json([
                'success' => true,
                'message' => 'Report submitted',
                'data' => [
                    'report_id' => $report->id,
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Block a user
     * POST /api/v1/chat/blocks
     */
    public function blockUser(Request $request): JsonResponse
    {
        $request->validate([
            'blocked_user_id' => 'required|uuid|exists:users,id',
            'reason' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();

        try {
            $this->moderationService->blockUser(
                $user->id,
                $request->blocked_user_id,
                $request->reason
            );

            return response()->json([
                'success' => true,
                'message' => 'User blocked',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Unblock a user
     * DELETE /api/v1/chat/blocks/{userId}
     */
    public function unblockUser(string $userId): JsonResponse
    {
        $user = Auth::user();

        try {
            $this->moderationService->unblockUser($user->id, $userId);

            return response()->json([
                'success' => true,
                'message' => 'User unblocked',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get blocked users
     * GET /api/v1/chat/blocks
     */
    public function getBlockedUsers(): JsonResponse
    {
        $user = Auth::user();
        $blockedUsers = $this->moderationService->getBlockedUsers($user->id);

        return response()->json([
            'success' => true,
            'data' => $blockedUsers->map(fn($block) => [
                'user_id' => $block->blockedUser->id,
                'name' => $block->blockedUser->first_name . ' ' . $block->blockedUser->last_name,
                'profile_picture' => $block->blockedUser->profile_picture,
                'reason' => $block->reason,
                'blocked_at' => $block->created_at->toISOString(),
            ]),
        ]);
    }

    /**
     * Get pending reports (admin only)
     * GET /api/v1/chat/admin/reports/pending
     */
    public function getPendingReports(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 20);
        $reports = $this->moderationService->getPendingReports((int) $perPage);

        return response()->json([
            'success' => true,
            'data' => $reports->map(fn($report) => $this->formatReport($report)),
            'meta' => [
                'current_page' => $reports->currentPage(),
                'last_page' => $reports->lastPage(),
                'per_page' => $reports->perPage(),
                'total' => $reports->total(),
            ],
        ]);
    }

    /**
     * Get all reports (admin only)
     * GET /api/v1/chat/admin/reports
     */
    public function getAllReports(Request $request): JsonResponse
    {
        $status = $request->get('status');
        $perPage = $request->get('per_page', 20);
        $reports = $this->moderationService->getAllReports($status, (int) $perPage);

        return response()->json([
            'success' => true,
            'data' => $reports->map(fn($report) => $this->formatReport($report)),
            'meta' => [
                'current_page' => $reports->currentPage(),
                'last_page' => $reports->lastPage(),
                'per_page' => $reports->perPage(),
                'total' => $reports->total(),
            ],
        ]);
    }

    /**
     * Review a report (admin only)
     * POST /api/v1/chat/admin/reports/{id}/review
     */
    public function reviewReport(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:reviewing,resolved,dismissed',
            'notes' => 'nullable|string|max:1000',
        ]);

        $user = Auth::user();

        try {
            $report = $this->moderationService->reviewReport(
                $id,
                $user->id,
                $request->status,
                $request->notes
            );

            return response()->json([
                'success' => true,
                'message' => 'Report reviewed',
                'data' => $this->formatReport($report),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Format report for API response
     */
    private function formatReport($report): array
    {
        return [
            'id' => $report->id,
            'reporter' => [
                'user_id' => $report->reporter->id,
                'name' => $report->reporter->first_name . ' ' . $report->reporter->last_name,
            ],
            'target_type' => $report->target_type,
            'target_id' => $report->target_id,
            'reason' => $report->reason,
            'description' => $report->description,
            'status' => $report->status,
            'conversation_id' => $report->conversation_id,
            'message' => $report->message ? [
                'id' => $report->message->id,
                'content' => $report->message->content,
                'sender_id' => $report->message->sender_id,
            ] : null,
            'reviewer' => $report->reviewer ? [
                'user_id' => $report->reviewer->id,
                'name' => $report->reviewer->first_name . ' ' . $report->reviewer->last_name,
            ] : null,
            'resolution_notes' => $report->resolution_notes,
            'created_at' => $report->created_at->toISOString(),
            'resolved_at' => $report->resolved_at?->toISOString(),
        ];
    }
}