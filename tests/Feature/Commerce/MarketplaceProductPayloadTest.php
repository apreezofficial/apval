<?php

namespace Tests\Feature\Commerce;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketplaceProductPayloadTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_marketplace_products_payload(): void
    {
        // 1. Setup: Create a vendor and a product with images
        $vendor = User::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe'
        ]);

        $product = Product::create([
            'id' => \Illuminate\Support\Str::uuid(),
            'vendor_id' => $vendor->id,
            'name' => 'Fresh Organic Tomatoes',
            'description' => 'Locally grown organic tomatoes, harvested daily.',
            'category' => 'Vegetables',
            'price' => 1500.50,
            'unit' => 'kg',
            'stock_quantity' => 100,
            'min_order_quantity' => 5,
            'pickup_location' => 'Farm Gate, Ibadan',
            'is_perishable' => true,
            'logistics_preference' => 'Vendor Delivery',
            'status' => 'active',
        ]);

        ProductImage::create([
            'product_id' => $product->id,
            'image_url' => 'https://example.com/tomato1.jpg',
            'is_primary' => true,
            'sort_order' => 0,
        ]);

        ProductImage::create([
            'product_id' => $product->id,
            'image_url' => 'https://example.com/tomato2.jpg',
            'is_primary' => false,
            'sort_order' => 1,
        ]);

        // 2. Action: Hit the marketplace products endpoint
        $response = $this->getJson('/api/commerce/marketplace/products');

        $response->assertStatus(200);
    }
}
