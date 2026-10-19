<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\OrderItem;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewVendorOrderNotification extends Notification
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
        $formattedAmount = number_format((float)$this->orderItem->total_price, 2);

        return (new MailMessage)
            ->subject("New Order Received #{$this->orderItem->id}")
            ->greeting("Hello {$notifiable->first_name},")
            ->line("You have received a new order item for '{$this->orderItem->product->name}'.")
            ->line("Quantity: {$this->orderItem->quantity}")
            ->line("Total Price: NGN {$formattedAmount}")
            ->line("Please log into your vendor dashboard to accept and process this order.");
    }
}
