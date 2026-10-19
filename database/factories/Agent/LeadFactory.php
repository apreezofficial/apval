<?php

namespace Database\Factories\Agent;

use App\Models\Agent\AgentProfile;
use App\Models\Agent\Lead;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Agent\Lead>
 */
class LeadFactory extends Factory
{
    protected $model = Lead::class;

    public function definition(): array
    {
        return [
            'agent_id' => AgentProfile::factory(),
            'full_name' => fake()->name(),
            'phone' => fake()->unique()->phoneNumber(),
            'business_name' => fake()->optional()->company(),
            'category' => fake()->randomElement(['farmer', 'vendor', 'agro_business']),
            'sub_category' => fake()->randomElement([
                'Crop farmer', 'Poultry farmer', 'Market trader',
                'Wholesaler', 'Processor', 'Cooperative',
            ]),
            'location' => fake()->city(),
            'state' => fake()->randomElement(['Lagos', 'Ogun', 'Oyo', 'Kano', 'Rivers']),
            'classification' => Lead::CLASSIFICATION_NEW,
            'source' => fake()->randomElement(['market_visit', 'call', 'referral', 'walk_in']),
            'needs_discovery' => [
                'sells' => fake()->randomElement(['Yam', 'Maize', 'Tomatoes', 'Poultry', 'Fish']),
                'wholesale_or_retail' => fake()->randomElement(['wholesale', 'retail', 'both']),
                'delivers' => fake()->boolean(),
                'wants_wider_reach' => fake()->boolean(80),
            ],
            'notes' => fake()->optional()->sentence(),
            'linked_user_id' => null,
            'next_follow_up_at' => now()->addDay(),
            'registered_at' => null,
            'activated_at' => null,
        ];
    }

    public function hot(): static
    {
        return $this->state(fn (array $attributes) => [
            'classification' => Lead::CLASSIFICATION_HOT,
            'next_follow_up_at' => now(),
        ]);
    }

    public function warm(): static
    {
        return $this->state(fn (array $attributes) => [
            'classification' => Lead::CLASSIFICATION_WARM,
            'next_follow_up_at' => now()->addHours(36),
        ]);
    }

    public function cold(): static
    {
        return $this->state(fn (array $attributes) => [
            'classification' => Lead::CLASSIFICATION_COLD,
            'next_follow_up_at' => now()->addWeeks(2),
        ]);
    }

    public function activated(): static
    {
        return $this->state(fn (array $attributes) => [
            'classification' => Lead::CLASSIFICATION_ACTIVATED,
            'registered_at' => now()->subDays(2),
            'activated_at' => now(),
        ]);
    }
}