<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Wallet\TransferRequest;
use App\Services\Wallet\ReservedAccountService;
use App\Services\Wallet\TransferService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WalletController
{
    public function balances(Request $request): JsonResponse
    {
        $wallets = $request->user()
            ->wallets()
            ->orderBy('currency')
            ->get(['currency', 'balance', 'frozen_balance', 'status']);

        return response()->json([
            'status' => 'success',
            'data' => $wallets,
        ]);
    }

    public function transactions(Request $request): JsonResponse
    {
        $transactions = $request->user()
            ->transactions()
            ->latest()
            ->paginate($request->input('per_page', 20));

        return response()->json([
            'status' => 'success',
            'data' => $transactions,
        ]);
    }

    public function transferInternal(TransferRequest $request, TransferService $service): JsonResponse
    {
        $data = $request->validated();

        $result = $service->transfer(
            user: $request->user(),
            to: $data['to'],
            currency: $data['currency'],
            amount: (string) $data['amount'],
            idempotencyKey: $data['idempotency_key'],
            purpose: $data['purpose'] ?? null,
            requestId: $request->attributes->get('request_id'),
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Transfer successful',
            'data' => $result,
        ]);
    }

    public function getOrCreateNgnVirtualAccount(Request $request, ReservedAccountService $service): JsonResponse
    {
        $va = $service->getOrCreateForUser($request->user());

        return response()->json([
            'status' => 'success',
            'message' => 'Virtual account ready',
            'data' => [
                'provider' => $va->provider,
                'currency' => $va->currency,
                'account_reference' => $va->account_reference,
                'account_name' => $va->account_name,
                'accounts' => $va->accounts,
                'status' => $va->status,
            ],
        ]);
    }
}
