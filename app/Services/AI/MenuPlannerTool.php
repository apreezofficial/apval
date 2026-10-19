<?php
declare(strict_types=1);
namespace App\Services\AI;

class MenuPlannerTool {
    // Nigerian food database with approximate costs (NGN per serving)
    private const MEAL_DATABASE = [
        'breakfast' => [
            ['name' => 'Bread and Eggs', 'cost' => 800, 'category' => 'breakfast'],
            ['name' => 'Akara and Pap', 'cost' => 600, 'category' => 'breakfast'],
            ['name' => 'Moi-Moi and Pap', 'cost' => 700, 'category' => 'breakfast'],
            ['name' => 'Oats with Milk', 'cost' => 500, 'category' => 'breakfast'],
        ],
        'lunch' => [
            ['name' => 'Jollof Rice with Chicken', 'cost' => 1500, 'category' => 'lunch'],
            ['name' => 'Fried Rice with Fish', 'cost' => 1400, 'category' => 'lunch'],
            ['name' => 'Beans and Plantain', 'cost' => 900, 'category' => 'lunch'],
            ['name' => 'Amala with Egusi', 'cost' => 1200, 'category' => 'lunch'],
            ['name' => 'Rice and Stew', 'cost' => 1100, 'category' => 'lunch'],
        ],
        'dinner' => [
            ['name' => 'Pounded Yam with Egusi', 'cost' => 1600, 'category' => 'dinner'],
            ['name' => 'Semovita with Vegetable Soup', 'cost' => 1300, 'category' => 'dinner'],
            ['name' => 'Spaghetti with Sauce', 'cost' => 1000, 'category' => 'dinner'],
            ['name' => 'Yam Porridge', 'cost' => 900, 'category' => 'dinner'],
            ['name' => 'Pepper Soup with Fish', 'cost' => 1400, 'category' => 'dinner'],
        ],
    ];

    public function generateMealPlan(array $params): array {
        $people = (int) ($params['people'] ?? 4);
        $adults = (int) ($params['adults'] ?? 2);
        $children = (int) ($params['children'] ?? $people - $adults);
        $days = (int) ($params['days'] ?? 7);
        $budget = (float) ($params['budget'] ?? 50000);
        $currency = $params['currency'] ?? 'NGN';

        $mealPlan = [];
        $totalCost = 0;
        $ingredientTotals = [];

        for ($day = 1; $day <= $days; $day++) {
            $dayPlan = ['day' => $day, 'meals' => []];
            foreach (['breakfast', 'lunch', 'dinner'] as $mealType) {
                $options = self::MEAL_DATABASE[$mealType];
                $selected = $options[array_rand($options)];
                $costPerPerson = $selected['cost'];
                $totalMealCost = $costPerPerson * $people;
                $totalCost += $totalMealCost;

                $dayPlan['meals'][] = [
                    'meal_type' => $mealType,
                    'name' => $selected['name'],
                    'servings' => $people,
                    'cost_per_person' => $costPerPerson,
                    'total_cost' => $totalMealCost,
                ];

                // Aggregate ingredients (simplified)
                $ingredientTotals[$selected['name']] = ($ingredientTotals[$selected['name']] ?? 0) + $people;
            }
            $mealPlan[] = $dayPlan;
        }

        $budgetStatus = $totalCost <= $budget ? 'within_budget' : 'over_budget';
        $overage = max(0, $totalCost - $budget);

        return [
            'people' => $people,
            'adults' => $adults,
            'children' => $children,
            'days' => $days,
            'budget' => $budget,
            'currency' => $currency,
            'meal_plan' => $mealPlan,
            'total_estimated_cost' => $totalCost,
            'budget_status' => $budgetStatus,
            'overage' => $overage,
            'ingredient_summary' => $ingredientTotals,
            'disclaimer' => 'Costs are estimates based on average Nigerian market prices. Actual prices may vary by location and vendor.',
        ];
    }
}