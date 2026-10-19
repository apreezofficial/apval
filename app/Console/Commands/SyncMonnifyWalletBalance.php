<?php

namespace App\Console\Commands;

use App\Models\LiquidityAccount;
use App\Services\Kyc\MonnifyClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SyncMonnifyWalletBalance extends Command
{
   protected $signature = 'monnify:wallet-balance-sync';
    protected $description = 'Sync Monnify wallet balance into liquidity_accounts';

    public function handle(MonnifyClient $monnify): int
    {
        $walletAccountNumber = config('services.monnify.payout_source_account_number');

        if (!$walletAccountNumber) {
            $this->error('MONNIFY_PAYOUT_SOURCE_ACCOUNT_NUMBER is not configured.');
            return self::FAILURE;
        }

        $this->info("Fetching Monnify wallet balance for {$walletAccountNumber}...");

        $body = $monnify->walletBalance($walletAccountNumber);

        // Adjust these keys if Monnify returns a slightly different shape in your account
        $availableBalance = (string) (
            $body['availableBalance']
            ?? $body['balance']
            ?? $body['walletBalance']
            ?? '0'
        );

        DB::transaction(function () use ($availableBalance) {
            $liq = LiquidityAccount::where('currency', 'NGN')->lockForUpdate()->first();

            if (!$liq) {
                $liq = LiquidityAccount::create([
                    'id' => (string) Str::uuid(),
                    'currency' => 'NGN',
                    'available_balance' => $availableBalance,
                    'reserved_balance' => '0.00000000',
                ]);
            } else {
                // Keep reserved_balance untouched. Only sync actual available payout float.
                $liq->update([
                    'available_balance' => $availableBalance,
                ]);
            }
        });

        $this->info("Liquidity synced successfully. NGN available_balance = {$availableBalance}");

        return self::SUCCESS;
    }
}
