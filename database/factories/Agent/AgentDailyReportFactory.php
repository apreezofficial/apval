<?php

namespace Database\Factories\Agent;

use App\Models\Agent\AgentDailyReport;
use App\Models\Agent\AgentProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Agent\AgentDailyReport>
 */
class AgentDailyReportFactory extends Factory
{
    protected $model = AgentDailyReport::class;

    public function definition(): array
    {
        $registrations = fake()->numberBetween(5, 10);
        $completed = fake()->numberBetween(3, $registrations);

        return [
            'agent_id' => AgentProfile::factory(),
            'report_date' => fake()->dateTimeBetween('-30 days', 'now')->format('Y-m-d'),
            'territory' => fake()->randomElement(['Lagos - Mile 12', 'Ogun - Ota', 'Kano - Dawanau']),

            'calls_count' => fake()->numberBetween(30, 50),
            'market_visits_count' => fake()->numberBetween(2, 6),
            'new_prospects' => fake()->numberBetween(10, 15),
            'qualified_prospects' => fake()->numberBetween(8, 15),

            'registrations' => $registrations,
            'completed_registrations' => $completed,
            'pending_registrations' => $registrations - $completed,
            'activated_users' => fake()->numberBetween(3, 5),

            'vendors_with_products' => fake()->numberBetween(2, 5),
            'products_uploaded_count' => fake()->numberBetween(6, 15),

            'first_time_transactions' => fake()->numberBetween(0, 3),
            'repeat_transactions' => fake()->numberBetween(0, 2),

            'customers_followed_up' => fake()->numberBetween(3, 10),
            'customers_requiring_followup' => fake()->numberBetween(0, 5),

            'challenges' => fake()->optional()->sentence(),
            'remarks' => fake()->optional()->sentence(),
        ];
    }
}