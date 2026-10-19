<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\InitiateWithdrawalRequest;
use App\Services\Wallet\WithdrawalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WithdrawalController
{
    public function __construct(private WithdrawalService $withdrawals) {}

    public function initiate(InitiateWithdrawalRequest $request): JsonResponse
    {
        // Validation is handled by InitiateWithdrawalRequest — no duplicate validation needed
        $data = $request->validated();

        $wd = $this->withdrawals->initiate(
            $request->user(),
            $data['currency'],
            (string) $data['amount'],
            $data['idempotency_key'],
            [
                'bank_code' => $data['bank_code'],
                'account_number' => $data['account_number'],
                'account_name' => $data['account_name'] ?? null,
            ],
            $data['payout_mode'] ?? 'monnify',
            $request->attributes->get('request_id'),
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Withdrawal initiated. Pin Confirmation required Within 5 Minutes.',
            'data' => [
                'reference' => $wd->reference,
                'status' => $wd->status,
                'expires_at' => $wd->created_at->addMinutes(5)->toDateTimeString(),
            ],
        ]);
    }

    public function confirm2fa(Request $request, string $reference): JsonResponse
    {
        $data = $request->validate([
            'pin' => ['required', 'digits:6'],
        ]);

        $wd = $this->withdrawals->confirmPinAndProcess(
            $request->user(),
            $reference,
            $data['pin']
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Withdrawal processed.',
            'data' => [
                'reference' => $wd->reference,
                'status' => $wd->status,
                'provider_reference' => $wd->provider_reference,
            ],
        ]);
    }
}
