<?php

declare(strict_types=1);

namespace App\Enums\Ads;

use App\Enums\Concerns\EnumHelpers;

enum DestinationType: string
{
    use EnumHelpers;

    case Product = 'product';
    case Vendor = 'vendor';
    case External = 'external';
    case Post = 'post';
}