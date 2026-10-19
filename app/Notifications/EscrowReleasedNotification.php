<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\OrderItem;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EscrowReleasedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public OrderItem $orderItem,
        public string $amount
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $formattedAmount = number_format((float)$this->amount, 2);

        return (new MailMessage)
            ->subject("Escrow Payout Released: NGN {$formattedAmount}")
            ->greeting("Hello {$notifiable->first_name},")
            ->line("Good news! Delivery has been confirmed for order item '{$this->orderItem->product->name}'.")
            ->line("System Escrow funds of NGN {$formattedAmount} have been released and credited to your AgreonPay wallet balance.")
            ->line("Thank you for selling with AgreonPay!");
    }
}
