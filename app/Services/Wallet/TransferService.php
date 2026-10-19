<?php

namespace App\Services\Wallet;

use App\Exceptions\Domain\IdempotencyReplayException;
use App\Exceptions\Domain\InvalidRequestException;
use App\Exceptions\Domain\InsufficientBalanceException;
use App\Exceptions\Domain\NotFoundException;
use App\Exceptions\Domain\UserSuspendedException;
use App\Models\Transaction;
use App\Models\User;
use App\Services\Kyc\KycTierService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TransferService
{
    public function __construct(
        private WalletBalanceService $balances,
        private KycTierService $kycTier,
    ) {}

    public function transfer(
        User $user,
        string $to,
        string $currency,
        string $amount,
        string $idempotencyKey,
        ?string $purpose,
        ?string $requestId,
    ): array {

        if ($user->status !== 'active') {
            throw new UserSuspendedException('User not active.');
        }

        // Enforce KYC tier limit before processing
        $this->kycTier->assertCanTransact($user, (string)$amount, 'transfer');

        // Idempotency (fast path — outside transaction for speed)
        $existing = Transaction::where('idempotency_key', $idempotencyKey)->first();
        if ($existing) {
            if ($existing->status === 'success') {
                return [
                    'reference' => $existing->reference,
                    'amount'    => (string) $existing->amount,
                    'currency'  => $existing->currency,
                    'replayed'  => true,
                ];
            }
            if ($existing->status === 'pending') {
                return [
                    'reference' => $existing->reference,
                    'amount'    => (string) $existing->amount,
                    'currency'  => $existing->currency,
                    'status'    => 'pending',
                    'replayed'  => true,
                ];
            }
            throw new IdempotencyReplayException();
        }

        $receiver = User::query()
            ->where('email', $to)
            ->orWhere('phone', $to)
            ->first();

        if (!$receiver) throw new NotFoundException('Receiver not found.');
        if ($receiver->status !== 'active') throw new UserSuspendedException('Receiver not active.');
        if ($receiver->id === $user->id) throw new InvalidRequestException('Cannot transfer to self.');

        return DB::transaction(function () use ($user, $receiver, $currency, $amount, $idempotencyKey, $purpose, $requestId) {

            // Idempotency inside txn (race-safe)
            $existing = Transaction::where('idempotency_key', $idempotencyKey)->lockForUpdate()->first();
            if ($existing) {
                if ($existing->status === 'success') {
                    return [
                        'reference' => $existing->reference,
                        'amount' => (string) $existing->amount,
                        'currency' => $existing->currency,
                        'replayed' => true,
                    ];
                }
                throw new IdempotencyReplayException();
            }

            $senderWallet   = $this->balances->lockUserWallet($user->id, $currency);
            $receiverWallet = $this->balances->lockUserWallet($receiver->id, $currency);

            if (bccomp($this->balances->available($senderWallet), (string)$amount, 8) < 0) {
                throw new InsufficientBalanceException();
            }

            $trx = Transaction::create([
                'id' => (string) Str::uuid(),
                'reference' => 'AGP_TRX_' . Str::lower(Str::random(24)),
                'idempotency_key' => $idempotencyKey,
                'type' => 'transfer',
                'status' => 'pending',
                'initiated_by' => $user->id,
                'from_wallet_id' => $senderWallet->id,
                'to_wallet_id' => $receiverWallet->id,
                'currency' => $currency,
                'amount' => $amount,
                'meta' => [
                    'purpose' => $purpose,
                    'request_id' => $requestId,
                    'receiver_user_id' => $receiver->id,
                ],
            ]);

            // debit + credit via service (creates ledger entries)
            $this->balances->debit($senderWallet, (string)$amount, $trx->id);
            $this->balances->credit($receiverWallet, (string)$amount, $trx->id);

            $trx->update([
                'status' => 'success',
                'completed_at' => now(),
            ]);

            // Dispatch Email Notification to Receiver
            $senderName = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
            $receiverName = trim(($receiver->first_name ?? '') . ' ' . ($receiver->last_name ?? ''));

            try {
                if ($user->email) {
                    \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\TransactionAlertMail(
                        type: 'debit',
                        amount: (string) $amount,
                        currency: $currency,
                        newBalance: $senderWallet->fresh()->balance,
                        reference: $trx->reference,
                        description: $purpose ?: 'Transfer to ' . $receiverName,
                        counterparty: $receiverName,
                    ));
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Failed to send sender debit alert: ' . $e->getMessage());
            }

            try {
                if ($receiver->email) {
                    \Illuminate\Support\Facades\Mail::to($receiver->email)->send(new \App\Mail\TransactionAlertMail(
                        type: 'credit',
                        amount: (string) $amount,
                        currency: $currency,
                        newBalance: $receiverWallet->fresh()->balance,
                        reference: $trx->reference,
                        description: $purpose ?: 'Transfer from ' . $senderName,
                        counterparty: $senderName,
                    ));
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Failed to send receiver credit alert: ' . $e->getMessage());
            }

            return [
                'reference' => $trx->reference,
                'amount' => (string) $trx->amount,
                'currency' => $trx->currency,
            ];
        });
    }
}
