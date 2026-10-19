<?php

declare(strict_types=1);

namespace App\Services\Ads;

use App\Enums\Ads\PricingModel;
use App\Exceptions\Ads\InvalidBudgetException;
use App\Models\Ads\AdPackage;
use Carbon\CarbonInterface;

class AdPricingService
{
    public function __construct(protected AdSettingService $settings)
    {
    }

    /**
     * Resolve daily/total budget for a campaign based on its pricing model.
     *
     * @return array{daily_budget: float|null, total_budget: float, days: int}
     */
    public function resolveBudget(
        PricingModel $pricingModel,
        ?string $packageId,
        ?float $dailyBudget,
        CarbonInterface $start,
        CarbonInterface $end,
    ): array {
        $days = $this->calculateDays($start, $end);

        if ($pricingModel === PricingModel::FixedPackage) {
            $package = AdPackage::query()
                ->where('id', $packageId)
                ->where('is_active', true)
                ->firstOrFail();

            return [
                'daily_budget' => null,
                'total_budget' => (float) $package->price,
                'days' => (int) ceil($package->duration_hours / 24),
            ];
        }

        if ($dailyBudget === null || $dailyBudget <= 0) {
            throw new InvalidBudgetException('Daily budget is required for this pricing model.');
        }

        $totalBudget = round($dailyBudget * $days, 2);

        $this->validateBudget($dailyBudget, $totalBudget);

        return [
            'daily_budget' => $dailyBudget,
            'total_budget' => $totalBudget,
            'days' => $days,
        ];
    }

    public function validateBudget(float $dailyBudget, float $totalBudget): void
    {
        $min = (float) $this->settings->get('min_budget');
        $max = (float) $this->settings->get('max_budget');
        $dailyLimit = (float) $this->settings->get('daily_spending_limit');
        $campaignLimit = (float) $this->settings->get('campaign_spending_limit');

        if ($totalBudget < $min) {
            throw new InvalidBudgetException("Total budget must be at least ₦{$min}.");
        }

        if ($totalBudget > $max) {
            throw new InvalidBudgetException("Total budget cannot exceed ₦{$max}.");
        }

        if ($dailyBudget > $dailyLimit) {
            throw new InvalidBudgetException("Daily budget cannot exceed ₦{$dailyLimit}.");
        }

        if ($totalBudget > $campaignLimit) {
            throw new InvalidBudgetException("Campaign budget cannot exceed ₦{$campaignLimit}.");
        }
    }

    public function estimateReach(float $totalBudget): int
    {
        // Heuristic: ₦1 ≈ 5 impressions, reach ≈ 60% of impressions.
        // Replace with a data-driven model once historical campaign data exists.
        $impressions = $totalBudget * 5;

        return (int) round($impressions * 0.6);
    }

    protected function calculateDays(CarbonInterface $start, CarbonInterface $end): int
    {
        $hours = $start->diffInHours($end);

        return max(1, (int) ceil($hours / 24));
    }
}