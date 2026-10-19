<?php

declare(strict_types=1);

namespace App\Enums\Ads;

use App\Enums\Concerns\EnumHelpers;

enum RefundStatus: string
{
    use EnumHelpers;

    case Pending = 'pending';
    case Processed = 'processed';
    case Failed = 'failed';
}