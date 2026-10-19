<?php

namespace Database\Factories\Agent;

use App\Models\Agent\AgentProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Agent\AgentProfile>
 */
class AgentProfileFactory extends Factory
{
    protected $model = AgentProfile::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->state(['role' => 'agent']),
            'agent_code' => 'AG-' . strtoupper(Str::random(6)),
            'territory' => fake()->randomElement([
                'Lagos - Mile 12', 'Lagos - Mushin', 'Ogun - Ota',
                'Kano - Dawanau', 'Oyo - Bodija', 'Rivers - Mile 3',
            ]),
            'assigned_manager_id' => null,
            'status' => 'active',
            'onboarded_at' => fake()->dateTimeBetween('-6 months', 'now'),
            'notes' => null,
        ];
    }

    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'suspended',
        ]);
    }

    public function withManager(User $manager): static
    {
        return $this->state(fn (array $attributes) => [
            'assigned_manager_id' => $manager->id,
        ]);
    }
}