<?php

declare(strict_types=1);

namespace App\Services\Ads;

use App\Enums\Ads\TransactionDirection;
use App\Enums\Ads\WalletTransactionType;
use App\Exceptions\Ads\InsufficientWalletBalanceException;
use App\Models\Ads\Advertiser;
use App\Models\Ads\AdvertisingTransaction;
use App\Models\Ads\AdvertisingWallet;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;

class AdWalletService
{
    public function getOrCreateWallet(Advertiser $advertiser): AdvertisingWallet
    {
        return AdvertisingWallet::firstOrCreate(
            ['advertiser_id' => $advertiser->id],
            ['balance' => 0]
        );
    }

    public function credit(
        AdvertisingWallet $wallet,
        float $amount,
        WalletTransactionType $type,
        ?string $campaignId = null,
        ?string $description = null,
        ?string $createdBy = null,
        ?string $paymentId = null,
    ): AdvertisingTransaction {
        return DB::transaction(function () use ($wallet, $amount, $type, $campaignId, $description, $createdBy, $paymentId) {
            $locked = AdvertisingWallet::query()->whereKey($wallet->id)->lockForUpdate()->firstOrFail();
            $locked->balance = bcadd((string) $locked->balance, (string) $amount, 2);
            $locked->save();

            return AdvertisingTransaction::create([
                'wallet_id' => $locked->id,
                'type' => $type,
                'direction' => TransactionDirection::Credit,
                'amount' => $amount,
                'balance_after' => $locked->balance,
                'campaign_id' => $campaignId,
                'payment_id' => $paymentId,
                'description' => $description,
                'created_by' => $createdBy,
            ]);
        });
    }

    public function debit(
        AdvertisingWallet $wallet,
        float $amount,
        WalletTransactionType $type,
        ?string $campaignId = null,
        ?string $description = null,
        ?string $createdBy = null,
        ?string $paymentId = null,
    ): AdvertisingTransaction {
        return DB::transaction(function () use ($wallet, $amount, $type, $campaignId, $description, $createdBy, $paymentId) {
            $locked = AdvertisingWallet::query()->whereKey($wallet->id)->lockForUpdate()->firstOrFail();

            if (bccomp((string) $locked->balance, (string) $amount, 2) < 0) {
                throw new InsufficientWalletBalanceException('Insufficient advertising wallet balance.');
            }

            $locked->balance = bcsub((string) $locked->balance, (string) $amount, 2);
            $locked->save();

            return AdvertisingTransaction::create([
                'wallet_id' => $locked->id,
                'type' => $type,
                'direction' => TransactionDirection::Debit,
                'amount' => $amount,
                'balance_after' => $locked->balance,
                'campaign_id' => $campaignId,
                'payment_id' => $paymentId,
                'description' => $description,
                'created_by' => $createdBy,
            ]);
        });
    }

    /**
     * Fund the advertising wallet by debiting the advertiser's main AgreonPay wallet.
     *
     * NOTE: This performs a direct, self-contained debit against the `wallets` table.
     * If a canonical internal transfer mechanism (e.g. a ledger-aware TransferService)
     * already exists in the main wallet module, prefer routing through it instead so
     * that ledger entries / audit trails stay consistent across the whole platform.
     */
    public function fundFromMainWallet(
        Advertiser $advertiser,
        float $amount,
        ?string $currency = null,
        ?string $createdBy = null,
    ): AdvertisingTransaction {
        $currency = $currency ?? config('ads.defaults.wallet_currency', 'NGN');

        return DB::transaction(function () use ($advertiser, $amount, $currency, $createdBy) {
            $mainWallet = Wallet::query()
                ->where('user_id', $advertiser->user_id)
                ->where('currency', $currency)
                ->lockForUpdate()
                ->first();

            if (! $mainWallet) {
                throw new InsufficientWalletBalanceException("No {$currency} wallet found for this advertiser.");
            }

            if (bccomp((string) $mainWallet->balance, (string) $amount, 8) < 0) {
                throw new InsufficientWalletBalanceException('Insufficient main wallet balance.');
            }

            $mainWallet->balance = bcsub((string) $mainWallet->balance, (string) $amount, 8);
            $mainWallet->save();

            $advertisingWallet = $this->getOrCreateWallet($advertiser);

            return $this->credit(
                $advertisingWallet,
                $amount,
                WalletTransactionType::WalletFunding,
                description: 'Advertising wallet funded from main wallet',
                createdBy: $createdBy,
            );
        });
    }
}