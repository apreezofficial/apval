<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Wallet>
 */
class WalletFactory extends Factory
{
    protected $model = Wallet::class;

    public function definition(): array
    {
        return [
            'user_id'        => User::factory(),
            'currency'       => 'NGN',
            'balance'        => '0.00000000',
            'frozen_balance' => '0.00000000',
            'agro_points'    => '0.0000',
            'status'         => 'active',
        ];
    }

    public function withBalance(string $amount): static
    {
        return $this->state(['balance' => $amount]);
    }

    public function frozen(): static
    {
        return $this->state(['status' => 'frozen']);
    }
}
