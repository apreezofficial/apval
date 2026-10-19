<?php

declare(strict_types=1);

namespace App\Enums\Ads;

use App\Enums\Concerns\EnumHelpers;

enum AdvertiserStatus: string
{
    use EnumHelpers;

    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Suspended = 'suspended';
}