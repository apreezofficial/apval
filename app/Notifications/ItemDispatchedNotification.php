<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\OrderItem;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ItemDispatchedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public OrderItem $orderItem
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Your Order Item Has Been Dispatched #{$this->orderItem->id}")
            ->greeting("Hello {$notifiable->first_name},")
            ->line("Great news! Your order item '{$this->orderItem->product->name}' has been dispatched by the vendor.")
            ->line("You can track shipment progress directly in your order history.")
            ->line("Once received, please confirm delivery in your app to release escrow funds.");
    }
}
