<?php

declare(strict_types=1);

namespace App\Enums\Ads;

use App\Enums\Concerns\EnumHelpers;

enum AdvertisementType: string
{
    use EnumHelpers;

    case Product = 'product';
    case Vendor = 'vendor';
    case Banner = 'banner';
    case SponsoredPost = 'sponsored_post';
    case SponsoredProduct = 'sponsored_product';
    case Video = 'video';
    case PromotionalOffer = 'promotional_offer';
    case PushNotification = 'push_notification';
    case Other = 'other';
}