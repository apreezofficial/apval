<?php

declare(strict_types=1);

namespace App\Notifications\Ads;

use App\Models\Ads\AdvertisementRefund;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class RefundProcessedNotification extends Notification
{
    use Queueable;

    public function __construct(protected AdvertisementRefund $refund)
    {
    }

    public function via(mixed $notifiable): array
    {
        return ['database'];
    }

    public function toArray(mixed $notifiable): array
    {
        return [
            'type' => 'refund_processed',
            'refund_id' => $this->refund->id,
            'reference' => $this->refund->reference,
            'amount' => (string) $this->refund->amount,
            'message' => "A refund of ₦{$this->refund->amount} has been processed.",
        ];
    }
}