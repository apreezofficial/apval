<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Models\KycRecord;
use App\Notifications\KycStatusUpdatedNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KycAdminController
{
    /**
     * GET /api/admin/kyc/pending
     * List all KYC records awaiting admin review.
     */
    public function pending(): JsonResponse
    {
        $records = KycRecord::with('user:id,first_name,last_name,email,phone')
            ->where('status', 'pending_review')
            ->latest('updated_at')
            ->paginate(20);

        return response()->json([
            'status' => 'success',
            'data'   => $records,
        ]);
    }

    /**
     * GET /api/admin/kyc
     * List all KYC records (any status), with optional ?status= filter.
     */
    public function index(Request $request): JsonResponse
    {
        $records = KycRecord::with('user:id,first_name,last_name,email,phone')
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->latest('updated_at')
            ->paginate(20);

        return response()->json([
            'status' => 'success',
            'data'   => $records,
        ]);
    }

    /**
     * POST /api/admin/kyc/{id}/approve
     * Approve a pending_review submission and bump the user's KYC tier.
     */
    public function approve(Request $request, string $id): JsonResponse
    {
        $kyc = KycRecord::with('user')->findOrFail($id);

        if ($kyc->status !== 'pending_review') {
            return response()->json([
                'status'  => 'error',
                'code'    => 'KYC_NOT_PENDING',
                'message' => 'This KYC record is not pending review.',
            ], 422);
        }

        $newLevel = ($kyc->level ?? 1) + 1;

        $kyc->update([
            'status'      => 'verified',
            'level'       => $newLevel,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'rejection_reason' => null,
        ]);

        // Notify user
        if ($kyc->user) {
            $kyc->user->notify(new KycStatusUpdatedNotification('verified', $newLevel));
        }

        return response()->json([
            'status'  => 'success',
            'message' => "KYC record approved. User upgraded to Tier {$newLevel}.",
            'data'    => [
                'kyc_id'    => $kyc->id,
                'new_level' => $newLevel,
                'status'    => 'verified',
            ],
        ]);
    }

    /**
     * POST /api/admin/kyc/{id}/reject
     * Reject a pending_review submission with a reason.
     */
    public function reject(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $kyc = KycRecord::with('user')->findOrFail($id);

        if ($kyc->status !== 'pending_review') {
            return response()->json([
                'status'  => 'error',
                'code'    => 'KYC_NOT_PENDING',
                'message' => 'This KYC record is not pending review.',
            ], 422);
        }

        // Revert to verified (their previous passing tier) but mark as rejected_upgrade
        $kyc->update([
            'status'           => 'upgrade_rejected',
            'reviewed_by'      => $request->user()->id,
            'reviewed_at'      => now(),
            'rejection_reason' => $request->reason,
        ]);

        // Notify user
        if ($kyc->user) {
            $kyc->user->notify(new KycStatusUpdatedNotification(
                'rejected',
                $kyc->level,
                $request->reason
            ));
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'KYC upgrade rejected.',
            'data'    => [
                'kyc_id'  => $kyc->id,
                'reason'  => $request->reason,
                'status'  => 'upgrade_rejected',
            ],
        ]);
    }
}
