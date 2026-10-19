<?php

declare(strict_types=1);

namespace App\Services\Wallet;

use App\Models\OrderItem;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EscrowService
{
    public function __construct(
        private WalletBalanceService $walletBalanceService
    ) {}

    /**
     * Get or create the central System Escrow Vault Wallet.
     */
    public function getOrCreateSystemEscrowWallet(string $currency = 'NGN'): Wallet
    {
        /** @var Wallet|null $wallet */
        $wallet = Wallet::whereNull('user_id')
            ->where('currency', $currency)
            ->first();

        if (!$wallet) {
            $wallet = Wallet::create([
                'id' => (string) Str::uuid(),
                'user_id' => null,
                'currency' => $currency,
                'balance' => '0.00000000',
                'frozen_balance' => '0.00000000',
                'status' => 'active',
            ]);
        }

        return $wallet;
    }

    /**
     * Hold funds in escrow during checkout.
     */
    public function holdInEscrow(Wallet $buyerWallet, string $amount, string $orderId): Transaction
    {
        return DB::transaction(function () use ($buyerWallet, $amount, $orderId) {
            $escrowWallet = $this->getOrCreateSystemEscrowWallet($buyerWallet->currency);

            // Lock wallets
            $lockedBuyerWallet = $this->walletBalanceService->lockWallet($buyerWallet->id);
            $lockedEscrowWallet = $this->walletBalanceService->lockWallet($escrowWallet->id);

            $transaction = Transaction::create([
                'id' => (string) Str::uuid(),
                'reference' => 'AGP_ESC_HOLD_' . Str::upper(Str::random(10)),
                'idempotency_key' => (string) Str::uuid(),
                'type' => 'purchase_escrow_hold',
                'status' => 'success',
                'initiated_by' => $lockedBuyerWallet->user_id,
                'from_wallet_id' => $lockedBuyerWallet->id,
                'to_wallet_id' => $lockedEscrowWallet->id,
                'currency' => $lockedBuyerWallet->currency,
                'amount' => $amount,
                'meta' => ['order_id' => $orderId],
                'completed_at' => now(),
            ]);

            // Move funds from buyer to escrow
            $this->walletBalanceService->debit($lockedBuyerWallet, $amount, $transaction->id);
            $this->walletBalanceService->credit($lockedEscrowWallet, $amount, $transaction->id);

            return $transaction;
        });
    }

    /**
     * Release escrow funds to vendor upon item delivery confirmation.
     */
    public function releaseEscrowToVendor(OrderItem $orderItem): Transaction
    {
        return DB::transaction(function () use ($orderItem) {
            $amount = (string) $orderItem->total_price;

            $escrowWallet = $this->getOrCreateSystemEscrowWallet('NGN');
            $lockedEscrowWallet = $this->walletBalanceService->lockWallet($escrowWallet->id);

            // Get or create Vendor Wallet
            $vendorWallet = Wallet::firstOrCreate(
                ['user_id' => $orderItem->vendor_id, 'currency' => 'NGN'],
                ['id' => (string) Str::uuid(), 'balance' => '0', 'status' => 'active']
            );
            $lockedVendorWallet = $this->walletBalanceService->lockWallet($vendorWallet->id);

            $transaction = Transaction::create([
                'id' => (string) Str::uuid(),
                'reference' => 'AGP_ESC_REL_' . Str::upper(Str::random(10)),
                'idempotency_key' => (string) Str::uuid(),
                'type' => 'vendor_payout_escrow_release',
                'status' => 'success',
                'initiated_by' => $orderItem->vendor_id,
                'from_wallet_id' => $lockedEscrowWallet->id,
                'to_wallet_id' => $lockedVendorWallet->id,
                'currency' => 'NGN',
                'amount' => $amount,
                'meta' => [
                    'order_id' => $orderItem->order_id,
                    'order_item_id' => $orderItem->id,
                    'vendor_id' => $orderItem->vendor_id,
                ],
                'completed_at' => now(),
            ]);

            $this->walletBalanceService->debit($lockedEscrowWallet, $amount, $transaction->id);
            $this->walletBalanceService->credit($lockedVendorWallet, $amount, $transaction->id);

            // Notify Vendor of payout
            $vendorUser = \App\Models\User::find($orderItem->vendor_id);
            if ($vendorUser) {
                $vendorUser->notify(new \App\Notifications\EscrowReleasedNotification($orderItem, $amount));
            }

            return $transaction;
        });
    }

    /**
     * Refund escrow funds back to buyer if order item is rejected or cancelled.
     */
    public function refundEscrowToBuyer(OrderItem $orderItem): Transaction
    {
        return DB::transaction(function () use ($orderItem) {
            $amount = (string) $orderItem->total_price;
            $buyerId = $orderItem->order->user_id;

            $escrowWallet = $this->getOrCreateSystemEscrowWallet('NGN');
            $lockedEscrowWallet = $this->walletBalanceService->lockWallet($escrowWallet->id);

            $buyerWallet = Wallet::where('user_id', $buyerId)->where('currency', 'NGN')->firstOrFail();
            $lockedBuyerWallet = $this->walletBalanceService->lockWallet($buyerWallet->id);

            $transaction = Transaction::create([
                'id' => (string) Str::uuid(),
                'reference' => 'AGP_ESC_REF_' . Str::upper(Str::random(10)),
                'idempotency_key' => (string) Str::uuid(),
                'type' => 'escrow_refund',
                'status' => 'success',
                'initiated_by' => $buyerId,
                'from_wallet_id' => $lockedEscrowWallet->id,
                'to_wallet_id' => $lockedBuyerWallet->id,
                'currency' => 'NGN',
                'amount' => $amount,
                'meta' => [
                    'order_id' => $orderItem->order_id,
                    'order_item_id' => $orderItem->id,
                    'reason' => 'order_rejected',
                ],
                'completed_at' => now(),
            ]);

            $this->walletBalanceService->debit($lockedEscrowWallet, $amount, $transaction->id);
            $this->walletBalanceService->credit($lockedBuyerWallet, $amount, $transaction->id);

            return $transaction;
        });
    }
}
