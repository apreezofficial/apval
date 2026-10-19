<?php

namespace App\Services\Kyc;

final class MonnifySanitizer
{
    /**
     * Keys that should never be persisted in plaintext.
     * Keep this list tight and explicit.
     */
    private const SENSITIVE_KEYS = [
        'bvn',
        'nin',
        'twoFactorSecret',
        'two_factor_secret',
        'password',
        'pin',
        'token',
        'accessToken',
    ];

    /**
     * Sanitize any Monnify payload/response recursively.
     * - Removes sensitive keys wherever they appear.
     * - Optional: mask some values rather than removing (configure below).
     */
    public function sanitize(array $data): array
    {
        return $this->deepStrip($data);
    }

    private function deepStrip(mixed $value): mixed
    {
        if (!is_array($value)) {
            return $value;
        }

        foreach ($value as $k => $v) {
            // Normalize key for matching
            $key = is_string($k) ? strtolower($k) : $k;

            if (is_string($key) && in_array($key, self::SENSITIVE_KEYS, true)) {
                unset($value[$k]);
                continue;
            }

            // Recurse
            $value[$k] = $this->deepStrip($v);
        }

        return $value;
    }
}
