<?php

declare(strict_types=1);

namespace App\Enums\Ads;

use App\Enums\Concerns\EnumHelpers;

enum RefundType: string
{
    use EnumHelpers;

    case Full = 'full';
    case Partial = 'partial';
    case WalletCredit = 'wallet_credit';
}