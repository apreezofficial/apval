<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Exceptions\Domain\InvalidCredentialsException;
use App\Exceptions\Domain\UserSuspendedException;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Authenticate user and return a token.
     */
    public function login(string $loginId, string $password, string $deviceName): array
    {
        $normalizedLogin = trim($loginId);
        $lowercased = strtolower($normalizedLogin);

        // Normalize potential phone variants (080... <-> +23480...)
        $phoneVariants = [$normalizedLogin];
        if (preg_match('/^0([789][01]\d{8})$/', $normalizedLogin, $m)) {
            $phoneVariants[] = '+234' . $m[1];
            $phoneVariants[] = '234' . $m[1];
        } elseif (preg_match('/^\+?234([789][01]\d{8})$/', $normalizedLogin, $m)) {
            $phoneVariants[] = '0' . $m[1];
            $phoneVariants[] = '+234' . $m[1];
            $phoneVariants[] = '234' . $m[1];
        }

        /** @var User|null $user */
        $user = User::where('email', $lowercased)
            ->orWhereIn('phone', $phoneVariants)
            ->first();

        if (!$user || !Hash::check($password, $user->password)) {
            throw new InvalidCredentialsException();
        }

        if ($user->status !== 'active') {
            throw new UserSuspendedException('User account is not active.');
        }

        $token = $user->createToken($deviceName)->plainTextToken;

        return [
            'token' => $token,
            'user' => $user->load(['kycRecord', 'wallets']),
        ];
    }

    /**
     * Revoke the user's current token.
     */
    public function logout(User $user): void
    {
        $user->currentAccessToken()?->delete();
    }

    /**
     * Change the authenticated user's password.
     */
    public function changePassword(User $user, string $currentPassword, string $newPassword): void
    {
        if (!Hash::check($currentPassword, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Current password is incorrect.'],
            ]);
        }

        $user->update(['password' => Hash::make($newPassword)]);
        
        // Revoke all tokens to force re-login on all devices
        $user->tokens()->delete();
    }

    /**
     * Send a password reset link.
     */
    public function sendResetLink(array $credentials): string
    {
        return Password::sendResetLink($credentials);
    }

    /**
     * Reset the user's password using a token.
     */
    public function resetPassword(array $credentials): string
    {
        return Password::reset(
            $credentials,
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->save();

                // Revoke tokens on reset
                $user->tokens()->delete();
            }
        );
    }
}
