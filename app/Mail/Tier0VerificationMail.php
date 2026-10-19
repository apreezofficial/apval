<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class Tier0VerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly string $verificationUrl
    ) {}

    public function envelope(): Envelope
    {
        $fromAddress = config('mail.from.address');
        if (empty($fromAddress) || str_ends_with($fromAddress, '@agreonpay.com')) {
            $fromAddress = 'noreply@agreonpay.com.ng';
        }
        $fromName = config('mail.from.name', 'AgreonPay') ?: 'AgreonPay';

        return new Envelope(
            from: new Address($fromAddress, $fromName),
            subject: 'Welcome to AgreonPay — Your Tier 0 Account is Active',
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlString: $this->buildHtmlContent(),
        );
    }

    public function buildHtmlContent(): string
    {
        $name = htmlspecialchars($this->user->first_name ?: 'Valued User');
        $verifyUrl = htmlspecialchars($this->verificationUrl);
        $appUrl = 'agreonpay://kyc/status';

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to AgreonPay</title>
</head>
<body style="font-family: Arial, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background-color: #F3F4F6; margin: 0; padding: 32px 16px;">
    <div style="max-width: 540px; margin: 0 auto; background-color: #FFFFFF; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.06); border: 1px solid #E5E7EB;">
        
        <!-- Header -->
        <div style="background-color: #1E7B42; padding: 28px 24px; text-align: center;">
            <h1 style="color: #FFFFFF; margin: 0; font-size: 26px; font-weight: 800; letter-spacing: -0.5px;">AgreonPay</h1>
            <p style="color: #D1FAE5; margin: 6px 0 0 0; font-size: 14px; font-weight: 500;">Agricultural & Fintech Ecosystem</p>
        </div>

        <!-- Body -->
        <div style="padding: 32px 28px; color: #1F2937;">
            <div style="display: inline-block; background-color: #EAF6EE; color: #1E7B42; font-size: 12px; font-weight: 700; padding: 4px 12px; border-radius: 20px; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 16px;">
                ✓ Tier 0 Verified
            </div>

            <h2 style="margin: 0 0 12px 0; font-size: 22px; font-weight: 700; color: #111827;">
                Welcome, {$name}!
            </h2>
            <p style="font-size: 15px; color: #4B5563; line-height: 1.6; margin: 0 0 20px 0;">
                Your AgreonPay account has been successfully created and your email is verified. You are currently operating at <strong>Tier 0</strong> status.
            </p>

            <!-- Tier Limits Box -->
            <div style="background-color: #F9FAFB; border: 1px solid #E5E7EB; border-radius: 12px; padding: 18px 20px; margin-bottom: 24px;">
                <h3 style="margin: 0 0 12px 0; font-size: 14px; font-weight: 700; color: #374151; text-transform: uppercase; letter-spacing: 0.5px;">
                    Your Tier 0 Account Limits
                </h3>
                <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                    <tr>
                        <td style="padding: 6px 0; color: #6B7280;">Daily Transaction Limit:</td>
                        <td style="padding: 6px 0; font-weight: 700; color: #111827; text-align: right;">₦20,000.00</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; color: #6B7280;">Maximum Wallet Balance:</td>
                        <td style="padding: 6px 0; font-weight: 700; color: #111827; text-align: right;">₦50,000.00</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; color: #6B7280;">Virtual Bank Account:</td>
                        <td style="padding: 6px 0; font-weight: 600; color: #D97706; text-align: right;">Requires Tier 1 (BVN)</td>
                    </tr>
                </table>
            </div>

            <!-- CTA Button -->
            <div style="text-align: center; margin: 28px 0 20px 0;">
                <a href="{$verifyUrl}" style="display: inline-block; background-color: #1E7B42; color: #FFFFFF; text-decoration: none; font-size: 15px; font-weight: 700; padding: 14px 28px; border-radius: 12px; box-shadow: 0 4px 12px rgba(30,123,66,0.25);">
                    Confirm Tier 0 & View Account
                </a>
            </div>

            <p style="font-size: 13px; color: #6B7280; text-align: center; margin: 0 0 16px 0;">
                Ready to unlock higher limits up to <strong>₦200,000/day</strong> and get your dedicated bank account?
                <br>
                Open the app and verify your BVN to reach <strong>Tier 1</strong> in under 60 seconds!
            </p>
        </div>

        <!-- Footer -->
        <div style="background-color: #F9FAFB; padding: 18px 24px; text-align: center; font-size: 12px; color: #9CA3AF; border-top: 1px solid #E5E7EB;">
            &copy; 2026 AgreonPay Nigeria. All rights reserved.<br>
            If you did not create this account, please contact <a href="mailto:support@agreonpay.com.ng" style="color: #1E7B42; text-decoration: none;">support@agreonpay.com.ng</a> immediately.
        </div>
    </div>
</body>
</html>
HTML;
    }
}
