<?php

declare(strict_types=1);

namespace App\Enums\Ads;

use App\Enums\Concerns\EnumHelpers;

enum PaymentMethod: string
{
    use EnumHelpers;

    case AdvertisingWallet = 'advertising_wallet';
    case AgreonpayWallet = 'agreonpay_wallet';
    case BankTransfer = 'bank_transfer';
    case Card = 'card';
    case Gateway = 'gateway';
}