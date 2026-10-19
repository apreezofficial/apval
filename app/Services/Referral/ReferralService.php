<?php

declare(strict_types=1);

namespace App\Services\Referral;

use App\Models\Referral;
use App\Models\User;
use App\Services\Wallet\AgroPointsService;
use Illuminate\Support\Facades\DB;

class ReferralService
{
    public function __construct(
        private AgroPointsService $agroPointsService
    ) {}

    /**
     * Get summary of user's referral code, stats, and referred friends list.
     */
    public function getReferralSummary(User $user): array
    {
        if (empty($user->referral_code)) {
            $user->referral_code = 'AGP-' . strtoupper(\Illuminate\Support\Str::random(6));
            $user->save();
        }

        $referrals = Referral::with('referee:id,first_name,last_name,email,created_at')
            ->where('referrer_id', $user->id)
            ->latest()
            ->get();

        $totalRewardPoints = $referrals->where('status', 'rewarded')->sum('reward_points');

        return [
            'referral_code' => $user->referral_code,
            'share_link' => config('app.url') . '/register?ref=' . $user->referral_code,
            'total_referrals' => $referrals->count(),
            'pending_referrals' => $referrals->where('status', 'pending')->count(),
            'rewarded_referrals' => $referrals->where('status', 'rewarded')->count(),
            'total_points_earned' => (string) number_format((float)$totalRewardPoints, 2),
            'referees' => $referrals->map(function ($ref) {
                return [
                    'id' => $ref->referee_id,
                    'name' => trim(($ref->referee->first_name ?? '') . ' ' . ($ref->referee->last_name ?? '')),
                    'email' => $ref->referee->email ?? null,
                    'status' => $ref->status,
                    'joined_at' => $ref->created_at->toIso8601String(),
                    'rewarded_at' => $ref->rewarded_at?->toIso8601String(),
                ];
            }),
        ];
    }

    /**
     * Trigger referral reward when a referee completes their first order.
     */
    public function processQualifyingOrder(User $buyer): void
    {
        $referral = Referral::where('referee_id', $buyer->id)
            ->where('status', 'pending')
            ->first();

        if (!$referral) {
            return;
        }

        DB::transaction(function () use ($referral, $buyer) {
            $referrer = User::find($referral->referrer_id);

            // Award 50 Agro Points to Referrer
            if ($referrer) {
                $referrerWallet = $referrer->wallets()->where('currency', 'NGN')->first();
                if ($referrerWallet) {
                    $this->agroPointsService->addPoints($referrerWallet, '50.0000');
                }
            }

            // Award 20 Agro Points to Referee (Buyer)
            $buyerWallet = $buyer->wallets()->where('currency', 'NGN')->first();
            if ($buyerWallet) {
                $this->agroPointsService->addPoints($buyerWallet, '20.0000');
            }

            $referral->update([
                'status' => 'rewarded',
                'reward_points' => '50.0000',
                'rewarded_at' => now(),
            ]);
        });
    }

    /**
     * Trigger referral reward when a referee funds their wallet for the first time.
     */
    public function processQualifyingDeposit(User $user): void
    {
        $referral = Referral::where('referee_id', $user->id)
            ->where('status', 'pending')
            ->first();

        if (!$referral) {
            return;
        }

        DB::transaction(function () use ($referral, $user) {
            $referrer = User::find($referral->referrer_id);

            // Award 50 Agro Points to Referrer
            if ($referrer) {
                $referrerWallet = $referrer->wallets()->where('currency', 'NGN')->first();
                if ($referrerWallet) {
                    $this->agroPointsService->addPoints($referrerWallet, '50.0000');
                }
            }

            // Award 25 Agro Points to Referee for their first funding
            $userWallet = $user->wallets()->where('currency', 'NGN')->first();
            if ($userWallet) {
                $this->agroPointsService->addPoints($userWallet, '25.0000');
            }

            $referral->update([
                'status' => 'rewarded',
                'reward_points' => '50.0000',
                'rewarded_at' => now(),
            ]);
        });
    }
}
