<?php

declare(strict_types=1);

namespace App\Enums\Ads;

use App\Enums\Concerns\EnumHelpers;

enum ConversionType: string
{
    use EnumHelpers;

    case ProductView = 'product_view';
    case StoreVisit = 'store_visit';
    case Order = 'order';
    case Purchase = 'purchase';
    case OfferClaim = 'offer_claim';
    case Registration = 'registration';
}