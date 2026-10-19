<?php

declare(strict_types=1);

namespace App\Services\Security;

use App\Mail\OtpMail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ResendEmailService
{
    /**
     * Send OTP email using Resend API or Laravel Mail fallback.
     */
    public function sendOtpEmail(string $recipientEmail, string $otp, string $purpose = 'verification'): bool
    {
        $apiKey = config('services.resend.key') ?? env('RESEND_API_KEY');
        $fromAddress = config('mail.from.address');
        if (empty($fromAddress) || str_ends_with($fromAddress, '@agreonpay.com')) {
            $fromAddress = 'noreply@agreonpay.com.ng';
        }
        $fromName = config('mail.from.name', 'AgreonPay') ?: 'AgreonPay';

        // If Resend API key is present, use Resend HTTP API directly
        if (!empty($apiKey)) {
            try {
                $mailable = new OtpMail($otp, $purpose);
                $response = Http::withToken($apiKey)
                    ->acceptJson()
                    ->withOptions([
                        'curl' => [
                            CURLOPT_RESOLVE => [
                                'api.resend.com:443:104.20.29.242',
                                'api.resend.com:443:172.66.165.132',
                            ],
                        ],
                    ])
                    ->post('https://api.resend.com/emails', [
                        'from' => sprintf('%s <%s>', $fromName, $fromAddress),
                        'to' => [$recipientEmail],
                        'subject' => sprintf('Your %s Code: %s', ucfirst($purpose), $otp),
                        'html' => $mailable->buildHtmlContent(),
                    ]);

                if ($response->successful()) {
                    Log::info("Resend OTP email delivered", [
                        'recipient' => $recipientEmail,
                        'resend_id' => $response->json('id'),
                        'purpose' => $purpose,
                    ]);
                    return true;
                }

                Log::error("Resend API error", [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            } catch (\Throwable $e) {
                Log::error("Resend HTTP request failed", [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Fallback to standard Laravel Mail facade
        try {
            Mail::to($recipientEmail)->send(new OtpMail($otp, $purpose));
            Log::info("Standard Mail OTP delivered", ['recipient' => $recipientEmail]);
            return true;
        } catch (\Throwable $e) {
            Log::warning("Fallback Mail delivery failed", ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Send Tier 0 Verification & Welcome email with confirmation link.
     */
    public function sendTier0VerificationEmail(\App\Models\User $user, string $verificationUrl): bool
    {
        $apiKey = config('services.resend.key') ?? env('RESEND_API_KEY');
        $fromAddress = config('mail.from.address');
        if (empty($fromAddress) || str_ends_with($fromAddress, '@agreonpay.com')) {
            $fromAddress = 'noreply@agreonpay.com.ng';
        }
        $fromName = config('mail.from.name', 'AgreonPay') ?: 'AgreonPay';

        $mailable = new \App\Mail\Tier0VerificationMail($user, $verificationUrl);

        if (!empty($apiKey)) {
            try {
                $response = Http::withToken($apiKey)
                    ->acceptJson()
                    ->post('https://api.resend.com/emails', [
                        'from' => sprintf('%s <%s>', $fromName, $fromAddress),
                        'to' => [$user->email],
                        'subject' => 'Welcome to AgreonPay — Your Tier 0 Account is Active',
                        'html' => $mailable->buildHtmlContent(),
                    ]);

                if ($response->successful()) {
                    Log::info("Tier 0 Verification email delivered", [
                        'recipient' => $user->email,
                        'resend_id' => $response->json('id'),
                    ]);
                    return true;
                }

                Log::error("Resend Tier 0 email API error", [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            } catch (\Throwable $e) {
                Log::error("Resend Tier 0 HTTP request failed", [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        try {
            Mail::to($user->email)->send($mailable);
            Log::info("Standard Mail Tier 0 delivered", ['recipient' => $user->email]);
            return true;
        } catch (\Throwable $e) {
            Log::warning("Fallback Mail Tier 0 delivery failed", ['error' => $e->getMessage()]);
            return false;
        }
    }
}
