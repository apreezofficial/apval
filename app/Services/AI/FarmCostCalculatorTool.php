<?php

declare(strict_types=1);

namespace App\Services\AI;

class FarmCostCalculatorTool
{
    /**
     * Calculate estimated farm production costs.
     * Note: In a full implementation, these rates would come from a `farm_input_costs` database table.
     * For MVP, we use standardized baseline estimates per hectare.
     */
    public function calculate(array $params): array
    {
        $crop = strtolower($params['crop'] ?? 'maize');
        $size = (float) ($params['farm_size_hectares'] ?? 1.0);
        $inputs = $params['inputs'] ?? []; // e.g., ['seeds', 'fertilizer', 'labor', 'pesticides']

        // Baseline costs per hectare (in NGN) - Replace with live DB data in V2
        $baselineCosts = [
            'maize' => ['seeds' => 15000, 'fertilizer' => 45000, 'labor' => 30000, 'pesticides' => 10000, 'misc' => 5000],
            'tomato' => ['seeds' => 25000, 'fertilizer' => 60000, 'labor' => 50000, 'pesticides' => 20000, 'misc' => 10000],
            'rice' => ['seeds' => 20000, 'fertilizer' => 50000, 'labor' => 40000, 'pesticides' => 15000, 'misc' => 8000],
        ];

        $rates = $baselineCosts[$crop] ?? $baselineCosts['maize'];
        
        $totalCost = 0;
        $breakdown = [];

        foreach ($rates as $inputType => $costPerHectare) {
            if (empty($inputs) || in_array($inputType, $inputs)) {
                $lineTotal = $costPerHectare * $size;
                $breakdown[$inputType] = $lineTotal;
                $totalCost += $lineTotal;
            }
        }

        // Estimated yield (kg per hectare) and price per kg (NGN)
        $estimatedYieldPerHectare = $crop === 'maize' ? 2500 : ($crop === 'tomato' ? 15000 : 3000);
        $estimatedPricePerKg = $crop === 'maize' ? 150 : ($crop === 'tomato' ? 80 : 400);
        
        $totalYield = $estimatedYieldPerHectare * $size;
        $estimatedRevenue = $totalYield * $estimatedPricePerKg;
        $grossProfit = $estimatedRevenue - $totalCost;

        return [
            'crop' => ucfirst($crop),
            'farm_size_hectares' => $size,
            'cost_breakdown' => $breakdown,
            'total_production_cost' => $totalCost,
            'cost_per_hectare' => $totalCost / $size,
            'estimated_yield_kg' => $totalYield,
            'estimated_revenue' => $estimatedRevenue,
            'estimated_gross_profit' => $grossProfit,
            'disclaimer' => 'These are estimates based on average market rates. Actual costs may vary based on location, season, and specific vendor prices.'
        ];
    }
}