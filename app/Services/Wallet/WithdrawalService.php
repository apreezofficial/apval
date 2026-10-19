<?php

namespace App\Services\Wallet;

use App\Exceptions\Domain\InsufficientBalanceException;
use App\Exceptions\Domain\InvalidRequestException;
use App\Exceptions\Domain\KycRequiredException;
use App\Exceptions\Domain\LiquidityInsufficientException;
use App\Exceptions\Domain\UserSuspendedException;
use App\Exceptions\Domain\WalletFrozenException;
use App\Models\LiquidityAccount;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WithdrawalRequest;
use App\Services\Banks\BankDirectory;
use App\Services\Kyc\KycTierService;
use App\Services\Payouts\PayoutProviderFactory;
use App\Services\Security\PinService;
use App\Services\Security\TwoFactorService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WithdrawalService
{
    public function __construct(
        //private TwoFactorService $twoFactor,
        private PinService $pins,
        private PayoutProviderFactory $providers,
        private WalletBalanceService $balances,
        private BankDirectory $bankDirectory,
        private KycTierService $kycTier,
    ) {}

    public function initiate(
        User $user,
        string $currency,
        string $amount,
        string $idempotencyKey,
        array $bank,
        string $payoutMode,
        ?string $requestId,
    ): WithdrawalRequest {
        if ($user->status !== 'active') {
            throw new UserSuspendedException();
        }

        $kyc = $user->kycRecord;
        if (!$kyc || !$kyc->bvn_verified || ($kyc->level ?? 0) < 1) {
            throw new KycRequiredException('KYC Level 1 required for withdrawals.');
        }

        // Enforce daily spend limit for this tier
        $this->kycTier->assertCanTransact($user, $amount, 'withdraw');

        // Validate destination bank code
        if (!$this->bankDirectory->isValidMonnifyBankCode($bank['bank_code'] ?? '')) {
            throw new InvalidRequestException('Invalid destination bank code.');
        }

        // Idempotency replay — scoped to this user so a guessed/shared key
        // cannot return another user's withdrawal record.
        $existing = WithdrawalRequest::where('idempotency_key', $idempotencyKey)
            ->where('user_id', $user->id)
            ->first();
        if ($existing) {
            return $existing;
        }

        return DB::transaction(function () use ($user, $currency, $amount, $idempotencyKey, $bank, $payoutMode, $requestId) {
            $wallet = Wallet::where('user_id', $user->id)
                ->where('currency', $currency)
                ->lockForUpdate()
                ->firstOrFail();

            if ($wallet->status !== 'active') {
                throw new WalletFrozenException();
            }

            // available = balance - frozen_balance
            $available = bcsub((string) $wallet->balance, (string) $wallet->frozen_balance, 8);

            if (bccomp($available, (string) $amount, 8) < 0) {
                throw new InsufficientBalanceException();
            }

            // Freeze funds now
            $wallet->update([
                'frozen_balance' => bcadd((string) $wallet->frozen_balance, (string) $amount, 8),
            ]);

            return WithdrawalRequest::create([
                'id' => (string) Str::uuid(),
                'reference' => 'AGP_WD_' . Str::lower(Str::random(24)),
                'idempotency_key' => $idempotencyKey,
                'user_id' => $user->id,
                'wallet_id' => $wallet->id,
                'currency' => $currency,
                'amount' => $amount,
                'fee' => '0.00000000',
                'net_amount' => $amount,
                'status' => 'pending_pin',
                'payout_mode' => $payoutMode,
                'bank_code' => $bank['bank_code'] ?? null,
                'account_number' => $bank['account_number'] ?? null,
                'account_name' => $bank['account_name'] ?? null,
                'meta' => [
                    'request_id' => $requestId,
                    'narration' => $bank['narration'] ?? null,
                ],
            ]);
        });
    }

    public function confirmPinAndProcess(User $user, string $reference, string $pin): WithdrawalRequest
{
    /** @var WithdrawalRequest $wd */
    $wd = WithdrawalRequest::where('reference', $reference)
        ->where('user_id', $user->id)
        ->firstOrFail();

    // Expire stale pending PIN withdrawals after 5 minutes
    if ($wd->status === 'pending_pin' && $wd->created_at->lt(now()->subMinutes(5))) {
        DB::transaction(function () use ($wd) {
            $wd = WithdrawalRequest::where('id', $wd->id)->lockForUpdate()->firstOrFail();

            if ($wd->status !== 'pending_pin') {
                return;
            }

            $wallet = $this->balances->lockWallet($wd->wallet_id);

            // release frozen funds
            $this->balances->unfreeze($wallet, (string) $wd->net_amount);

            $wd->update([
                'status' => 'expired',
                'completed_at' => now(),
            ]);
        });

        throw new InvalidRequestException('Withdrawal request expired. Please initiate again.');
    }

    $user->refresh();
    $this->pins->assertValidPin($user, $pin);

    if (in_array($wd->status, ['success', 'failed', 'reversed', 'expired'], true)) {
        return $wd;
    }

    [$wd, $providerMode] = DB::transaction(function () use ($user, $wd) {
        $wd->refresh();

        if ($wd->status !== 'pending_pin') {
            return [$wd, $wd->payout_mode];
        }

        $wallet = $this->balances->lockWallet($wd->wallet_id);

        if (bccomp((string) $wallet->frozen_balance, (string) $wd->net_amount, 8) < 0) {
            throw new InsufficientBalanceException('Insufficient frozen balance.');
        }

        $liq = LiquidityAccount::where('currency', $wd->currency)->lockForUpdate()->first();

        if (!$liq) {
            $liq = LiquidityAccount::create([
                'id' => (string) Str::uuid(),
                'currency' => $wd->currency,
                'available_balance' => 0,
                'reserved_balance' => 0,
            ]);
            $liq->refresh();
        }

        if (bccomp((string) $liq->available_balance, (string) $wd->net_amount, 8) < 0) {
            // IMPORTANT: don't throw here if you want queued + unfreeze to persist
            $wd->update(['status' => 'queued']);

            $this->balances->unfreeze($wallet, (string) $wd->net_amount);

            return [$wd->fresh(), $wd->payout_mode];
        }

        $liq->update([
            'available_balance' => bcsub((string) $liq->available_balance, (string) $wd->net_amount, 8),
            'reserved_balance' => bcadd((string) $liq->reserved_balance, (string) $wd->net_amount, 8),
        ]);

        $trx = Transaction::create([
            'id' => (string) Str::uuid(),
            'reference' => 'AGP_WDTRX_' . Str::lower(Str::random(24)),
            'idempotency_key' => null,
            'type' => 'withdrawal',
            'status' => 'pending',
            'initiated_by' => $user->id,
            'from_wallet_id' => $wallet->id,
            'to_wallet_id' => null,
            'currency' => $wd->currency,
            'amount' => $wd->net_amount,
            'provider' => $wd->payout_mode === 'monnify' ? 'monnify' : null,
            'meta' => [
                'withdrawal_reference' => $wd->reference,
            ],
        ]);

        $this->balances->captureFrozenAsDebit($wallet, (string) $wd->net_amount, $trx->id);

        $wd->update([
            'status' => 'processing',
            'provider' => $wd->payout_mode === 'monnify' ? 'monnify' : null,
        ]);

        return [$wd->fresh(), $wd->payout_mode];
    });

    // Only dispatch if the transaction block set status to 'processing'.
    // Any other status (queued, success, failed, expired, reversed) means
    // this request lost the race or the withdrawal was already handled.
    if ($wd->status !== 'processing') {
        return $wd;
    }

    \App\Jobs\ProcessPayoutJob::dispatch($wd, $providerMode);

    return $wd->fresh();
}
}
