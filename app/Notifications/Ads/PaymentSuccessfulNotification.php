<?php

declare(strict_types=1);

namespace App\Notifications\Ads;

use App\Models\Ads\AdvertisementPayment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PaymentSuccessfulNotification extends Notification
{
    use Queueable;

    public function __construct(protected AdvertisementPayment $payment)
    {
    }

    public function via(mixed $notifiable): array
    {
        return ['database'];
    }

    public function toArray(mixed $notifiable): array
    {
        return [
            'type' => 'payment_successful',
            'payment_id' => $this->payment->id,
            'reference' => $this->payment->reference,
            'amount' => (string) $this->payment->amount,
            'message' => "Payment of ₦{$this->payment->amount} was successful.",
        ];
    }
}