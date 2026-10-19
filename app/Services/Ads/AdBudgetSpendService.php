<?php

declare(strict_types=1);

namespace App\Services\Ads;

use App\Enums\Ads\AdvertisementStatus;
use App\Enums\Ads\PricingModel;
use App\Models\Ads\AdPricingModel;
use App\Models\Ads\AdvertisementCampaign;
use Illuminate\Support\Facades\DB;

class AdBudgetSpendService
{
    public function __construct(protected AdSettingService $settings)
    {
    }

    public function deductClickCost(AdvertisementCampaign $campaign): void
    {
        $rate = $this->resolveRate(PricingModel::Cpc, 'default_cpc_rate');

        $this->spend($campaign, $rate);
    }

    public function deductImpressionBatchCost(AdvertisementCampaign $campaign, int $impressions): void
    {
        $rate = $this->resolveRate(PricingModel::Cpm, 'default_cpm_rate');
        $cost = ($impressions / 1000) * $rate;

        $this->spend($campaign, $cost);
    }

    protected function resolveRate(PricingModel $model, string $fallbackSettingKey): float
    {
        $rate = AdPricingModel::query()
            ->where('pricing_model', $model->value)
            ->where('is_active', true)
            ->value('rate');

        return $rate !== null
            ? (float) $rate
            : (float) $this->settings->get($fallbackSettingKey);
    }

    protected function spend(AdvertisementCampaign $campaign, float $cost): void
    {
        DB::transaction(function () use ($campaign, $cost): void {
            $locked = AdvertisementCampaign::query()->whereKey($campaign->id)->lockForUpdate()->firstOrFail();

            $locked->amount_spent = bcadd((string) $locked->amount_spent, (string) $cost, 2);

            if (bccomp((string) $locked->amount_spent, (string) $locked->total_budget, 2) >= 0) {
                $locked->amount_spent = $locked->total_budget;
                $locked->status = AdvertisementStatus::Completed;
                $locked->advertisement?->update(['status' => AdvertisementStatus::Completed]);
            }

            $locked->save();
        });
    }
}