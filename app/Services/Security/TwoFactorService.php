<?php

declare(strict_types=1);

namespace App\Services\Security;

use App\Exceptions\Domain\InvalidOtpException;
use App\Exceptions\Domain\TwoFactorRequiredException;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Laravel\Fortify\TwoFactorAuthenticationProvider;

use App\Exceptions\Domain\OtpCooldownException;

class TwoFactorService
{
    public function __construct(
        private TwoFactorAuthenticationProvider $provider,
        private ?ResendEmailService $resendEmailService = null
    ) {
        $this->resendEmailService = $resendEmailService ?? app(ResendEmailService::class);
    }

    /**
     * Send OTP for step-up actions (withdrawal/transfer/login).
     * Works for email/sms channels.
     */
    public function sendOtp(User $user, string $purpose, bool $isResend = false): void
    {
        if ($purpose !== 'verification') {
            $this->assert2faEnabled($user);
        }

        // If user is on authenticator, don't generate/send OTP.
        if (!empty($user->two_factor_secret)) {
            return;
        }

        $cooldownKey = $this->cooldownCacheKey($user->id, $purpose);

        // Enforce 60-second cooldown on resends
        if ($isResend && Cache::has($cooldownKey)) {
            throw new OtpCooldownException('Please wait 60 seconds before requesting another OTP.');
        }

        $otp = (string) random_int(100000, 999999);
        $key = $this->cacheKey($user->id, $purpose);

        Cache::put($key, password_hash($otp, PASSWORD_BCRYPT), now()->addMinutes(5));
        Cache::put($cooldownKey, true, now()->addSeconds(60));

        // Log OTP dispatch event without revealing the code itself
        logger()->info("OTP dispatched", [
            'user_id' => $user->id,
            'channel' => $user->two_factor_channel ?? 'email',
            'purpose' => $purpose,
            'is_resend' => $isResend,
        ]);

        // Deliver via Resend.com / Email service
        if (!empty($user->email)) {
            $this->resendEmailService->sendOtpEmail($user->email, $otp, $purpose);
        }
    }

    /**
     * Resend a fresh OTP code with 60-second cooldown protection.
     */
    public function resendOtp(User $user, string $purpose): void
    {
        $this->sendOtp($user, $purpose, true);
    }

    /**
     * Verify OTP/TOTP for step-up actions.
     * - If Fortify secret exists => verify TOTP via provider
     * - Else => verify cached OTP
     */
    public function assertValidOtp(User $user, ?string $otp, string $purpose = 'withdrawal'): void
    {
        // 0) OTP required
        if (!$otp) {
            throw new TwoFactorRequiredException('OTP is required.');
        }

        // 1) Ensure user has 2FA enabled (either method), unless purpose is verification
        if ($purpose !== 'verification') {
            $this->assert2faEnabled($user);
        }

        // 3) If user has Fortify authenticator secret, verify TOTP
        if (!empty($user->two_factor_secret)) {
            $secret = $this->decryptSecret($user->two_factor_secret);

            $ok = $this->provider->verify($secret, $otp);
            if (!$ok) {
                throw new InvalidOtpException('Invalid or expired OTP.');
            }

            return;
        }

        // 4) Otherwise validate cached OTP (email/sms)
        $hash = Cache::get($this->cacheKey($user->id, $purpose));
        if (!$hash || !password_verify($otp, $hash)) {
            throw new InvalidOtpException('Invalid or expired OTP.');
        }

        // Consume OTP (one-time)
        Cache::forget($this->cacheKey($user->id, $purpose));

        if ($purpose === 'verification' && empty($user->email_verified_at)) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }
    }

    private function assert2faEnabled(User $user): void
    {
        // Accept either:
        // - "two_factor_enabled" flag (email/sms OTP)
        // - Fortify secret present (authenticator)
        $enabled = (bool) ($user->two_factor_enabled ?? false);
        $hasSecret = !empty($user->two_factor_secret);

        if (!$enabled && !$hasSecret) {
            throw new TwoFactorRequiredException('2FA must be enabled before this action.');
        }
    }

    private function decryptSecret(string $encryptedOrPlain): string
    {
        try {
            return Crypt::decryptString($encryptedOrPlain);
        } catch (\Throwable $e) {
            return $encryptedOrPlain; // dev fallback
        }
    }

    private function cacheKey(string $userId, string $purpose): string
    {
        return "twofa:{$purpose}:{$userId}";
    }

    private function cooldownCacheKey(string $userId, string $purpose): string
    {
        return "twofa:cooldown:{$purpose}:{$userId}";
    }
}
