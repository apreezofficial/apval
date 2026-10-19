<?php

namespace Database\Factories\Agent;

use App\Models\Agent\ActivationPayment;
use App\Models\Agent\AgentProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Agent\ActivationPayment>
 */
class ActivationPaymentFactory extends Factory
{
    protected $model = ActivationPayment::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'agent_id' => AgentProfile::factory(),
            'lead_id' => null,
            'amount' => ActivationPayment::FIXED_AMOUNT,
            'currency' => 'NGN',
            'reference' => 'ACT-' . strtoupper(Str::random(10)),
            'provider' => 'monnify',
            'provider_reference' => null,
            'status' => ActivationPayment::STATUS_PENDING,
            'receipt_number' => null,
            'paid_at' => null,
            'meta' => null,
        ];
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ActivationPayment::STATUS_PAID,
            'provider_reference' => 'MNFY-' . strtoupper(Str::random(12)),
            'receipt_number' => 'RCPT-' . strtoupper(Str::random(8)),
            'paid_at' => now(),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ActivationPayment::STATUS_FAILED,
        ]);
    }
}