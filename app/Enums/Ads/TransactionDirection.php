<?php

declare(strict_types=1);

namespace App\Enums\Ads;

use App\Enums\Concerns\EnumHelpers;

enum TransactionDirection: string
{
    use EnumHelpers;

    case Credit = 'credit';
    case Debit = 'debit';
}