<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $otp,
        public string $purpose = 'verification',
        public int $expiresInMinutes = 5
    ) {}

    public function envelope(): Envelope
    {
        $subject = sprintf('Your %s Code: %s', ucfirst($this->purpose), $this->otp);
        $fromAddress = config('mail.from.address') ?: '_mainaccount@agreonpay.com.ng';
        $fromName = config('mail.from.name', 'AgreonPay') ?: 'AgreonPay';

        return new Envelope(
            from: new Address($fromAddress, $fromName),
            subject: $subject
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
        $year = date('Y');
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>AgreonPay Verification Code</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f6f8; margin: 0; padding: 40px 20px;">
    <div style="max-width: 500px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05); font-family: system-ui, -apple-system, sans-serif;">
        <div style="background-color: #10B981; padding: 24px; text-align: center;">
            <h1 style="color: #ffffff; margin: 0; font-size: 24px; font-weight: 700;">AgreonPay</h1>
        </div>
        <div style="padding: 32px 24px; text-align: center; color: #1F2937;">
            <h2 style="margin-top: 0; font-size: 20px; font-weight: 600;">Verification Code</h2>
            <p style="font-size: 15px; color: #4B5563; line-height: 1.5;">
                Use the verification code below to authorize your <strong>{$this->purpose}</strong> request.
            </p>
            <div style="margin: 28px 0; background-color: #F3F4F6; padding: 18px; border-radius: 8px; letter-spacing: 8px; font-size: 32px; font-weight: 800; color: #047857;">
                {$this->otp}
            </div>
            <p style="font-size: 13px; color: #6B7280; margin-bottom: 0;">
                This code is valid for <strong>{$this->expiresInMinutes} minutes</strong>. Do not share this code with anyone.
            </p>
        </div>
        <div style="background-color: #F9FAFB; padding: 16px 24px; text-align: center; font-size: 12px; color: #9CA3AF; border-top: 1px solid #E5E7EB;">
            &copy; {$year} AgreonPay. All rights reserved.
        </div>
    </div>
</body>
</html>
HTML;
    }
}
