<?php

namespace Database\Factories\Agent;

use App\Models\Agent\AgentEarning;
use App\Models\Agent\AgentProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Agent\AgentEarning>
 */
class AgentEarningFactory extends Factory
{
    protected $model = AgentEarning::class;

    public function definition(): array
    {
        return [
            'agent_id' => AgentProfile::factory(),
            'earning_type' => AgentEarning::TYPE_ACTIVATION,
            'source_type' => null,
            'source_id' => null,
            'amount' => fake()->randomFloat(2, 200, 1000),
            'status' => AgentEarning::STATUS_PENDING,
            'description' => 'Commission for customer activation',
            'paid_at' => null,
        ];
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AgentEarning::STATUS_PAID,
            'paid_at' => now(),
        ]);
    }
}