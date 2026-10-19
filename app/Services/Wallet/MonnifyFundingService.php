<?php
namespace App\Services\Wallet;

use App\Models\WebhookLog;
use App\Models\VirtualAccount;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\LedgerEntry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MonnifyFundingService
{
    public function __construct(private WalletBalanceService $walletBalanceService) {}

     public function processReservedAccountCredit(WebhookLog $webhook): void
    {
        /**
         * ✅ Funding flow (Reserved Account → NGN wallet)
         * - Only process SUCCESSFUL_TRANSACTION + PAID
         * - Identify user via accountReference (fallback product.reference)
         * - Idempotency: paymentReference (fallback transactionReference)
         * - Atomic: transaction + ledger + wallet balance update
         */

        if ($webhook->processed) return;

        $payload = $webhook->payload ?? [];

        // 1) Only process successful transactions
        if (($payload['eventType'] ?? null) !== 'SUCCESSFUL_TRANSACTION') {
            return;
        }

        $eventData = $payload['eventData'] ?? [];

        // 2) Must be PAID
        if (($eventData['paymentStatus'] ?? null) !== 'PAID') {
            return;
        }

        // 3) Extract provider reference for idempotency
        $providerRef =
            $eventData['paymentReference'] ?? null
            ?? $eventData['transactionReference'] ?? null;

        // 4) Extract amount/currency
        $amount = (string) ($eventData['amountPaid'] ?? 0);
        $currency = $eventData['currency'] ?? 'NGN';

        if (!$providerRef || bccomp($amount, '0', 8) <= 0) return;
        if ($currency !== 'NGN') return;

        // 5) Link payment → user via account reference (reserved accounts)
        $accountReference =
            $eventData['accountReference'] ?? null
            ?? ($eventData['product']['reference'] ?? null);

        if (!$accountReference) return;

        DB::transaction(function () use ($accountReference, $providerRef, $amount, $webhook) {

            // Lock VA row so we don't race
            $va = VirtualAccount::where('account_reference', $accountReference)
                ->where('provider', 'monnify')
                ->where('currency', 'NGN')
                ->lockForUpdate()
                ->first();

            if (!$va) return;

            // Lock wallet row
            $wallet = Wallet::where('id', $va->wallet_id)->lockForUpdate()->first();
            if (!$wallet || $wallet->status !== 'active') return;

            // Idempotency: use provider reference as unique business key
            $ref = 'MONNIFY_' . $providerRef;

            $existing = Transaction::where('reference', $ref)->lockForUpdate()->first();
            if ($existing) {
                $webhook->update(['processed' => true, 'processed_at' => now()]);
                return;
            }

            // Create funding transaction
            $trx = Transaction::create([
                'id' => (string) Str::uuid(),
                'reference' => $ref,
                'idempotency_key' => $ref, // optional reuse; still unique
                'type' => 'funding',
                'status' => 'success',
                'initiated_by' => $va->user_id,
                'from_wallet_id' => null,          // external funding
                'to_wallet_id' => $wallet->id,
                'currency' => 'NGN',
                'amount' => $amount,
                'meta' => [
                    'provider' => 'monnify',
                    'account_reference' => $accountReference,
                    'provider_reference' => $providerRef,
                    'webhook_log_id' => $webhook->id,
                ],
                'completed_at' => now(),
            ]);

            // Credit wallet balance + ledger
            $this->walletBalanceService->credit($wallet, $amount, $trx->id);

            $webhook->update(['processed' => true, 'processed_at' => now()]);

            // Dispatch Email Notification to User
            if ($wallet->user) {
                $wallet->user->notify(new \App\Notifications\DepositReceivedNotification($amount, 'NGN', $ref));
            }
        });
    }
}
