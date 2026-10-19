<?php

declare(strict_types=1);

namespace App\Http\Controllers\Feed;

use App\Services\Feed\AdminFeedService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminFeedController
{
    public function __construct(
        private AdminFeedService $adminFeedService
    ) {}

    /**
     * POST /api/v1/feed/admin/posts/{id}/pin
     */
    public function pinPost(string $postId): JsonResponse
    {
        try {
            $this->adminFeedService->pinPost($postId);
            return response()->json(['success' => true, 'message' => 'Post pinned']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * POST /api/v1/feed/admin/posts/{id}/unpin
     */
    public function unpinPost(string $postId): JsonResponse
    {
        try {
            $this->adminFeedService->unpinPost($postId);
            return response()->json(['success' => true, 'message' => 'Post unpinned']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * POST /api/v1/feed/admin/posts/{id}/feature
     */
    public function featurePost(string $postId): JsonResponse
    {
        try {
            $this->adminFeedService->featurePost($postId);
            return response()->json(['success' => true, 'message' => 'Post featured']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * POST /api/v1/feed/admin/posts/{id}/unfeature
     */
    public function unfeaturePost(string $postId): JsonResponse
    {
        try {
            $this->adminFeedService->unfeaturePost($postId);
            return response()->json(['success' => true, 'message' => 'Post unfeatured']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * POST /api/v1/feed/admin/posts/{id}/hide
     */
    public function hidePost(string $postId): JsonResponse
    {
        try {
            $this->adminFeedService->hidePost($postId);
            return response()->json(['success' => true, 'message' => 'Post hidden']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * POST /api/v1/feed/admin/posts/{id}/publish
     */
    public function publishPost(string $postId): JsonResponse
    {
        try {
            $this->adminFeedService->publishPost($postId);
            return response()->json(['success' => true, 'message' => 'Post published']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * DELETE /api/v1/feed/admin/posts/{id}
     */
    public function deletePost(string $postId): JsonResponse
    {
        try {
            $this->adminFeedService->deletePost($postId);
            return response()->json(['success' => true, 'message' => 'Post deleted']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * POST /api/v1/feed/admin/comments/{id}/hide
     */
    public function hideComment(string $commentId): JsonResponse
    {
        try {
            $this->adminFeedService->hideComment($commentId);
            return response()->json(['success' => true, 'message' => 'Comment hidden']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * POST /api/v1/feed/admin/comments/{id}/remove
     */
    public function removeComment(string $commentId): JsonResponse
    {
        try {
            $this->adminFeedService->removeComment($commentId);
            return response()->json(['success' => true, 'message' => 'Comment removed']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * GET /api/v1/feed/admin/reports
     */
    public function getReports(Request $request): JsonResponse
    {
        $status = $request->get('status');
        $perPage = (int) $request->get('per_page', 20);

        $reports = $this->adminFeedService->getReports($status, $perPage);

        return response()->json([
            'success' => true,
            'data' => $reports->map(fn($report) => [
                'id' => $report->id,
                'reporter' => [
                    'id' => $report->reporter->id,
                    'name' => $report->reporter->first_name . ' ' . $report->reporter->last_name,
                ],
                'target_type' => $report->target_type,
                'target_id' => $report->target_id,
                'reason' => $report->reason,
                'description' => $report->description,
                'status' => $report->status,
                'created_at' => $report->created_at->toISOString(),
            ]),
            'meta' => [
                'current_page' => $reports->currentPage(),
                'last_page' => $reports->lastPage(),
                'per_page' => $reports->perPage(),
                'total' => $reports->total(),
            ],
        ]);
    }

    /**
     * POST /api/v1/feed/admin/reports/{id}/resolve
     */
    public function resolveReport(Request $request, string $reportId): JsonResponse
    {
        $request->validate(['notes' => 'nullable|string|max:1000']);
        $user = Auth::user();

        try {
            $this->adminFeedService->resolveReport($reportId, $user->id, $request->notes ?? '');
            return response()->json(['success' => true, 'message' => 'Report resolved']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * POST /api/v1/feed/admin/reports/{id}/dismiss
     */
    public function dismissReport(Request $request, string $reportId): JsonResponse
    {
        $request->validate(['notes' => 'nullable|string|max:1000']);
        $user = Auth::user();

        try {
            $this->adminFeedService->dismissReport($reportId, $user->id, $request->notes ?? '');
            return response()->json(['success' => true, 'message' => 'Report dismissed']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * POST /api/v1/feed/admin/categories
     */
    public function createCategory(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'icon' => 'nullable|string|max:10',
            'color' => 'nullable|string|max:7',
            'parent_id' => 'nullable|uuid|exists:feed_categories,id',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        try {
            $category = $this->adminFeedService->createCategory($request->all());
            return response()->json([
                'success' => true,
                'message' => 'Category created',
                'data' => $category,
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * PUT /api/v1/feed/admin/categories/{id}
     */
    public function updateCategory(Request $request, string $categoryId): JsonResponse
    {
        try {
            $category = $this->adminFeedService->updateCategory($categoryId, $request->all());
            return response()->json([
                'success' => true,
                'message' => 'Category updated',
                'data' => $category,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * DELETE /api/v1/feed/admin/categories/{id}
     */
    public function deleteCategory(string $categoryId): JsonResponse
    {
        try {
            $this->adminFeedService->deleteCategory($categoryId);
            return response()->json(['success' => true, 'message' => 'Category deleted']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * GET /api/v1/feed/admin/statistics
     */
    public function statistics(): JsonResponse
    {
        try {
            $stats = $this->adminFeedService->getStatistics();
            return response()->json([
                'success' => true,
                'data' => $stats,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}