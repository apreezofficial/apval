<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Wallet\AgroPointsService;
use App\Services\Wallet\WalletBalanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AgroPointsController
{
    public function __construct(
        private AgroPointsService $agroPointsService,
        private WalletBalanceService $walletBalanceService
    ) {}

    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        $wallet = $user->wallets()->where('currency', 'NGN')->first();

        $points = $wallet ? (float) $wallet->agro_points : 0.0;
        $equivalentCash = $points * AgroPointsService::CONVERSION_RATE_NGN_PER_POINT;

        return response()->json([
            'status' => 'success',
            'data' => [
                'agro_points' => $points,
                'conversion_rate_ngn' => AgroPointsService::CONVERSION_RATE_NGN_PER_POINT,
                'equivalent_cash_ngn' => $equivalentCash,
            ],
        ]);
    }

    public function redeem(Request $request): JsonResponse
    {
        $request->validate([
            'points' => ['required', 'numeric', 'min:1'],
        ]);

        $result = $this->agroPointsService->redeemPoints(
            $request->user(),
            (float) $request->points
        );

        return response()->json([
            'status' => 'success',
            'message' => sprintf('Successfully redeemed %.2f Agro Points for N%s cash balance.', $result['points_redeemed'], $result['cash_credited']),
            'data' => $result,
        ]);
    }
}
