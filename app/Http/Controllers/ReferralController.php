<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Referral\ReferralService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReferralController
{
    public function __construct(
        private ReferralService $referralService
    ) {}

    public function show(Request $request): JsonResponse
    {
        $summary = $this->referralService->getReferralSummary($request->user());

        return response()->json([
            'status' => 'success',
            'data' => $summary,
        ]);
    }
}
