<?php

declare(strict_types=1);

namespace App\Services\Ads;

use App\Enums\Ads\AdvertisementStatus;
use App\Enums\Ads\ReviewAction;
use App\Exceptions\Ads\InvalidAdvertisementStateException;
use App\Models\Ads\Advertisement;
use App\Models\Ads\AdvertisementReview;
use App\Models\User;
use App\Notifications\Ads\AdApprovedNotification;
use App\Notifications\Ads\AdChangesRequiredNotification;
use App\Notifications\Ads\AdRejectedNotification;
use Illuminate\Support\Facades\DB;

class AdWorkflowService
{
    public function submitForReview(Advertisement $advertisement): Advertisement
    {
        if ($advertisement->status !== AdvertisementStatus::PaymentSuccessful) {
            throw new InvalidAdvertisementStateException('Advertisement must be paid before submission for review.');
        }

        $advertisement->update(['status' => AdvertisementStatus::PendingReview]);
        $advertisement->campaign?->update(['status' => AdvertisementStatus::PendingReview]);

        return $advertisement->refresh();
    }

    public function approve(Advertisement $advertisement, User $admin): Advertisement
    {
        return DB::transaction(function () use ($advertisement, $admin) {
            $campaign = $advertisement->campaign;

            $status = $campaign?->start_date?->isFuture()
                ? AdvertisementStatus::Scheduled
                : AdvertisementStatus::Active;

            $advertisement->update([
                'status' => AdvertisementStatus::Approved,
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
            ]);

            $campaign?->update(['status' => $status]);

            AdvertisementReview::create([
                'campaign_id' => $campaign?->id,
                'advertisement_id' => $advertisement->id,
                'admin_id' => $admin->id,
                'action' => ReviewAction::Approved,
                'created_at' => now(),
            ]);

            $advertisement->user->notify(new AdApprovedNotification($advertisement));

            return $advertisement->refresh();
        });
    }

    public function reject(Advertisement $advertisement, User $admin, string $reason): Advertisement
    {
        return DB::transaction(function () use ($advertisement, $admin, $reason) {
            $advertisement->update([
                'status' => AdvertisementStatus::Rejected,
                'rejection_reason' => $reason,
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
            ]);

            $advertisement->campaign?->update(['status' => AdvertisementStatus::Rejected]);

            AdvertisementReview::create([
                'campaign_id' => $advertisement->campaign?->id,
                'advertisement_id' => $advertisement->id,
                'admin_id' => $admin->id,
                'action' => ReviewAction::Rejected,
                'reason' => $reason,
                'created_at' => now(),
            ]);

            $advertisement->user->notify(new AdRejectedNotification($advertisement, $reason));

            return $advertisement->refresh();
        });
    }

    public function requestChanges(Advertisement $advertisement, User $admin, string $reason): Advertisement
    {
        return DB::transaction(function () use ($advertisement, $admin, $reason) {
            $advertisement->update([
                'status' => AdvertisementStatus::ChangesRequired,
                'admin_notes' => $reason,
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
            ]);

            $advertisement->campaign?->update(['status' => AdvertisementStatus::ChangesRequired]);

            AdvertisementReview::create([
                'campaign_id' => $advertisement->campaign?->id,
                'advertisement_id' => $advertisement->id,
                'admin_id' => $admin->id,
                'action' => ReviewAction::ChangesRequested,
                'reason' => $reason,
                'created_at' => now(),
            ]);

            $advertisement->user->notify(new AdChangesRequiredNotification($advertisement, $reason));

            return $advertisement->refresh();
        });
    }

    public function pause(Advertisement $advertisement): Advertisement
    {
        $this->assertStatusIn($advertisement, [AdvertisementStatus::Active, AdvertisementStatus::Scheduled]);

        $advertisement->update(['status' => AdvertisementStatus::Paused]);
        $advertisement->campaign?->update(['status' => AdvertisementStatus::Paused, 'paused_at' => now()]);

        return $advertisement->refresh();
    }

    public function resume(Advertisement $advertisement): Advertisement
    {
        $this->assertStatusIn($advertisement, [AdvertisementStatus::Paused]);

        $status = $advertisement->campaign?->start_date?->isFuture()
            ? AdvertisementStatus::Scheduled
            : AdvertisementStatus::Active;

        $advertisement->update(['status' => $status]);
        $advertisement->campaign?->update(['status' => $status, 'paused_at' => null]);

        return $advertisement->refresh();
    }

    public function cancel(Advertisement $advertisement): Advertisement
    {
        $this->assertStatusNotIn($advertisement, [
            AdvertisementStatus::Completed,
            AdvertisementStatus::Cancelled,
            AdvertisementStatus::Refunded,
        ]);

        $advertisement->update(['status' => AdvertisementStatus::Cancelled]);
        $advertisement->campaign?->update(['status' => AdvertisementStatus::Cancelled]);

        return $advertisement->refresh();
    }

    public function suspend(Advertisement $advertisement, User $admin, ?string $reason = null): Advertisement
    {
        $advertisement->update(['status' => AdvertisementStatus::Suspended, 'admin_notes' => $reason]);
        $advertisement->campaign?->update(['status' => AdvertisementStatus::Suspended]);

        AdvertisementReview::create([
            'campaign_id' => $advertisement->campaign?->id,
            'advertisement_id' => $advertisement->id,
            'admin_id' => $admin->id,
            'action' => ReviewAction::Suspended,
            'reason' => $reason,
            'created_at' => now(),
        ]);

        return $advertisement->refresh();
    }

    /**
     * @param  array<AdvertisementStatus>  $allowed
     */
    protected function assertStatusIn(Advertisement $advertisement, array $allowed): void
    {
        if (! in_array($advertisement->status, $allowed, true)) {
            $allowedLabels = implode(', ', array_map(fn (AdvertisementStatus $s) => $s->value, $allowed));

            throw new InvalidAdvertisementStateException("Action requires status to be one of: {$allowedLabels}.");
        }
    }

    /**
     * @param  array<AdvertisementStatus>  $disallowed
     */
    protected function assertStatusNotIn(Advertisement $advertisement, array $disallowed): void
    {
        if (in_array($advertisement->status, $disallowed, true)) {
            throw new InvalidAdvertisementStateException('Action cannot be performed in the current status.');
        }
    }
}