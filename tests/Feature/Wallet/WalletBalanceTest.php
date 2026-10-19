<?php

namespace Tests\Feature\Wallet;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WalletBalanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_get_wallet_balances()
    {
        $user = User::factory()->create();
        
        // Create NGN wallet
        Wallet::create([
            'user_id' => $user->id,
            'currency' => 'NGN',
            'balance' => 5000.00,
            'status' => 'active',
        ]);

        // Create USDT wallet
        Wallet::create([
            'user_id' => $user->id,
            'currency' => 'USDT',
            'balance' => 100.50,
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->getJson('/api/walletManager/balances');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    [
                        'currency' => 'NGN',
                        'balance' => '5000.00',
                    ],
                    [
                        'currency' => 'USDT',
                        'balance' => '100.50',
                    ],
                ]
            ]);
    }

    public function test_unauthenticated_user_cannot_get_wallet_balances()
    {
        $response = $this->getJson('/api/walletManager/balances');

        $response->assertStatus(401);
    }
}
