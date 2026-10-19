<?php

declare(strict_types=1);

namespace App\Notifications\Ads;

use App\Models\Ads\Advertisement;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AdChangesRequiredNotification extends Notification
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
            'type' => 'ad_changes_required',
            'advertisement_id' => $this->advertisement->id,
            'reference' => $this->advertisement->reference,
            'title' => $this->advertisement->title,
            'reason' => $this->reason,
            'message' => "Changes required on your advertisement: {$this->reason}",
        ];
    }
}