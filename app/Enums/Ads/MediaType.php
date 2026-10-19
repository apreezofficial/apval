<?php

declare(strict_types=1);

namespace App\Enums\Ads;

use App\Enums\Concerns\EnumHelpers;

enum MediaType: string
{
    use EnumHelpers;

    case Image = 'image';
    case Video = 'video';
}