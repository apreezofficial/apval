<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\LiquidityAccount;
use App\Models\Transaction;
use App\Models\WithdrawalRequest;
use App\Services\Payouts\PayoutProviderFactory;
use App\Services\Wallet\WalletBalanceService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProcessPayoutJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [10, 30, 60];

    public function __construct(
        public WithdrawalRequest $wd,
        public string $providerMode
    ) {}

    public function handle(PayoutProviderFactory $providers, WalletBalanceService $balances): void
    {
        $provider = $providers->for($this->providerMode);
        $result = $provider->payout($this->wd);

        DB::transaction(function () use ($result, $balances) {
            $wd = WithdrawalRequest::where('id', $this->wd->id)->lockForUpdate()->firstOrFail();

            if (!in_array($wd->status, ['processing', 'queued'], true)) {
                return; // Already handled or invalid state
            }

            $trx = Transaction::where('type', 'withdrawal')
                ->where('meta->withdrawal_reference', $wd->reference)
                ->lockForUpdate()
                ->latest('created_at')
                ->first();

            $liq = LiquidityAccount::where('currency', $wd->currency)->lockForUpdate()->first();

            if ($result['ok']) {
                if ($liq) {
                    $liq->update([
                        'reserved_balance' => bcsub((string) $liq->reserved_balance, (string) $wd->net_amount, 8),
                    ]);
                }

                $wd->update([
                    'status' => 'success',
                    'provider_reference' => $result['provider_reference'],
                    'provider_response' => $result['raw'],
                    'completed_at' => now(),
                ]);

                if ($trx) {
                    $trx->update([
                        'status' => 'success',
                        'provider_reference' => $result['provider_reference'],
                        'completed_at' => now(),
                    ]);
                }

                // Notify user of withdrawal completion
                $user = \App\Models\User::find($wd->user_id);
                if ($user) {
                    $user->notify(new \App\Notifications\WithdrawalCompletedNotification(
                        (string) $wd->net_amount,
                        $wd->currency,
                        $wd->bank_name ?? null,
                        $wd->account_number ?? null
                    ));

                    try {
                        if ($user->email) {
                            \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\TransactionAlertMail(
                                type: 'debit',
                                amount: (string) $wd->net_amount,
                                currency: $wd->currency,
                                newBalance: $wd->wallet?->fresh()?->balance,
                                reference: $wd->reference,
                                description: 'Bank Withdrawal to ' . ($wd->bank_name ?? 'Bank') . ' (' . ($wd->account_number ?? '') . ')',
                                counterparty: ($wd->account_name ?? 'Self') . ' - ' . ($wd->bank_name ?? ''),
                            ));
                        }
                    } catch (\Throwable $e) {
                        \Illuminate\Support\Facades\Log::error('Failed to send withdrawal debit alert: ' . $e->getMessage());
                    }
                }

                return;
            }

            // Failure handling - Reversal
            $wallet = $balances->lockWallet($wd->wallet_id);

            $rev = Transaction::create([
                'id' => (string) Str::uuid(),
                'reference' => 'AGP_REV_' . Str::lower(Str::random(24)),
                'idempotency_key' => null,
                'type' => 'reversal',
                'status' => 'success',
                'initiated_by' => $wd->user_id,
                'from_wallet_id' => null,
                'to_wallet_id' => $wallet->id,
                'currency' => $wd->currency,
                'amount' => $wd->net_amount,
                'provider' => $wd->provider,
                'provider_reference' => $wd->provider_reference,
                'meta' => [
                    'reverses_withdrawal_reference' => $wd->reference,
                    'provider_failure' => $result['raw'] ?? null,
                ],
                'completed_at' => now(),
            ]);

            $balances->credit($wallet, (string) $wd->net_amount, $rev->id);

            if ($liq) {
                $liq->update([
                    'available_balance' => bcadd((string) $liq->available_balance, (string) $wd->net_amount, 8),
                    'reserved_balance' => bcsub((string) $liq->reserved_balance, (string) $wd->net_amount, 8),
                ]);
            }

            $wd->update([
                'status' => 'reversed',
                'provider_response' => $result['raw'],
                'completed_at' => now(),
            ]);

            if ($trx) {
                $trx->update([
                    'status' => 'failed',
                    'completed_at' => now(),
                ]);
            }
        });
    }
}
