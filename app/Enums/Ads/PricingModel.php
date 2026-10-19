<?php

declare(strict_types=1);

namespace App\Enums\Ads;

use App\Enums\Concerns\EnumHelpers;

enum PricingModel: string
{
    use EnumHelpers;

    case FixedPackage = 'fixed_package';
    case Cpm = 'cpm';
    case Cpc = 'cpc';
    case SponsoredListing = 'sponsored_listing';
}