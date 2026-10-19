<?php

namespace Tests\Feature\Commerce;

use App\Models\Product;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase3FulfillmentTest extends TestCase
{
    use RefreshDatabase;

    private User $buyer;
    private User $vendor;
    private User $admin;
    private User $inspector;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->buyer = User::factory()->create();
        $this->vendor = User::factory()->create();
        $this->admin = User::factory()->create(['role' => 'admin'] ?? []); // if role isn't natively seeded we just fake it
        $this->inspector = User::factory()->create(['role' => 'inspector']);
        
        // Fund buyer wallet
        $this->buyer->wallets()->create([
            'id' => \Illuminate\Support\Str::uuid(),
            'currency' => 'NGN',
            'balance' => '50000',
            'status' => 'active'
        ]);

        $this->product = Product::factory()->create([
            'vendor_id' => $this->vendor->id,
            'price' => 1000,
            'stock_quantity' => 10,
        ]);
    }

    private function placeOrder(): string
    {
        // Add to cart
        $this->actingAs($this->buyer)
            ->postJson('/api/commerce/cart/add', [
                'product_id' => $this->product->id,
                'quantity' => 1,
            ]);

        // Checkout
        $response = $this->actingAs($this->buyer)
            ->postJson('/api/commerce/orders/checkout', [
                'payment_method' => 'wallet',
            ]);
            
        return $response->json('data.items.0.id');
    }

    public function test_vendor_can_accept_order_item()
    {
        $orderItemId = $this->placeOrder();

        $response = $this->actingAs($this->vendor)
            ->putJson("/api/commerce/vendor/orders/{$orderItemId}/accept");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ]);

        $this->assertDatabaseHas('order_items', [
            'id' => $orderItemId,
            'vendor_status' => 'accepted',
        ]);
    }

    public function test_vendor_can_reject_order_item_and_restore_inventory()
    {
        $orderItemId = $this->placeOrder();

        $this->product->refresh();
        $this->assertEquals(9, $this->product->stock_quantity); // 1 bought

        $response = $this->actingAs($this->vendor)
            ->putJson("/api/commerce/vendor/orders/{$orderItemId}/reject");

        $response->assertStatus(200);

        $this->assertDatabaseHas('order_items', [
            'id' => $orderItemId,
            'vendor_status' => 'rejected',
        ]);

        $this->product->refresh();
        $this->assertEquals(10, $this->product->stock_quantity); // Restored!
    }

    public function test_vendor_can_mark_ready_for_inspection_which_creates_ticket()
    {
        $orderItemId = $this->placeOrder();
        
        $this->actingAs($this->vendor)
            ->putJson("/api/commerce/vendor/orders/{$orderItemId}/accept");

        $response = $this->actingAs($this->vendor)
            ->putJson("/api/commerce/vendor/orders/{$orderItemId}/ready");

        $response->assertStatus(200);

        $this->assertDatabaseHas('order_items', [
            'id' => $orderItemId,
            'vendor_status' => 'ready_for_inspection',
        ]);

        $this->assertDatabaseHas('inspections', [
            'order_item_id' => $orderItemId,
            'status' => 'pending'
        ]);
    }

    public function test_admin_can_assign_inspector()
    {
        $orderItemId = $this->placeOrder();
        
        $this->actingAs($this->vendor)->putJson("/api/commerce/vendor/orders/{$orderItemId}/accept");
        $this->actingAs($this->vendor)->putJson("/api/commerce/vendor/orders/{$orderItemId}/ready");

        $response = $this->actingAs($this->admin)
            ->postJson("/api/commerce/admin/inspections/assign", [
                'order_item_id' => $orderItemId,
                'inspector_id' => $this->inspector->id
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('inspections', [
            'order_item_id' => $orderItemId,
            'inspector_id' => $this->inspector->id,
            'status' => 'assigned'
        ]);
    }

    public function test_inspector_can_submit_evidence_and_pass()
    {
        $orderItemId = $this->placeOrder();
        
        $this->actingAs($this->vendor)->putJson("/api/commerce/vendor/orders/{$orderItemId}/accept");
        $this->actingAs($this->vendor)->putJson("/api/commerce/vendor/orders/{$orderItemId}/ready");

        $this->actingAs($this->admin)
            ->postJson("/api/commerce/admin/inspections/assign", [
                'order_item_id' => $orderItemId,
                'inspector_id' => $this->inspector->id
            ]);

        $inspection = \App\Models\Inspection::where('order_item_id', $orderItemId)->first();

        // Submit as inspector
        $response = $this->actingAs($this->inspector)
            ->postJson("/api/commerce/inspector/inspections/{$inspection->id}/submit", [
                'passed' => true,
                'notes' => 'Product is in excellent condition.',
                'evidence_urls' => [
                    'https://example.com/evidence1.jpg'
                ]
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('order_items', [
            'id' => $orderItemId,
            'vendor_status' => 'inspected_approved',
        ]);

        $this->assertDatabaseHas('inspections', [
            'id' => $inspection->id,
            'status' => 'passed',
            'notes' => 'Product is in excellent condition.'
        ]);
    }
}
