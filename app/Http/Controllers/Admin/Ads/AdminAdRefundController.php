<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Ads;

use App\Enums\Ads\AdvertisementStatus;
use App\Enums\Ads\PaymentStatus;
use App\Enums\Ads\RefundStatus;
use App\Enums\Ads\RefundType;
use App\Enums\Ads\WalletTransactionType;
use App\Models\Ads\AdvertisementPayment;
use App\Models\Ads\AdvertisementRefund;
use App\Notifications\Ads\RefundProcessedNotification;
use App\Services\Ads\AdWalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AdminAdRefundController
{
    public function store(Request $request, AdvertisementPayment $payment, AdWalletService $walletService): JsonResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:1', 'lte:' . $payment->amount],
            'type' => ['required', Rule::enum(RefundType::class)],
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $refund = DB::transaction(function () use ($data, $payment, $request, $walletService) {
            $refund = AdvertisementRefund::create([
                'campaign_id' => $payment->campaign_id,
                'advertiser_id' => $payment->advertiser_id,
                'payment_id' => $payment->id,
                'amount' => $data['amount'],
                'type' => $data['type'],
                'reason' => $data['reason'],
                'admin_id' => $request->user()->id,
                'status' => RefundStatus::Processed,
            ]);

            $wallet = $walletService->getOrCreateWallet($payment->advertiser);

            $walletService->credit(
                $wallet,
                (float) $data['amount'],
                WalletTransactionType::Refund,
                $payment->campaign_id,
                "Refund for payment {$payment->reference}",
                $request->user()->id,
                $payment->id,
            );

            $isFull = bccomp((string) $data['amount'], (string) $payment->amount, 2) === 0;

            $payment->update([
                'status' => $isFull ? PaymentStatus::Refunded : PaymentStatus::PartiallyRefunded,
            ]);

            $payment->campaign->update(['status' => AdvertisementStatus::Refunded]);
            $payment->advertisement->update(['status' => AdvertisementStatus::Refunded]);

            return $refund;
        });

        $payment->advertiser->user->notify(new RefundProcessedNotification($refund));

        return response()->json([
            'status' => 'success',
            'message' => 'Refund processed.',
            'data' => $refund,
        ], 201);
    }
}