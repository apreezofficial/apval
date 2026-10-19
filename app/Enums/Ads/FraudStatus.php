<?php

declare(strict_types=1);

namespace App\Enums\Ads;

use App\Enums\Concerns\EnumHelpers;

enum FraudStatus: string
{
    use EnumHelpers;

    case Flagged = 'flagged';
    case Reviewed = 'reviewed';
    case Cleared = 'cleared';
    case Confirmed = 'confirmed';
}