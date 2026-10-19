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

class WelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user
    ) {}

    public function envelope(): Envelope
    {
        $fromAddress = config('mail.from.address') ?: '_mainaccount@agreonpay.com.ng';
        $fromName = config('mail.from.name', 'AgreonPay') ?: 'AgreonPay';

        return new Envelope(
            from: new Address($fromAddress, $fromName),
            subject: 'Welcome to AgreonPay! 🎉 Your Digital Wallet is Ready'
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlString: $this->buildHtmlContent()
        );
    }

    public function buildHtmlContent(): string
    {
        $name = htmlspecialchars(trim(($this->user->first_name ?? '') . ' ' . ($this->user->last_name ?? '')) ?: 'Valued User');
        $referralCode = htmlspecialchars($this->user->referral_code ?? 'N/A');
        $year = date('Y');

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Welcome to AgreonPay</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #F8FAFC; margin: 0; padding: 32px 16px;">
    <div style="max-width: 520px; margin: 0 auto; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.06); border: 1px solid #E2E8F0;">
        <!-- Header -->
        <div style="background-color: #059669; padding: 32px 24px; text-align: center;">
            <h1 style="color: #ffffff; margin: 0; font-size: 24px; font-weight: 800; letter-spacing: -0.5px;">AgreonPay</h1>
            <p style="color: rgba(255,255,255,0.9); margin: 8px 0 0; font-size: 14px;">Welcome to the future of agricultural payments</p>
        </div>

        <!-- Body -->
        <div style="padding: 32px 28px; color: #1E293B;">
            <h2 style="font-size: 20px; font-weight: 700; color: #0F172A; margin-top: 0;">Welcome aboard, {$name}! 👋</h2>
            
            <p style="font-size: 15px; color: #475569; line-height: 1.6;">
                We are thrilled to have you with us. Your AgreonPay account has been successfully created and your digital wallet is ready for seamless transactions.
            </p>

            <!-- Highlights Box -->
            <div style="background-color: #F0FDF4; border: 1px solid #BBF7D0; border-radius: 12px; padding: 20px; margin: 24px 0;">
                <h3 style="margin: 0 0 12px 0; font-size: 15px; font-weight: 700; color: #166534;">Here is what you can do right now:</h3>
                <ul style="margin: 0; padding-left: 20px; color: #15803D; font-size: 14px; line-height: 1.8;">
                    <li><strong>Dedicated Bank Account:</strong> Receive instant bank transfers directly into your AgreonPay wallet.</li>
                    <li><strong>Zero-Fee Peer Transfers:</strong> Send money instantly to any AgreonPay user.</li>
                    <li><strong>Instant Bank Withdrawals:</strong> Withdraw your funds to any commercial or microfinance bank in Nigeria.</li>
                    <li><strong>Earn Referral Rewards:</strong> Share your unique referral code <strong>{$referralCode}</strong> to earn Agro Points!</li>
                </ul>
            </div>

            <!-- Referral Box -->
            <div style="background-color: #F8FAFC; border: 1px dashed #CBD5E1; border-radius: 12px; padding: 18px; text-align: center; margin-bottom: 24px;">
                <p style="margin: 0 0 6px 0; font-size: 12px; font-weight: 600; text-transform: uppercase; color: #64748B; letter-spacing: 0.5px;">Your Unique Referral Code</p>
                <div style="font-size: 22px; font-weight: 800; color: #059669; letter-spacing: 2px; font-family: monospace;">{$referralCode}</div>
            </div>

            <!-- Security Note -->
            <div style="background-color: #FFFBEB; border-radius: 10px; padding: 14px; border-left: 4px solid #F59E0B;">
                <p style="margin: 0; font-size: 12px; color: #92400E; line-height: 1.5;">
                    <strong>Security Reminder:</strong> AgreonPay staff will never ask for your password, transaction PIN, or OTP. Never share your security credentials with anyone.
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div style="background-color: #F8FAFC; padding: 20px 24px; text-align: center; font-size: 12px; color: #94A3B8; border-top: 1px solid #E2E8F0;">
            <p style="margin: 0 0 6px 0;">&copy; {$year} AgreonPay Technologies. All rights reserved.</p>
            <p style="margin: 0;">Need assistance? Reach us at <a href="mailto:support@agreonpay.com.ng" style="color: #059669; text-decoration: none;">support@agreonpay.com.ng</a></p>
        </div>
    </div>
</body>
</html>
HTML;
    }
}
