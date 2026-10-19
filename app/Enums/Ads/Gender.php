<?php

declare(strict_types=1);

namespace App\Enums\Ads;

use App\Enums\Concerns\EnumHelpers;

enum Gender: string
{
    use EnumHelpers;

    case All = 'all';
    case Male = 'male';
    case Female = 'female';
}