<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderPlacedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Order $order
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $formattedTotal = number_format((float)$this->order->total_amount, 2);

        return (new MailMessage)
            ->subject("AgreonPay Order Receipt #{$this->order->id}")
            ->greeting("Hello {$notifiable->first_name},")
            ->line("Thank you for your purchase! Your order #{$this->order->id} has been placed successfully.")
            ->line("Total Paid: NGN {$formattedTotal}")
            ->line("Your payment is securely held in System Escrow until your items are delivered and verified.")
            ->line("Thank you for shopping with AgreonPay!");
    }
}
