<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Kyc\StoreKycRequest;
use App\Models\KycRecord;
use App\Services\Kyc\BvnVerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KycController
{
    public function __construct(private readonly BvnVerificationService $bvnService) {}

    /**
     * GET /api/userManager/kyc
     *
     * Return the authenticated user's current KYC status.
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        $kyc  = $user->kycRecord;

        $tierTiers = [
            0 => [
                'name' => 'Tier 0',
                'title' => 'Tier 0 (Basic)',
                'daily_limit' => 20000,
                'daily_limit_formatted' => '₦20,000',
                'max_balance' => 50000,
                'max_balance_formatted' => '₦50,000',
                'virtual_account_enabled' => false,
                'requirement' => 'Email Verification',
                'description' => 'Email verified. Upgrade to Tier 1 with BVN to generate your dedicated bank account and increase daily limit to ₦200,000.',
            ],
            1 => [
                'name' => 'Tier 1',
                'title' => 'Tier 1 (BVN Verified)',
                'daily_limit' => 200000,
                'daily_limit_formatted' => '₦200,000',
                'max_balance' => 500000,
                'max_balance_formatted' => '₦500,000',
                'virtual_account_enabled' => true,
                'requirement' => 'BVN Verification',
                'description' => 'BVN verified. Dedicated virtual bank accounts unlocked.',
            ],
            2 => [
                'name' => 'Tier 2',
                'title' => 'Tier 2 (ID Verified)',
                'daily_limit' => 1000000,
                'daily_limit_formatted' => '₦1,000,000',
                'max_balance' => 2000000,
                'max_balance_formatted' => '₦2,000,000',
                'virtual_account_enabled' => true,
                'requirement' => 'Valid ID & Utility Bill',
                'description' => 'Government ID and address verified. Daily limit expanded to ₦1,000,000.',
            ],
            3 => [
                'name' => 'Tier 3',
                'title' => 'Tier 3 (Enterprise)',
                'daily_limit' => 5000000,
                'daily_limit_formatted' => '₦5,000,000',
                'max_balance' => null,
                'max_balance_formatted' => 'Unlimited',
                'virtual_account_enabled' => true,
                'requirement' => 'CAC & Business Verification',
                'description' => 'Corporate account verified with unlimited maximum balance.',
            ],
        ];

        $level = $kyc ? (int)$kyc->level : 0;
        $tierInfo = $tierTiers[$level] ?? $tierTiers[0];

        if (!$kyc) {
            return response()->json([
                'status' => 'success',
                'data'   => [
                    'kyc_status'   => 'pending',
                    'level'        => 0,
                    'bvn_verified' => false,
                    'verified_at'  => null,
                    'tier'         => $tierInfo,
                ],
            ]);
        }

        $kycStatus = $kyc->bvn_verified ? $kyc->status : 'pending';

        return response()->json([
            'status' => 'success',
            'data'   => [
                'kyc_status'   => $kycStatus,
                'level'        => $kyc->level,
                'bvn_verified' => (bool)$kyc->bvn_verified,
                'verified_at'  => $kyc->verified_at?->toIso8601String(),
                'tier'         => $tierInfo,
            ],
        ]);
    }

    /**
     * POST /api/userManager/kyc
     *
     * Submit BVN + DOB for verification. Only allowed when KYC is pending or rejected.
     */
    public function store(StoreKycRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();

        // Lazily initialise the KYC record if it doesn't exist yet
        $kyc = $user->kycRecord ?? KycRecord::create([
            'user_id' => $user->id,
            'level'   => 0,
            'status'  => 'pending',
        ]);

        // Guard: already verified with BVN (Tier 1+) → nothing to do
        if ($kyc->bvn_verified && (int)$kyc->level >= 1) {
            return response()->json([
                'status'  => 'error',
                'code'    => 'KYC_ALREADY_VERIFIED',
                'message' => 'Your identity has already been verified.',
            ], 422);
        }

        // Call Monnify BVN match
        $result = $this->bvnService->verify(
            $data['bvn'],
            $user->first_name,
            $user->last_name,
            $user->phone,
            $data['dob'],
        );

        // Provider is down — don't touch KYC status, ask user to retry
        if (($result['reason'] ?? null) === 'PROVIDER_UNAVAILABLE') {
            return response()->json([
                'status'  => 'error',
                'code'    => 'PROVIDER_UNAVAILABLE',
                'message' => 'BVN verification provider is temporarily unavailable. Please try again later.',
            ], 503);
        }

        if ($result['ok']) {
            // ✅ Verification passed
            $kyc->update([
                'status'            => 'verified',
                'bvn_verified'      => true,
                'bvn'               => encrypt($data['bvn']),
                'dob'               => $data['dob'],
                'level'             => 1,
                'provider_response' => $result['raw'] ?? null,
                'verified_at'       => now(),
            ]);

            // Notify user of KYC approval
            $user->notify(new \App\Notifications\KycStatusUpdatedNotification('verified', 1));

            return response()->json([
                'status'  => 'success',
                'message' => 'KYC verified successfully.',
                'data'    => [
                    'kyc_status'   => 'verified',
                    'level'        => 1,
                    'bvn_verified' => true,
                    'verified_at'  => $kyc->verified_at->toIso8601String(),
                ],
            ]);
        }

        // ❌ BVN details did not match
        $kyc->update([
            'status'            => 'rejected',
            'bvn_verified'      => false,
            'provider_response' => $result['raw'] ?? null,
        ]);

        $failureMessage = $result['message'] ?? 'BVN verification failed. Please ensure your BVN, date of birth, name, and phone number match exactly.';

        // Notify user of KYC rejection
        $user->notify(new \App\Notifications\KycStatusUpdatedNotification(
            'rejected',
            0,
            $failureMessage
        ));

        return response()->json([
            'status'  => 'error',
            'code'    => 'KYC_VERIFICATION_FAILED',
            'message' => $failureMessage,
        ], 422);
    }

    /**
     * POST /api/userManager/kyc/upgrade
     *
     * Upgrade KYC from Tier 1 → 2 (ID + utility bill)
     * or Tier 2 → 3 (CAC documents + business details).
     * Submission is queued for manual admin review.
     */
    public function upgrade(Request $request): JsonResponse
    {
        $user = $request->user();
        $kyc  = $user->kycRecord;

        if (!$kyc || $kyc->status !== 'verified') {
            return response()->json([
                'status'  => 'error',
                'code'    => 'KYC_NOT_VERIFIED',
                'message' => 'You must complete Tier 1 KYC (BVN verification) before upgrading.',
            ], 422);
        }

        $currentLevel = $kyc->level ?? 1;

        // ── Tier 1 → 2 ──────────────────────────────────────────────
        if ($currentLevel === 1) {
            $request->validate([
                'id_card_url'      => ['required', 'url', 'max:500'],
                'utility_bill_url' => ['required', 'url', 'max:500'],
            ]);

            $kyc->update([
                'status'           => 'pending_review',
                'id_card_url'      => $request->id_card_url,
                'utility_bill_url' => $request->utility_bill_url,
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Your Tier 2 upgrade documents have been submitted and are under review. You will be notified once approved.',
                'data'    => ['kyc_status' => 'pending_review', 'level' => 1],
            ]);
        }

        // ── Tier 2 → 3 ──────────────────────────────────────────────
        if ($currentLevel === 2) {
            $request->validate([
                'business_name'    => ['required', 'string', 'max:255'],
                'cac_number'       => ['required', 'string', 'max:50'],
                'cac_document_url' => ['required', 'url', 'max:500'],
            ]);

            $kyc->update([
                'status'           => 'pending_review',
                'business_name'    => $request->business_name,
                'cac_number'       => $request->cac_number,
                'cac_document_url' => $request->cac_document_url,
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Your Tier 3 corporate upgrade documents have been submitted and are under review. You will be notified once approved.',
                'data'    => ['kyc_status' => 'pending_review', 'level' => 2],
            ]);
        }

        return response()->json([
            'status'  => 'error',
            'code'    => 'KYC_MAX_TIER',
            'message' => 'Your account is already at the highest KYC tier.',
        ], 422);
    }
}
