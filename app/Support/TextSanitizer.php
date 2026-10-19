<?php
namespace App\Support;
class TextSanitizer
{
    public static function narration(?string $text, int $max = 40): string
    {
        $text = trim((string) $text);
        if ($text === '') $text = 'AgreonPay withdrawal';

        // Remove weird characters (keep letters, numbers, spaces, dash)
        $text = preg_replace('/[^A-Za-z0-9 \-]/', '', $text) ?? 'AgreonPay withdrawal';

        // Collapse whitespace
        $text = preg_replace('/\s+/', ' ', $text) ?? $text;

        return mb_substr($text, 0, $max);
    }
}
