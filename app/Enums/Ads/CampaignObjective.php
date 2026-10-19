<?php

declare(strict_types=1);

namespace App\Enums\Ads;

use App\Enums\Concerns\EnumHelpers;

enum CampaignObjective: string
{
    use EnumHelpers;

    case Reach = 'reach';
    case Traffic = 'traffic';
    case Engagement = 'engagement';
    case Sales = 'sales';
    case Awareness = 'awareness';
}