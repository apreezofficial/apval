<?php

declare(strict_types=1);

namespace App\Enums\Ads;

use App\Enums\Concerns\EnumHelpers;

enum WalletTransactionType: string
{
    use EnumHelpers;

    case WalletFunding = 'wallet_funding';
    case CampaignPayment = 'campaign_payment';
    case CampaignDeduction = 'campaign_deduction';
    case Refund = 'refund';
    case PromotionalCredit = 'promotional_credit';
    case BonusCredit = 'bonus_credit';
    case Adjustment = 'adjustment';
}