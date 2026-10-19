<?php

declare(strict_types=1);

namespace Tests\Feature\Commerce;

use App\Models\Product;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SplitBuyTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_can_create_and_contribute_to_split_buy_session(): void
    {
        $creator = User::factory()->create();
        $contributor = User::factory()->create();

        Wallet::create(['user_id' => $creator->id, 'currency' => 'NGN', 'balance' => 50000.00, 'status' => 'active']);
        Wallet::create(['user_id' => $contributor->id, 'currency' => 'NGN', 'balance' => 50000.00, 'status' => 'active']);

        $product = Product::factory()->create(['price' => 10000.00, 'stock_quantity' => 10]);

        // 1. Creator starts Split Buy for 2 units (N20,000 total) with N10,000 initial contribution
        $this->actingAs($creator, 'sanctum');
        $createResponse = $this->postJson('/api/commerce/split-buy', [
            'product_id' => $product->id,
            'quantity' => 2,
            'initial_contribution' => 10000.00,
        ]);

        $createResponse->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'total_amount' => '20000.00',
                    'current_amount' => '10000.00',
                    'status' => 'pending',
                ],
            ]);

        $sessionId = $createResponse->json('data.id');

        // 2. Contributor completes the remaining N10,000 contribution
        $this->actingAs($contributor, 'sanctum');
        $contribResponse = $this->postJson("/api/commerce/split-buy/{$sessionId}/contribute", [
            'amount' => 10000.00,
        ]);

        $contribResponse->assertStatus(200);

        // 3. View session -> Should now be completed
        $showResponse = $this->getJson("/api/commerce/split-buy/{$sessionId}");
        $showResponse->assertStatus(200)
            ->assertJson([
                'data' => [
                    'current_amount' => '20000.00',
                    'status' => 'completed',
                ],
            ]);
    }
}
