<?php

declare(strict_types=1);

namespace App\Services\Kyc;

use App\Exceptions\Domain\KycRequiredException;
use App\Exceptions\Domain\LimitExceededException;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Carbon;

class KycTierService
{
    /**
     * Get Tier limits metadata for a given level.
     */
    public function getTierLimits(int $level): array
    {
        return match ($level) {
            0 => [
                'tier' => 0,
                'name' => 'Unverified',
                'daily_limit' => '0.00',
                'max_balance' => '50000.00',
                'can_withdraw' => false,
                'can_transfer' => false,
            ],
            1 => [
                'tier' => 1,
                'name' => 'BVN/NIN Verified',
                'daily_limit' => '50000.00',
                'max_balance' => '300000.00',
                'can_withdraw' => true,
                'can_transfer' => true,
            ],
            2 => [
                'tier' => 2,
                'name' => 'ID & Address Verified',
                'daily_limit' => '500000.00',
                'max_balance' => '1000000.00',
                'can_withdraw' => true,
                'can_transfer' => true,
            ],
            3 => [
                'tier' => 3,
                'name' => 'Corporate / High Tier',
                'daily_limit' => '10000000.00',
                'max_balance' => '100000000.00',
                'can_withdraw' => true,
                'can_transfer' => true,
            ],
            default => [
                'tier' => 0,
                'name' => 'Unverified',
                'daily_limit' => '0.00',
                'max_balance' => '0.00',
                'can_withdraw' => false,
                'can_transfer' => false,
            ],
        };
    }

    /**
     * Enforce tier limits for outgoing transactions (transfers/withdrawals).
     */
    public function assertCanTransact(User $user, string $amount, string $action = 'transfer'): void
    {
        $kyc = $user->kycRecord;
        $level = $kyc && $kyc->status === 'verified' ? ($kyc->level ?? 0) : 0;
        $tierInfo = $this->getTierLimits($level);

        if ($action === 'withdraw' && !$tierInfo['can_withdraw']) {
            throw new KycRequiredException('KYC Level 1 (BVN Verification) required to perform withdrawals.');
        }

        if ($action === 'transfer' && !$tierInfo['can_transfer']) {
            throw new KycRequiredException('KYC Level 1 required to perform wallet transfers.');
        }

        // Calculate today's spent volume
        $todaySpent = Transaction::where('initiated_by', $user->id)
            ->whereIn('type', ['transfer', 'withdrawal', 'purchase'])
            ->where('status', 'success')
            ->where('created_at', '>=', Carbon::today())
            ->sum('amount');

        $newDailyTotal = bcadd((string) $todaySpent, $amount, 8);

        if (bccomp($newDailyTotal, (string) $tierInfo['daily_limit'], 8) > 0) {
            throw new LimitExceededException(sprintf(
                'Transaction exceeds your daily limit of N%s for KYC Tier %d.',
                number_format((float)$tierInfo['daily_limit'], 2),
                $level
            ));
        }
    }

    /**
     * Enforce maximum balance limit.
     */
    public function assertCanReceiveDeposit(Wallet $wallet, string $depositAmount): void
    {
        $user = $wallet->user;
        $kyc = $user ? $user->kycRecord : null;
        $level = $kyc && $kyc->status === 'verified' ? ($kyc->level ?? 0) : 0;
        $tierInfo = $this->getTierLimits($level);

        $newBalance = bcadd((string) $wallet->balance, $depositAmount, 8);

        if (bccomp($newBalance, (string) $tierInfo['max_balance'], 8) > 0) {
            throw new LimitExceededException(sprintf(
                'Deposit would exceed your maximum wallet balance limit of N%s for KYC Tier %d.',
                number_format((float)$tierInfo['max_balance'], 2),
                $level
            ));
        }
    }
}
