<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\OrderItem;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EscrowRefundedNotification extends Notification
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
            ->subject("Escrow Refund Credited: NGN {$formattedAmount}")
            ->greeting("Hello {$notifiable->first_name},")
            ->line("Your order item '{$this->orderItem->product->name}' was rejected or cancelled.")
            ->line("An Escrow refund of NGN {$formattedAmount} has been credited back to your AgreonPay wallet balance.")
            ->line("Thank you for using AgreonPay.");
    }
}
