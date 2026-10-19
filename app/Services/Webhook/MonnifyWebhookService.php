<?php

namespace App\Services\Webhook;

use App\Exceptions\Domain\MonnifyFailure;
use App\Models\LedgerEntry;
use App\Models\Transaction;
use App\Models\VirtualAccount;
use App\Models\Wallet;
use App\Models\WebhookLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MonnifyWebhookService
{
    /**
     * Signature validation based on Monnify/TeamApt doc:
     * computedHash = sha512(clientSecret + rawBody)
     * Header: monnify-signature
     */
    public function isValidSignature(string $rawBody, ?string $signatureHeader): bool
    {
        if (!$signatureHeader) return false;

        $secret = config('services.monnify.secret_key'); // your normal Monnify secret key
        if (!$secret) return false;

        // Monnify doc sample uses HMAC SHA512
        $expected = hash_hmac('sha512', $rawBody, $secret);

        return hash_equals($expected, trim($signatureHeader));
    }

    /**
     * Process webhook log (idempotent).
     * Goal: for successful reserved account funding, credit user's NGN wallet and record Transaction + LedgerEntry.
     *
     * IMPORTANT:
     * - This method should be called only after WebhookLog was inserted (log-first strategy).
     * - Must be idempotent because Monnify will retry webhooks.
     */
    public function process(WebhookLog $log): void
    {
        if ($log->processed) {
            return;
        }

        $payload   = $log->payload ?? [];
        $eventType = $payload['eventType'] ?? null;
        $eventData = $payload['eventData'] ?? [];

        /**
         * 0) Only process "paid/success" events.
         * Monnify eventType varies by product, so we mainly rely on paymentStatus.
         */
        $paymentStatus = strtoupper((string) data_get($eventData, 'paymentStatus', ''));
        if (!in_array($paymentStatus, ['PAID', 'SUCCESS', 'SUCCESSFUL'], true)) {
            $log->update([
                'processed'    => true,
                'processed_at' => now(),
            ]);
            return;
        }

        /**
         * 1) Extract provider references (we use this for idempotency).
         * - transactionReference is the strongest unique ref
         * - paymentReference is also useful fallback
         */
        $paymentReference     = data_get($eventData, 'paymentReference');
        $transactionReference = data_get($eventData, 'transactionReference');

        // Provider ref used for idempotency + audit trail
        $providerRef = $transactionReference ?: $paymentReference;

        if (!$providerRef) {
            // No usable reference -> we cannot safely process idempotently
            $log->update([
                'processed'    => true,
                'processed_at' => now(),
            ]);
            return;
        }

        /**
         * 2) Extract amount and currency
         * Monnify payload may use amountPaid/totalPayable/amount depending on product type.
         */
        $amount = (string) (
            data_get($eventData, 'amountPaid')
            ?? data_get($eventData, 'amount')
            ?? data_get($eventData, 'totalPayable')
            ?? '0'
        );
        $currency = data_get($eventData, 'currency') ?? 'NGN';

        // Defensive: ignore invalid amounts
        if (!is_numeric($amount) || (float) $amount <= 0) {
            $log->update([
                'processed'    => true,
                'processed_at' => now(),
            ]);
            return;
        }

        /**
         * 3) Find VirtualAccount mapping
         * Best key: accountReference (your stored VirtualAccount.account_reference)
         * Monnify may provide it as:
         * - eventData.product.reference
         * - eventData.accountReference
         */
        $accountReference =
            data_get($eventData, 'product.reference')
            ?? data_get($eventData, 'accountReference')
            ?? null;

        DB::transaction(function () use (
            $log,
            $eventType,
            $eventData,
            $providerRef,
            $paymentReference,
            $transactionReference,
            $amount,
            $currency,
            $accountReference
        ) {

            /**
             * 4) Idempotency: if we've already created a funding transaction for this providerRef, stop.
             * This prevents double-credit on webhook retries.
             */
            $existing = Transaction::where('provider', 'monnify')
                ->where('provider_reference', $providerRef)
                ->lockForUpdate() // prevents race if two webhooks hit at same time
                ->first();

            if ($existing) {
                $log->update([
                    'processed'    => true,
                    'processed_at' => now(),
                ]);
                return;
            }

            /**
             * 5) Locate VirtualAccount row.
             */
            $va = null;

            if ($accountReference) {
                $va = VirtualAccount::where('provider', 'monnify')
                    ->where('account_reference', $accountReference)
                    ->lockForUpdate()
                    ->first();
            }

            /**
             * Fallback: match by destination account number if Monnify includes it.
             * NOTE: whereJsonContains works best if accounts JSON is stored exactly.
             * If it fails, we’ll implement a safer lookup later (store account_numbers in a separate table).
             */
            if (!$va) {
                $destAccNo = data_get($eventData, 'destinationAccountInformation.accountNumber');
                if ($destAccNo) {
                    $va = VirtualAccount::where('provider', 'monnify')
                        ->whereJsonContains('accounts', [['accountNumber' => $destAccNo]])
                        ->lockForUpdate()
                        ->first();
                }
            }

            /**
             * If we cannot map payment to a VirtualAccount, we can't credit any wallet.
             * Mark processed to stop retries, but leave it for reconciliation review.
             */
            if (!$va) {
                $log->update([
                    'processed'    => true,
                    'processed_at' => now(),
                ]);
                return;
            }

            /**
             * 6) Lock the wallet row and compute new balance.
             */
            $wallet = Wallet::where('id', $va->wallet_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($wallet->status !== 'active') {
                throw new MonnifyFailure('Wallet frozen. Cannot credit.', 'WALLET_FROZEN', 403);
            }

            $before = (string) $wallet->balance;
            $after  = bcadd($before, (string) $amount, 8);

            /**
             * 7) Create funding transaction (SUCCESS).
             * - idempotency_key is NULL for webhook-driven transactions
             * - provider/provider_reference used for uniqueness + audit
             */
            $trx = Transaction::create([
                'id'              => (string) Str::uuid(),
                'reference'        => 'AGP_FUND_' . Str::lower(Str::random(24)),
                'idempotency_key'  => null,             // webhook (not client initiated)
                'type'             => 'funding',
                'status'           => 'success',
                'initiated_by'     => $va->user_id,
                'from_wallet_id'   => null,
                'to_wallet_id'     => $wallet->id,
                'currency'         => $currency,
                'amount'           => $amount,
                'provider'         => 'monnify',
                'provider_reference' => $providerRef,
                'meta' => [
                    'event_type' => $eventType,
                    'paymentReference' => $paymentReference,
                    'transactionReference' => $transactionReference,
                    'accountReference' => $accountReference,
                    'raw' => $eventData, // optional: keep only if acceptable size; else store in webhook_logs only
                ],
                'completed_at' => now(),
            ]);

            /**
             * 8) Update wallet balance (credit).
             */
            $wallet->update(['balance' => $after]);

            /**
             * 9) Ledger entry for audit trail.
             */
            LedgerEntry::create([
                'id'             => (string) Str::uuid(),
                'wallet_id'      => $wallet->id,
                'transaction_id' => $trx->id,
                'entry_type'     => 'credit',
                'amount'         => $amount,
                'balance_before' => $before,
                'balance_after'  => $after,
            ]);

            /**
             * 10) Mark webhook processed.
             * Optional: store providerRef in webhook_logs if column exists.
             */
            $log->update([
                'processed'    => true,
                'processed_at' => now(),
                // 'provider_reference' => $providerRef, // uncomment if column exists
            ]);

            /**
             * 11) Send Credit Alert Email Notification
             */
            try {
                $user = $va->user;
                if ($user && !empty($user->email)) {
                    \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\TransactionAlertMail(
                        type: 'credit',
                        amount: $amount,
                        currency: $currency,
                        newBalance: $after,
                        reference: $trx->reference,
                        description: 'Wallet Funding via Monnify Dedicated Account',
                        counterparty: data_get($eventData, 'payerName') ?: 'Bank Transfer',
                    ));
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Failed to send credit alert email: ' . $e->getMessage());
            }

            /**
             * 12) Check and process referral reward on first successful deposit
             */
            try {
                $user = $va->user;
                if ($user) {
                    app(\App\Services\Referral\ReferralService::class)->processQualifyingDeposit($user);
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Referral hook error: ' . $e->getMessage());
            }
        });
    }
}
