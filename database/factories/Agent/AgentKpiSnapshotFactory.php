<?php

namespace Database\Factories\Agent;

use App\Models\Agent\AgentKpiSnapshot;
use App\Models\Agent\AgentProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Agent\AgentKpiSnapshot>
 */
class AgentKpiSnapshotFactory extends Factory
{
    protected $model = AgentKpiSnapshot::class;

    public function definition(): array
    {
        $start = fake()->dateTimeBetween('-8 weeks', '-1 week');

        return [
            'agent_id' => AgentProfile::factory(),
            'period_type' => 'weekly',
            'period_start' => $start->format('Y-m-d'),
            'period_end' => (clone $start)->modify('+6 days')->format('Y-m-d'),

            'prospects_contacted' => fake()->numberBetween(150, 300),
            'qualified_prospects' => fake()->numberBetween(60, 100),
            'registrations' => fake()->numberBetween(25, 50),
            'completed_onboardings' => fake()->numberBetween(15, 30),
            'activated_users' => fake()->numberBetween(10, 25),
            'products_listed' => fake()->numberBetween(30, 90),
            'first_transactions' => fake()->numberBetween(5, 15),
            'transaction_value' => fake()->randomFloat(2, 50000, 500000),
            'follow_ups_completed' => fake()->numberBetween(20, 60),

            'performance_status' => fake()->randomElement(['excellent', 'good', 'average', 'poor']),
        ];
    }
}