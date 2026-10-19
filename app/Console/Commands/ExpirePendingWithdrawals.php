<?php

namespace App\Console\Commands;

use App\Models\WithdrawalRequest;
use App\Services\Wallet\WalletBalanceService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ExpirePendingWithdrawals extends Command
{
    protected $signature = 'withdrawals:expire-pending';
    protected $description = 'Expire pending PIN withdrawals older than 5 minutes and release frozen funds';

    public function handle(WalletBalanceService $balances): int
    {
        WithdrawalRequest::where('status', 'pending_pin')
            ->where('created_at', '<', now()->subMinutes(5))
            ->orderBy('created_at')
            ->chunkById(50, function ($rows) use ($balances) {
                foreach ($rows as $wd) {
                    DB::transaction(function () use ($wd, $balances) {
                        $wd = WithdrawalRequest::where('id', $wd->id)->lockForUpdate()->first();

                        if (!$wd || $wd->status !== 'pending_pin') {
                            return;
                        }

                        $wallet = $balances->lockWallet($wd->wallet_id);

                        // release frozen amount
                        $balances->unfreeze($wallet, (string) $wd->net_amount);

                        $wd->update([
                            'status' => 'expired',
                            'completed_at' => now(),
                        ]);
                    });
                }
            });

        $this->info('Expired stale pending withdrawals.');
        return self::SUCCESS;
    }
}
