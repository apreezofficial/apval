<?php

namespace App\Services\Security;

use App\Exceptions\Domain\InvalidRequestException;
use App\Exceptions\Domain\TwoFactorRequiredException;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class PinService
{
    public function setPin(User $user, string $pin): void
    {
        if (!preg_match('/^\d{4,6}$/', $pin)) {
            throw new InvalidRequestException('PIN must be 4 to 6 digits.');
        }

        $user->transaction_pin = Hash::make($pin);
        $user->transaction_pin_set_at = now();
        $user->transaction_pin_attempts = 0;
        $user->transaction_pin_locked_until = null;
        $user->save();
    }

    public function assertValidPin(User $user, ?string $pin): void
    {
        $user->refresh();

        if (!$user->transaction_pin) {
            throw new TwoFactorRequiredException('Transaction PIN must be set before this action.');
        }

        if ($user->transaction_pin_locked_until && now()->lt($user->transaction_pin_locked_until)) {
            throw new InvalidRequestException('Transaction PIN is temporarily locked. Try again later.');
        }

        if (!$pin) {
            throw new InvalidRequestException('Transaction PIN is required.');
        }

        if (!Hash::check($pin, $user->transaction_pin)) {
            $attempts = (int) $user->transaction_pin_attempts + 1;

            $user->transaction_pin_attempts = $attempts;

            if ($attempts >= 5) {
                $user->transaction_pin_locked_until = now()->addMinutes(15);
                $user->transaction_pin_attempts = 0;
            }

            $user->save();

            throw new InvalidRequestException('Invalid transaction PIN.');
        }

        $user->transaction_pin_attempts = 0;
        $user->transaction_pin_locked_until = null;
        $user->save();
    }
}
