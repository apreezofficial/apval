<?php

namespace Tests\Feature\Wallet;

use App\Models\User;
use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class UserTransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_get_transaction_history()
    {
        $user = User::factory()->create();
        
        $wallet = Wallet::create([
            'user_id' => $user->id,
            'currency' => 'NGN',
            'balance' => 1000.00,
            'status' => 'active',
        ]);

        // Create a few transactions
        Transaction::create([
            'id' => (string) Str::uuid(),
            'reference' => 'TRX_1',
            'idempotency_key' => 'IK_1',
            'type' => 'funding',
            'status' => 'success',
            'initiated_by' => $user->id,
            'to_wallet_id' => $wallet->id,
            'currency' => 'NGN',
            'amount' => 500.00,
            'created_at' => now()->subMinutes(10),
        ]);

        Transaction::create([
            'id' => (string) Str::uuid(),
            'reference' => 'TRX_2',
            'idempotency_key' => 'IK_2',
            'type' => 'transfer',
            'status' => 'pending',
            'initiated_by' => $user->id,
            'from_wallet_id' => $wallet->id,
            'currency' => 'NGN',
            'amount' => 100.00,
            'created_at' => now()->subMinutes(5),
        ]);

        $response = $this->actingAs($user)->getJson('/api/walletManager/transactions');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'reference',
                            'type',
                            'status',
                            'amount',
                            'currency',
                        ]
                    ]
                ]
            ]);

        $this->assertCount(2, $response->json('data.data'));
        // Verify order is latest first
        $this->assertEquals('TRX_2', $response->json('data.data.0.reference'));
    }

    public function test_unauthenticated_user_cannot_get_transaction_history()
    {
        $response = $this->getJson('/api/walletManager/transactions');

        $response->assertStatus(401);
    }
}
