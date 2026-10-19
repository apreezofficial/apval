<?php

declare(strict_types=1);

namespace App\Services\Wallet;

use App\Exceptions\Domain\InvalidRequestException;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AgroPointsService
{
    public const CONVERSION_RATE_NGN_PER_POINT = 10.0; // 1 Agro Point = N10

    public function __construct(
        private WalletBalanceService $walletBalanceService
    ) {}

    /**
     * Add fixed points to wallet.
     */
    public function addPoints(Wallet $wallet, string $points): float
    {
        $wallet->update([
            'agro_points' => bcadd((string) $wallet->agro_points, $points, 4),
        ]);

        return (float) $points;
    }

    /**
     * Award Agro Points to a wallet based on spend/funding amount.
     * Earns 1 Agro Point for every N1,000 spent.
     */
    public function awardPointsForSpend(Wallet $wallet, string $amountSpent): float
    {
        $pointsEarned = floor(floatval($amountSpent) / 1000);
        if ($pointsEarned <= 0) {
            return 0.0;
        }

        $wallet->update([
            'agro_points' => bcadd((string) $wallet->agro_points, (string) $pointsEarned, 4),
        ]);

        logger()->info("Agro Points awarded", [
            'wallet_id' => $wallet->id,
            'points' => $pointsEarned,
            'amount_spent' => $amountSpent,
        ]);

        return (float) $pointsEarned;
    }

    /**
     * Redeem Agro Points for cash balance in NGN wallet.
     */
    public function redeemPoints(User $user, float $pointsToRedeem): array
    {
        return DB::transaction(function () use ($user, $pointsToRedeem) {
            if ($pointsToRedeem <= 0) {
                throw new InvalidRequestException('Points to redeem must be greater than zero.');
            }

            /** @var Wallet $wallet */
            $wallet = $this->walletBalanceService->lockUserWallet($user->id, 'NGN');

            if (bccomp((string) $wallet->agro_points, (string) $pointsToRedeem, 4) < 0) {
                throw new InvalidRequestException('Insufficient Agro Points balance.');
            }

            $cashCreditAmount = number_format($pointsToRedeem * self::CONVERSION_RATE_NGN_PER_POINT, 2, '.', '');

            // Deduct points
            $newPointsBalance = bcsub((string) $wallet->agro_points, (string) $pointsToRedeem, 4);
            $wallet->update(['agro_points' => $newPointsBalance]);

            // Create credit transaction
            $transaction = Transaction::create([
                'id' => (string) Str::uuid(),
                'reference' => 'AGP_PTS_RED_' . Str::upper(Str::random(10)),
                'idempotency_key' => (string) Str::uuid(),
                'type' => 'points_redemption',
                'status' => 'success',
                'initiated_by' => $user->id,
                'from_wallet_id' => null,
                'to_wallet_id' => $wallet->id,
                'currency' => 'NGN',
                'amount' => $cashCreditAmount,
                'meta' => [
                    'points_redeemed' => $pointsToRedeem,
                    'conversion_rate' => self::CONVERSION_RATE_NGN_PER_POINT,
                ],
                'completed_at' => now(),
            ]);

            // Credit wallet balance
            $this->walletBalanceService->credit($wallet, $cashCreditAmount, $transaction->id);

            return [
                'points_redeemed' => $pointsToRedeem,
                'cash_credited' => $cashCreditAmount,
                'remaining_points' => (float) $newPointsBalance,
                'new_wallet_balance' => (float) $wallet->balance,
            ];
        });
    }
}
