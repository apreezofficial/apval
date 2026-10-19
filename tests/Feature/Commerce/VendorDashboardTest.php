<?php

namespace Tests\Feature\Commerce;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class VendorDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_vendor_can_get_dashboard_overview()
    {
        $vendor = User::factory()->create();
        $buyer = User::factory()->create();
        $product = Product::factory()->create(['vendor_id' => $vendor->id]);

        // Create a completed order
        $order1 = Order::create([
            'user_id' => $buyer->id,
            'total_amount' => 1000,
            'status' => 'completed',
            'payment_status' => 'paid',
        ]);

        OrderItem::create([
            'order_id' => $order1->id,
            'product_id' => $product->id,
            'vendor_id' => $vendor->id,
            'quantity' => 2,
            'unit_price' => 500,
            'total_price' => 1000,
        ]);

        // Create a returned order
        $order2 = Order::create([
            'user_id' => $buyer->id,
            'total_amount' => 500,
            'status' => 'returned',
            'payment_status' => 'paid',
        ]);

        OrderItem::create([
            'order_id' => $order2->id,
            'product_id' => $product->id,
            'vendor_id' => $vendor->id,
            'quantity' => 1,
            'unit_price' => 500,
            'total_price' => 500,
        ]);

        $response = $this->actingAs($vendor)->getJson('/api/commerce/vendor/dashboard/overview');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'overview' => [
                        'sales' => '3.00',
                        'revenue' => '1000.00',
                        'returns' => '1.00',
                        'complete_orders' => '1',
                        'total_customers' => 1,
                    ]
                ]
            ]);
    }

    public function test_vendor_can_get_dashboard_transactions()
    {
        $vendor = User::factory()->create();
        
        // Ensure vendor has a wallet. If factory already created one, use it.
        $wallet = Wallet::firstOrCreate(
            ['user_id' => $vendor->id],
            ['balance' => 1000, 'currency' => 'NGN', 'status' => 'active']
        );
        
        Transaction::create([
            'id' => \Illuminate\Support\Str::uuid(),
            'reference' => 'TEST_REF_' . uniqid(),
            'idempotency_key' => 'TEST_IK_' . uniqid(),
            'to_wallet_id' => $wallet->id,
            'amount' => 500,
            'type' => 'transfer',
            'status' => 'success',
            'currency' => 'NGN',
            'initiated_by' => $vendor->id,
        ]);

        $response = $this->actingAs($vendor)->getJson('/api/commerce/vendor/dashboard/transactions');

        if ($response->json('data.transactions') === []) {
             // Debug why transactions is empty
             // dd($vendor->wallets()->get()->toArray(), $wallet->id);
        }

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'transactions' => [
                        '*' => [
                            'transaction_id',
                            'amount',
                            'description',
                            'transaction_date',
                            'status',
                            'transaction_type',
                        ]
                    ]
                ]
            ]);
            
        $this->assertCount(1, $response->json('data.transactions'));
    }
}
