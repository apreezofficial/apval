<?php

namespace Tests\Feature\Commerce;

use App\Models\Product;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartAndOrderTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();

        $this->user->wallets()->create([
            'id' => \Illuminate\Support\Str::uuid(),
            'currency' => 'NGN',
            'balance' => '50000',
            'status' => 'active'
        ]);

        $vendor = User::factory()->create();
        $this->product = Product::factory()->create([
            'vendor_id' => $vendor->id,
            'price' => 1000,
            'stock_quantity' => 10,
        ]);
    }

    public function test_user_can_add_item_to_cart()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/commerce/cart/add', [
                'product_id' => $this->product->id,
                'quantity' => 2,
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'message' => 'Item added to cart',
            ]);

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);
    }

    public function test_user_can_view_cart()
    {
        $this->actingAs($this->user)
            ->postJson('/api/commerce/cart/add', [
                'product_id' => $this->product->id,
                'quantity' => 2,
            ]);

        $response = $this->actingAs($this->user)->getJson('/api/commerce/cart');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    '*' => [
                        'id',
                        'product_id',
                        'quantity',
                        'product' => ['id', 'name', 'price']
                    ]
                ]
            ]);
    }

    public function test_user_can_update_cart_quantity()
    {
        $this->actingAs($this->user)
            ->postJson('/api/commerce/cart/add', [
                'product_id' => $this->product->id,
                'quantity' => 2,
            ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/commerce/cart/update', [
                'product_id' => $this->product->id,
                'quantity' => 5,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Cart updated',
            ]);

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 5,
        ]);
    }

    public function test_user_can_remove_item_from_cart()
    {
        $this->actingAs($this->user)
            ->postJson('/api/commerce/cart/add', [
                'product_id' => $this->product->id,
                'quantity' => 2,
            ]);

        $response = $this->actingAs($this->user)
            ->deleteJson('/api/commerce/cart/remove', [
                'product_id' => $this->product->id,
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('cart_items', [
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
        ]);
    }

    public function test_user_can_place_order_from_cart()
    {
        $this->actingAs($this->user)
            ->postJson('/api/commerce/cart/add', [
                'product_id' => $this->product->id,
                'quantity' => 2,
            ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/commerce/orders/checkout', [
                'payment_method' => 'wallet',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'message' => 'Order placed successfully',
            ]);

        $this->assertDatabaseHas('orders', [
            'user_id' => $this->user->id,
            'total_amount' => 2000,
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('order_items', [
            'product_id' => $this->product->id,
            'quantity' => 2,
            'total_price' => 2000,
        ]);

        // Verify inventory was updated
        $this->product->refresh();
        $this->assertEquals(8, $this->product->stock_quantity);

        // Verify cart is cleared
        $this->assertDatabaseMissing('cart_items', [
            'user_id' => $this->user->id,
        ]);
    }

    public function test_user_can_view_order_history()
    {
        $this->actingAs($this->user)
            ->postJson('/api/commerce/cart/add', [
                'product_id' => $this->product->id,
                'quantity' => 2,
            ]);

        $this->actingAs($this->user)
            ->postJson('/api/commerce/orders/checkout', [
                'payment_method' => 'wallet',
            ]);

        $response = $this->actingAs($this->user)->getJson('/api/commerce/orders');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'total_amount',
                            'status',
                            'items' => [
                                '*' => [
                                    'product' => ['id', 'name']
                                ]
                            ]
                        ]
                    ]
                ]
            ]);
    }

    public function test_user_can_view_single_order_detail()
    {
        $this->actingAs($this->user)
            ->postJson('/api/commerce/cart/add', [
                'product_id' => $this->product->id,
                'quantity' => 1,
            ]);

        $checkoutResponse = $this->actingAs($this->user)
            ->postJson('/api/commerce/orders/checkout', [
                'payment_method' => 'wallet',
            ]);

        $orderId = $checkoutResponse->json('data.id');

        $response = $this->actingAs($this->user)->getJson("/api/commerce/orders/{$orderId}");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'id' => $orderId,
                    'total_amount' => '1000.00',
                ]
            ]);
    }
}
