<?php
declare(strict_types=1);
namespace App\Services\AI;
use App\Models\ShoppingList;
use Illuminate\Support\Str;

class ShoppingListTool {
    // Map common Nigerian meals to their core ingredients
    private const RECIPE_INGREDIENTS = [
        'Jollof Rice with Chicken' => [
            ['name' => 'Rice', 'category' => 'GRAINS', 'qty_per_person' => 0.25, 'unit' => 'kg', 'price_per_unit' => 1200],
            ['name' => 'Chicken', 'category' => 'PROTEIN', 'qty_per_person' => 0.3, 'unit' => 'kg', 'price_per_unit' => 3500],
            ['name' => 'Tomato Paste', 'category' => 'VEGETABLES', 'qty_per_person' => 0.1, 'unit' => 'kg', 'price_per_unit' => 800],
            ['name' => 'Onion', 'category' => 'VEGETABLES', 'qty_per_person' => 0.1, 'unit' => 'kg', 'price_per_unit' => 600],
            ['name' => 'Pepper', 'category' => 'VEGETABLES', 'qty_per_person' => 0.05, 'unit' => 'kg', 'price_per_unit' => 1000],
        ],
        'Beans and Plantain' => [
            ['name' => 'Beans', 'category' => 'GRAINS', 'qty_per_person' => 0.2, 'unit' => 'kg', 'price_per_unit' => 1500],
            ['name' => 'Plantain', 'category' => 'VEGETABLES', 'qty_per_person' => 0.3, 'unit' => 'kg', 'price_per_unit' => 800],
            ['name' => 'Palm Oil', 'category' => 'OILS', 'qty_per_person' => 0.05, 'unit' => 'L', 'price_per_unit' => 2000],
        ],
        'Amala with Egusi' => [
            ['name' => 'Yam Flour', 'category' => 'GRAINS', 'qty_per_person' => 0.3, 'unit' => 'kg', 'price_per_unit' => 1800],
            ['name' => 'Egusi Seeds', 'category' => 'PROTEIN', 'qty_per_person' => 0.1, 'unit' => 'kg', 'price_per_unit' => 4000],
            ['name' => 'Spinach', 'category' => 'VEGETABLES', 'qty_per_person' => 0.1, 'unit' => 'bunch', 'price_per_unit' => 500],
        ],
    ];

    public function createShoppingList(string $userId, array $mealPlan): array {
        $itemsByCategory = [];
        $totalCost = 0;

        // Aggregate ingredients across all meals
        foreach ($mealPlan as $dayPlan) {
            foreach ($dayPlan['meals'] ?? [] as $meal) {
                $recipeName = $meal['name'];
                $servings = $meal['servings'] ?? 1;

                if (isset(self::RECIPE_INGREDIENTS[$recipeName])) {
                    foreach (self::RECIPE_INGREDIENTS[$recipeName] as $ingredient) {
                        $key = $ingredient['name'];
                        $qty = $ingredient['qty_per_person'] * $servings;
                        $cost = $qty * $ingredient['price_per_unit'];
                        $totalCost += $cost;

                        if (!isset($itemsByCategory[$ingredient['category']][$key])) {
                            $itemsByCategory[$ingredient['category']][$key] = [
                                'name' => $ingredient['name'],
                                'category' => $ingredient['category'],
                                'quantity' => 0,
                                'unit' => $ingredient['unit'],
                                'estimated_price' => 0,
                            ];
                        }
                        $itemsByCategory[$ingredient['category']][$key]['quantity'] += $qty;
                        $itemsByCategory[$ingredient['category']][$key]['estimated_price'] += $cost;
                    }
                }
            }
        }

        // Flatten items
        $flatItems = [];
        foreach ($itemsByCategory as $category => $items) {
            foreach ($items as $item) {
                $flatItems[] = $item;
            }
        }

        // Save to database
        $shoppingList = ShoppingList::create([
            'id' => (string) Str::uuid(),
            'user_id' => $userId,
            'title' => 'Agrinova Shopping List',
            'items' => $flatItems,
            'total_estimated_cost' => $totalCost,
            'currency' => 'NGN',
        ]);

        return [
            'shopping_list_id' => $shoppingList->id,
            'total_items' => count($flatItems),
            'total_estimated_cost' => $totalCost,
            'items_by_category' => $itemsByCategory,
            'message' => 'Shopping list generated. You can now search for these items on AgreonPay marketplace.',
        ];
    }
}