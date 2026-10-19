<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WithdrawalCompletedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $amount,
        public string $currency = 'NGN',
        public ?string $bankName = null,
        public ?string $accountNumber = null
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $formattedAmount = number_format((float)$this->amount, 2);

        $mail = (new MailMessage)
            ->subject("Withdrawal Successful: {$this->currency} {$formattedAmount}")
            ->greeting("Hello {$notifiable->first_name},")
            ->line("Your withdrawal request of {$this->currency} {$formattedAmount} has been processed successfully.");

        if ($this->bankName && $this->accountNumber) {
            $mail->line("Destination Bank: {$this->bankName} ({$this->accountNumber})");
        }

        return $mail->line("Thank you for using AgreonPay!");
    }
}
