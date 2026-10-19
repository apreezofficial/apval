<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DepositReceivedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $amount,
        public string $currency = 'NGN',
        public ?string $reference = null
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $formattedAmount = number_format((float)$this->amount, 2);

        return (new MailMessage)
            ->subject("Wallet Deposit Confirmed: {$this->currency} {$formattedAmount}")
            ->greeting("Hello {$notifiable->first_name},")
            ->line("Your wallet has been successfully credited with {$this->currency} {$formattedAmount}.")
            ->lineIf($this->reference !== null, "Transaction Reference: {$this->reference}")
            ->line("Thank you for using AgreonPay!");
    }
}
