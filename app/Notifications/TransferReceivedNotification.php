<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TransferReceivedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $amount,
        public string $senderName,
        public string $currency = 'NGN'
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $formattedAmount = number_format((float)$this->amount, 2);

        return (new MailMessage)
            ->subject("Wallet Transfer Received: {$this->currency} {$formattedAmount}")
            ->greeting("Hello {$notifiable->first_name},")
            ->line("You have received a wallet transfer of {$this->currency} {$formattedAmount} from {$this->senderName}.")
            ->line("The funds are immediately available in your AgreonPay wallet balance.");
    }
}
