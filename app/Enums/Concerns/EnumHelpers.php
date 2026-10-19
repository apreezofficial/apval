<?php

declare(strict_types=1);

namespace App\Enums\Concerns;

use Illuminate\Support\Str;

trait EnumHelpers
{
    public static function values(): array
    {
        return array_map(fn (self $case) => $case->value, self::cases());
    }

    public static function options(): array
    {
        return array_map(
            fn (self $case) => [
                'value' => $case->value,
                'label' => Str::of($case->value)->replace('_', ' ')->title()->toString(),
            ],
            self::cases()
        );
    }
}