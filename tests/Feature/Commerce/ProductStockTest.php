<?php

namespace Tests\Feature\Commerce;

use App\Models\User;
use App\Models\Product;
use App\Models\InventoryMovement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductStockTest extends TestCase
{
    use RefreshDatabase;

    public function test_vendor_can_create_product_with_initial_stock()
    {
        $vendor = User::factory()->create();
        
        $payload = [
            'name' => 'Fresh Pepper',
            'description' => 'Hot and fresh',
            'category' => 'Vegetables',
            'price' => 10.50,
            'unit' => 'Bag',
            'stock_quantity' => 25,
            'min_order_quantity' => 1,
            'pickup_location' => 'Farm Gate',
            'is_perishable' => true,
        ];

        $response = $this->actingAs($vendor)
            ->postJson('/api/commerce/vendor/products', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.stock_quantity', "25.00");

        $this->assertDatabaseHas('products', [
            'name' => 'Fresh Pepper',
            'stock_quantity' => 25.00,
        ]);

        $this->assertDatabaseHas('inventory_movements', [
            'type' => 'addition',
            'quantity' => 25.00,
            'balance_after' => 25.00,
            'reference_type' => 'initial_stock',
        ]);
    }
}
