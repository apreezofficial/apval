<?php

declare(strict_types=1);

namespace App\Notifications\Ads;

use App\Models\Ads\Advertisement;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AdRejectedNotification extends Notification
{
    use Queueable;

    public function __construct(protected Advertisement $advertisement, protected string $reason)
    {
    }

    public function via(mixed $notifiable): array
    {
        return ['database'];
    }

    public function toArray(mixed $notifiable): array
    {
        return [
            'type' => 'ad_rejected',
            'advertisement_id' => $this->advertisement->id,
            'reference' => $this->advertisement->reference,
            'title' => $this->advertisement->title,
            'reason' => $this->reason,
            'message' => "Advertisement rejected: {$this->reason}",
        ];
    }
}