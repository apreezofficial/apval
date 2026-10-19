<?php

declare(strict_types=1);

namespace Tests\Feature\Commerce;

use App\Models\KycRecord;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketplaceProductPerfectionTest extends TestCase
{
    use RefreshDatabase;

    private User $vendor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->vendor = User::factory()->create([
            'first_name' => 'Adio',
            'last_name' => 'Farms',
            'role' => 'vendor',
            'market' => 'Mile 12 Market, Lagos',
        ]);

        KycRecord::create([
            'user_id' => $this->vendor->id,
            'bvn_verified' => true,
            'level' => 1,
            'status' => 'verified',
        ]);
    }

    public function test_marketplace_filtering_by_price_and_perishable(): void
    {
        Product::factory()->create([
            'vendor_id' => $this->vendor->id,
            'name' => 'Perishable Tomatoes',
            'price' => 3000,
            'is_perishable' => true,
            'status' => 'active',
            'stock_quantity' => 10,
        ]);

        Product::factory()->create([
            'vendor_id' => $this->vendor->id,
            'name' => 'Dry Yam Flour',
            'price' => 12000,
            'is_perishable' => false,
            'status' => 'active',
            'stock_quantity' => 50,
        ]);

        // Filter by perishable = true
        $res1 = $this->getJson('/api/commerce/marketplace/products?is_perishable=1');
        $res1->assertStatus(200);
        $this->assertCount(1, $res1->json('data.data'));
        $this->assertEquals('Perishable Tomatoes', $res1->json('data.data.0.name'));

        // Filter by price range <= 5000
        $res2 = $this->getJson('/api/commerce/marketplace/products?max_price=5000');
        $res2->assertStatus(200);
        $this->assertCount(1, $res2->json('data.data'));
        $this->assertEquals('Perishable Tomatoes', $res2->json('data.data.0.name'));
    }

    public function test_marketplace_sorting_by_price(): void
    {
        Product::factory()->create([
            'vendor_id' => $this->vendor->id,
            'name' => 'Product Cheap',
            'price' => 1000,
            'status' => 'active',
        ]);

        Product::factory()->create([
            'vendor_id' => $this->vendor->id,
            'name' => 'Product Expensive',
            'price' => 9000,
            'status' => 'active',
        ]);

        $resAsc = $this->getJson('/api/commerce/marketplace/products?sort=price_asc');
        $resAsc->assertStatus(200);
        $this->assertEquals('Product Cheap', $resAsc->json('data.data.0.name'));

        $resDesc = $this->getJson('/api/commerce/marketplace/products?sort=price_desc');
        $resDesc->assertStatus(200);
        $this->assertEquals('Product Expensive', $resDesc->json('data.data.0.name'));
    }

    public function test_marketplace_share_card_endpoint(): void
    {
        $product = Product::factory()->create([
            'vendor_id' => $this->vendor->id,
            'name' => 'Golden Brown Eggs',
            'price' => 4200,
            'unit' => 'crate',
            'stock_quantity' => 30,
            'status' => 'active',
        ]);

        $res = $this->getJson("/api/commerce/marketplace/products/{$product->id}/share-card");
        $res->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'product_id' => $product->id,
                    'name' => 'Golden Brown Eggs',
                    'price' => 4200,
                    'unit' => 'crate',
                    'in_stock' => true,
                    'is_verified' => true,
                ],
            ]);
    }
}
