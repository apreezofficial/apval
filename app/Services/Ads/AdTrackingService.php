<?php

declare(strict_types=1);

namespace App\Services\Ads;

use App\Enums\Ads\ConversionType;
use App\Enums\Ads\PricingModel;
use App\Models\Ads\AdvertisementCampaign;
use App\Models\Ads\AdvertisementClick;
use App\Models\Ads\AdvertisementConversion;
use App\Models\Ads\AdvertisementImpression;
use App\Models\Ads\AdvertisementReport;
use Illuminate\Http\Request;

class AdTrackingService
{
    public function __construct(protected AdBudgetSpendService $budgetSpendService)
    {
    }

    public function recordImpression(
        AdvertisementCampaign $campaign,
        Request $request,
        ?string $placementId = null,
    ): AdvertisementImpression {
        $impression = AdvertisementImpression::create([
            'campaign_id' => $campaign->id,
            'advertisement_id' => $campaign->advertisement_id,
            'placement_id' => $placementId,
            'user_id' => $request->user()?->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);

        $this->bumpDailyReport($campaign->id, 'impressions');

        if ($campaign->pricing_model === PricingModel::Cpm) {
            $this->budgetSpendService->deductImpressionBatchCost($campaign, 1);
        }

        return $impression;
    }

    public function recordClick(
        AdvertisementCampaign $campaign,
        Request $request,
        ?string $placementId = null,
    ): AdvertisementClick {
        $click = AdvertisementClick::create([
            'campaign_id' => $campaign->id,
            'advertisement_id' => $campaign->advertisement_id,
            'placement_id' => $placementId,
            'user_id' => $request->user()?->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);

        $this->bumpDailyReport($campaign->id, 'clicks');

        if ($campaign->pricing_model === PricingModel::Cpc) {
            $this->budgetSpendService->deductClickCost($campaign);
        }

        return $click;
    }

    public function recordConversion(
        AdvertisementCampaign $campaign,
        ConversionType $type,
        ?string $userId = null,
        ?string $referenceId = null,
        ?float $value = null,
    ): AdvertisementConversion {
        $conversion = AdvertisementConversion::create([
            'campaign_id' => $campaign->id,
            'advertisement_id' => $campaign->advertisement_id,
            'user_id' => $userId,
            'conversion_type' => $type,
            'reference_id' => $referenceId,
            'value' => $value,
            'created_at' => now(),
        ]);

        $this->bumpDailyReport($campaign->id, 'conversions');

        return $conversion;
    }

    public function calculateCtr(int $clicks, int $impressions): float
    {
        if ($impressions === 0) {
            return 0.0;
        }

        return round(($clicks / $impressions) * 100, 2);
    }

    protected function bumpDailyReport(string $campaignId, string $column): void
    {
        $report = AdvertisementReport::query()->firstOrCreate([
            'campaign_id' => $campaignId,
            'date' => now()->toDateString(),
        ]);

        $report->increment($column);
    }
}