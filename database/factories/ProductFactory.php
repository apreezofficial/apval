<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'vendor_id' => User::factory(),
            'name' => fake()->words(3, true),
            'description' => fake()->paragraph(),
            'category' => fake()->randomElement(['Seeds', 'Fertilizers', 'Tools', 'Fruits', 'Vegetables']),
            'price' => fake()->randomFloat(2, 10, 1000),
            'unit' => fake()->randomElement(['kg', 'unit', 'bag', 'litre']),
            'stock_quantity' => fake()->randomFloat(2, 0, 100),
            'min_order_quantity' => 1,
            'status' => 'active',
        ];
    }
}
