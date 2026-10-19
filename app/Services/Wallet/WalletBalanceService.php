<?php

namespace App\Services\Wallet;

use App\Exceptions\Domain\InsufficientBalanceException;
use App\Exceptions\Domain\WalletFrozenException;
use App\Models\LedgerEntry;
use App\Models\Wallet;
use Illuminate\Support\Str;

class WalletBalanceService
{
    /**
     * Lock wallet row for update (prevents double spend).
     */
    public function lockWallet(string $walletId): Wallet
    {
        /** @var Wallet $wallet */
        $wallet = Wallet::where('id', $walletId)->lockForUpdate()->firstOrFail();

        if ($wallet->status !== 'active') {
            throw new WalletFrozenException();
        }

        return $wallet;
    }

    /**
     * Lock user's wallet by currency.
     */
    public function lockUserWallet(string $userId, string $currency): Wallet
    {
        /** @var Wallet $wallet */
        $wallet = Wallet::where('user_id', $userId)
            ->where('currency', $currency)
            ->lockForUpdate()
            ->firstOrFail();

        if ($wallet->status !== 'active') {
            throw new WalletFrozenException();
        }

        return $wallet;
    }

    /**
     * Available = balance - frozen_balance.
     */
    public function available(Wallet $wallet): string
    {
        return bcsub((string) $wallet->balance, (string) $wallet->frozen_balance, 8);
    }

    /**
     * Debit spendable balance (does NOT touch frozen_balance).
     * Creates a debit ledger entry.
     */
    public function debit(Wallet $wallet, string $amount, string $transactionId): void
    {
        if (bccomp($this->available($wallet), (string) $amount, 8) < 0) {
            throw new InsufficientBalanceException();
        }

        $before = (string) $wallet->balance;
        $after  = bcsub($before, (string) $amount, 8);

        $wallet->update(['balance' => $after]);

        LedgerEntry::create([
            'id' => (string) Str::uuid(),
            'wallet_id' => $wallet->id,
            'transaction_id' => $transactionId,
            'entry_type' => 'debit',
            'amount' => $amount,
            'balance_before' => $before,
            'balance_after' => $after,
        ]);
    }

    /**
     * Credit balance.
     * Creates a credit ledger entry.
     */
    public function credit(Wallet $wallet, string $amount, string $transactionId): void
    {
        $before = (string) $wallet->balance;
        $after  = bcadd($before, (string) $amount, 8);

        $wallet->update(['balance' => $after]);

        LedgerEntry::create([
            'id' => (string) Str::uuid(),
            'wallet_id' => $wallet->id,
            'transaction_id' => $transactionId,
            'entry_type' => 'credit',
            'amount' => $amount,
            'balance_before' => $before,
            'balance_after' => $after,
        ]);
    }

    /**
     * Freeze funds (move from available into frozen_balance).
     * Good for pending withdrawals.
     */
    public function freeze(Wallet $wallet, string $amount): void
    {
        if (bccomp($this->available($wallet), (string) $amount, 8) < 0) {
            throw new InsufficientBalanceException();
        }

        $wallet->update([
            'frozen_balance' => bcadd((string) $wallet->frozen_balance, (string) $amount, 8),
        ]);
    }

    /**
     * Unfreeze funds (reduce frozen_balance).
     */
    public function unfreeze(Wallet $wallet, string $amount): void
    {
        if (bccomp((string) $wallet->frozen_balance, (string) $amount, 8) < 0) {
            throw new InsufficientBalanceException('Insufficient frozen balance.');
        }

        $wallet->update([
            'frozen_balance' => bcsub((string) $wallet->frozen_balance, (string) $amount, 8),
        ]);
    }

    /**
     * Capture frozen funds: reduces frozen_balance AND reduces balance.
     * Creates a debit ledger entry.
     */
    public function captureFrozenAsDebit(Wallet $wallet, string $amount, string $transactionId): void
    {
        if (bccomp((string) $wallet->frozen_balance, (string) $amount, 8) < 0) {
            throw new InsufficientBalanceException('Insufficient frozen balance.');
        }

        // reduce frozen first
        $wallet->update([
            'frozen_balance' => bcsub((string) $wallet->frozen_balance, (string) $amount, 8),
        ]);

        // then debit spendable balance + ledger
        $this->debit($wallet, $amount, $transactionId);
    }
}
