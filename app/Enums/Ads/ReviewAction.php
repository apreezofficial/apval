<?php

declare(strict_types=1);

namespace App\Enums\Ads;

use App\Enums\Concerns\EnumHelpers;

enum ReviewAction: string
{
    use EnumHelpers;

    case Approved = 'approved';
    case Rejected = 'rejected';
    case ChangesRequested = 'changes_requested';
    case Paused = 'paused';
    case Resumed = 'resumed';
    case Suspended = 'suspended';
    case Cancelled = 'cancelled';
}