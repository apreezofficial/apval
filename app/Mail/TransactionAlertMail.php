<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TransactionAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param string $type 'credit' or 'debit'
     * @param string|float $amount Formatted amount e.g. 5,000.00
     * @param string $currency 'NGN', 'USDT', etc.
     * @param string|float|null $newBalance User's balance after transaction
     * @param string $reference Transaction reference
     * @param string|null $description Narrative/description
     * @param string|null $counterparty Sender or Recipient name/account
     * @param string|null $dateTime Formatted date string
     */
    public function __construct(
        public string $type,
        public string|float $amount,
        public string $currency = 'NGN',
        public string|float|null $newBalance = null,
        public string $reference = '',
        public ?string $description = null,
        public ?string $counterparty = null,
        public ?string $dateTime = null,
    ) {
        $this->type = strtolower($type);
        $this->dateTime = $dateTime ?: now()->format('d M, Y h:i A');
    }

    public function envelope(): Envelope
    {
        $symbol = $this->currency === 'NGN' ? '₦' : ($this->currency === 'USDT' ? '$' : $this->currency . ' ');
        $formattedAmount = is_numeric($this->amount) ? number_format((float)$this->amount, 2) : $this->amount;
        $title = $this->type === 'credit' ? 'Credit Alert' : 'Debit Alert';
        $subject = sprintf('[%s] %s%s - %s', $title, $symbol, $formattedAmount, config('app.name', 'AgreonPay'));

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
        $isCredit = $this->type === 'credit';
        $headerColor = $isCredit ? '#059669' : '#DC2626';
        $badgeBg = $isCredit ? '#ECFDF5' : '#FEF2F2';
        $badgeColor = $isCredit ? '#047857' : '#B91C1C';
        $typeLabel = $isCredit ? 'CREDIT TRANSACTION' : 'DEBIT TRANSACTION';
        $symbol = $this->currency === 'NGN' ? '₦' : ($this->currency === 'USDT' ? 'USDT ' : $this->currency . ' ');
        $formattedAmount = is_numeric($this->amount) ? number_format((float)$this->amount, 2) : $this->amount;
        $sign = $isCredit ? '+' : '-';
        $year = date('Y');

        $balanceRow = '';
        if ($this->newBalance !== null) {
            $formattedBalance = is_numeric($this->newBalance) ? number_format((float)$this->newBalance, 2) : $this->newBalance;
            $balanceRow = <<<HTML
            <tr style="border-bottom: 1px solid #F3F4F6;">
                <td style="padding: 12px 0; color: #6B7280; font-size: 14px;">Available Balance</td>
                <td style="padding: 12px 0; color: #111827; font-size: 14px; font-weight: 600; text-align: right;">{$symbol}{$formattedBalance}</td>
            </tr>
HTML;
        }

        $counterpartyLabel = $isCredit ? 'Sender / Source' : 'Recipient / Beneficiary';
        $counterpartyRow = '';
        if ($this->counterparty) {
            $counterpartyRow = <<<HTML
            <tr style="border-bottom: 1px solid #F3F4F6;">
                <td style="padding: 12px 0; color: #6B7280; font-size: 14px;">{$counterpartyLabel}</td>
                <td style="padding: 12px 0; color: #111827; font-size: 14px; font-weight: 600; text-align: right;">{$this->counterparty}</td>
            </tr>
HTML;
        }

        $descriptionRow = '';
        if ($this->description) {
            $descriptionRow = <<<HTML
            <tr style="border-bottom: 1px solid #F3F4F6;">
                <td style="padding: 12px 0; color: #6B7280; font-size: 14px;">Description / Narration</td>
                <td style="padding: 12px 0; color: #111827; font-size: 14px; font-weight: 500; text-align: right;">{$this->description}</td>
            </tr>
HTML;
        }

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>AgreonPay Transaction Notification</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #F8FAFC; margin: 0; padding: 32px 16px;">
    <div style="max-width: 520px; margin: 0 auto; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.06); border: 1px solid #E2E8F0;">
        <!-- Header -->
        <div style="background-color: {$headerColor}; padding: 28px 24px; text-align: center;">
            <h1 style="color: #ffffff; margin: 0; font-size: 22px; font-weight: 800; letter-spacing: -0.5px;">AgreonPay</h1>
            <p style="color: rgba(255,255,255,0.85); margin: 6px 0 0; font-size: 13px;">Transaction Alert</p>
        </div>

        <!-- Body -->
        <div style="padding: 32px 28px;">
            <div style="text-align: center; margin-bottom: 24px;">
                <span style="background-color: {$badgeBg}; color: {$badgeColor}; font-size: 11px; font-weight: 700; letter-spacing: 1px; padding: 6px 14px; border-radius: 20px; display: inline-block;">
                    {$typeLabel}
                </span>
                <div style="font-size: 32px; font-weight: 800; color: {$headerColor}; margin-top: 14px; letter-spacing: -1px;">
                    {$sign}{$symbol}{$formattedAmount}
                </div>
            </div>

            <table style="width: 100%; border-collapse: collapse; margin-top: 16px;">
                <tr style="border-bottom: 1px solid #F3F4F6;">
                    <td style="padding: 12px 0; color: #6B7280; font-size: 14px;">Date & Time</td>
                    <td style="padding: 12px 0; color: #111827; font-size: 14px; font-weight: 600; text-align: right;">{$this->dateTime}</td>
                </tr>
                <tr style="border-bottom: 1px solid #F3F4F6;">
                    <td style="padding: 12px 0; color: #6B7280; font-size: 14px;">Reference</td>
                    <td style="padding: 12px 0; color: #111827; font-size: 14px; font-weight: 600; font-family: monospace; text-align: right;">{$this->reference}</td>
                </tr>
                {$counterpartyRow}
                {$descriptionRow}
                {$balanceRow}
            </table>

            <div style="margin-top: 28px; padding: 14px; background-color: #F8FAFC; border-radius: 10px; border-left: 4px solid {$headerColor};">
                <p style="margin: 0; font-size: 12px; color: #64748B; line-height: 1.5;">
                    If you did not authorize this transaction or notice any suspicious activity on your account, please contact our support team immediately at <strong>support@agreonpay.com.ng</strong>.
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div style="background-color: #F8FAFC; padding: 20px 24px; text-align: center; font-size: 12px; color: #94A3B8; border-top: 1px solid #E2E8F0;">
            <p style="margin: 0 0 6px 0;">&copy; {$year} AgreonPay Technologies. All rights reserved.</p>
            <p style="margin: 0;">Securing agricultural commerce and instant payments across Africa.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
}
