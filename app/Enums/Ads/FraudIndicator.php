<?php

declare(strict_types=1);

namespace App\Enums\Ads;

use App\Enums\Concerns\EnumHelpers;

enum FraudIndicator: string
{
    use EnumHelpers;

    case ArtificialClicks = 'artificial_clicks';
    case RepeatedClicks = 'repeated_clicks';
    case BotTraffic = 'bot_traffic';
    case FakeImpressions = 'fake_impressions';
    case PaymentFraud = 'payment_fraud';
    case SuspiciousTraffic = 'suspicious_traffic';
    case MisleadingAd = 'misleading_ad';
    case MaliciousLink = 'malicious_link';
}