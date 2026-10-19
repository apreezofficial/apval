<?php

declare(strict_types=1);

namespace App\Http\Controllers\Ads;

use App\Enums\Ads\AdvertisementStatus;
use App\Enums\Ads\PaymentMethod;
use App\Enums\Ads\PaymentStatus;
use App\Enums\Ads\WalletTransactionType;
use App\Exceptions\Ads\InsufficientWalletBalanceException;
use App\Http\Requests\Ads\MakePaymentRequest;
use App\Models\Ads\Advertisement;
use App\Models\Ads\AdvertisementCampaign;
use App\Models\Ads\AdvertisementPayment;
use App\Notifications\Ads\PaymentSuccessfulNotification;
use App\Services\Ads\AdSettingService;
use App\Services\Ads\AdWalletService;
use App\Services\Ads\AdWorkflowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class AdPaymentController
{
    public function store(
        MakePaymentRequest $request,
        Advertisement $advertisement,
        AdWalletService $walletService,
        AdSettingService $settings,
        AdWorkflowService $workflowService,
    ): JsonResponse {
        Gate::authorize('update', $advertisement);

        $campaign = $advertisement->campaign;

        if (! $campaign || ! $campaign->total_budget) {
            return response()->json([
                'status' => 'error',
                'message' => 'Set campaign budget and duration before making payment.',
            ], 422);
        }

        if (! in_array($advertisement->status, [AdvertisementStatus::Draft, AdvertisementStatus::ChangesRequired], true)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Advertisement is not awaiting payment.',
            ], 422);
        }

        $method = PaymentMethod::from($request->validated()['payment_method']);
        $amount = (float) $campaign->total_budget;
        $feePercent = (float) $settings->get('default_gateway_fee_percent');
        $fee = $method === PaymentMethod::AdvertisingWallet ? 0.0 : round($amount * $feePercent / 100, 2);

        $payment = AdvertisementPayment::create([
            'advertiser_id' => $advertisement->advertiser_id,
            'campaign_id' => $campaign->id,
            'advertisement_id' => $advertisement->id,
            'amount' => $amount,
            'gateway_fee' => $fee,
            'net_revenue' => $amount - $fee,
            'payment_method' => $method,
            'status' => PaymentStatus::Pending,
        ]);

        if ($method === PaymentMethod::AdvertisingWallet) {
            return $this->payFromWallet($advertisement, $campaign, $payment, $walletService, $workflowService);
        }

        // TODO: integrate with the platform's gateway/bank-transfer flow.
        // Call PaymentController::confirm() once the gateway confirms success (e.g. via webhook).
        return response()->json([
            'status' => 'success',
            'message' => 'Payment initialized. Complete payment via the selected method.',
            'data' => $payment,
        ], 201);
    }

    public function confirm(Advertisement $advertisement, AdvertisementPayment $payment, AdWorkflowService $workflowService): JsonResponse
    {
        abort_if($payment->advertisement_id !== $advertisement->id, 404);

        $payment->update(['status' => PaymentStatus::Successful]);
        $advertisement->update(['status' => AdvertisementStatus::PaymentSuccessful]);
        $advertisement->campaign?->update(['status' => AdvertisementStatus::PaymentSuccessful]);

        $workflowService->submitForReview($advertisement->refresh());

        $advertisement->user->notify(new PaymentSuccessfulNotification($payment->fresh()));

        return response()->json([
            'status' => 'success',
            'message' => 'Payment confirmed. Advertisement submitted for review.',
            'data' => $payment->fresh(),
        ]);
    }

    protected function payFromWallet(
        Advertisement $advertisement,
        AdvertisementCampaign $campaign,
        AdvertisementPayment $payment,
        AdWalletService $walletService,
        AdWorkflowService $workflowService,
    ): JsonResponse {
        try {
            DB::transaction(function () use ($advertisement, $campaign, $payment, $walletService): void {
                $wallet = $walletService->getOrCreateWallet($advertisement->advertiser);

                $walletService->debit(
                    $wallet,
                    (float) $payment->amount,
                    WalletTransactionType::CampaignPayment,
                    $campaign->id,
                    "Payment for campaign {$campaign->reference}",
                    $advertisement->user_id,
                    $payment->id,
                );

                $payment->update(['status' => PaymentStatus::Successful]);
                $advertisement->update(['status' => AdvertisementStatus::PaymentSuccessful]);
                $campaign->update(['status' => AdvertisementStatus::PaymentSuccessful]);
            });
        } catch (InsufficientWalletBalanceException $e) {
            $payment->update(['status' => PaymentStatus::Failed]);

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }

        $workflowService->submitForReview($advertisement->refresh());

        $advertisement->user->notify(new PaymentSuccessfulNotification($payment->fresh()));

        return response()->json([
            'status' => 'success',
            'message' => 'Payment successful. Advertisement submitted for review.',
            'data' => $payment->fresh(),
        ]);
    }
}