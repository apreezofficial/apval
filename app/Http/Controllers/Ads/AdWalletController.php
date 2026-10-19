<?php

declare(strict_types=1);

namespace App\Http\Controllers\Ads;

use App\Exceptions\Ads\InsufficientWalletBalanceException;
use App\Http\Requests\Ads\FundAdWalletRequest;
use App\Models\Ads\Advertiser;
use App\Services\Ads\AdWalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdWalletController
{
    public function show(Request $request, AdWalletService $walletService): JsonResponse
    {
        /** @var Advertiser $advertiser */
        $advertiser = $request->attributes->get('advertiser');

        $wallet = $walletService->getOrCreateWallet($advertiser);

        return response()->json([
            'status' => 'success',
            'data' => $wallet,
        ]);
    }

    public function transactions(Request $request, AdWalletService $walletService): JsonResponse
    {
        /** @var Advertiser $advertiser */
        $advertiser = $request->attributes->get('advertiser');

        $wallet = $walletService->getOrCreateWallet($advertiser);

        $transactions = $wallet->transactions()->latest()->paginate($request->integer('per_page', 20));

        return response()->json([
            'status' => 'success',
            'data' => $transactions,
        ]);
    }

    public function fund(FundAdWalletRequest $request, AdWalletService $walletService): JsonResponse
    {
        /** @var Advertiser $advertiser */
        $advertiser = $request->attributes->get('advertiser');

        $data = $request->validated();

        try {
            $transaction = $walletService->fundFromMainWallet(
                $advertiser,
                (float) $data['amount'],
                $data['currency'] ?? null,
                $request->user()->id,
            );
        } catch (InsufficientWalletBalanceException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Advertising wallet funded.',
            'data' => $transaction,
        ]);
    }
}