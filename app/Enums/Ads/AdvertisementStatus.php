<?php

declare(strict_types=1);

namespace App\Enums\Ads;

use App\Enums\Concerns\EnumHelpers;

enum AdvertisementStatus: string
{
    use EnumHelpers;

    case Draft = 'draft';
    case PendingPayment = 'pending_payment';
    case PaymentSuccessful = 'payment_successful';
    case PendingReview = 'pending_review';
    case ChangesRequired = 'changes_required';
    case Approved = 'approved';
    case Scheduled = 'scheduled';
    case Active = 'active';
    case Paused = 'paused';
    case Completed = 'completed';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';
    case Refunded = 'refunded';
    case Suspended = 'suspended';
    case Flagged = 'flagged';
}