<?php

namespace Tests\Feature\Commerce;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class VendorProductManagementTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_vendor_can_update_their_own_product(): void
    {
        $vendor = User::factory()->create();
        $product = Product::factory()->create(['vendor_id' => $vendor->id, 'name' => 'Old Name']);
        
        $this->actingAs($vendor, 'sanctum');

        $response = $this->putJson("/api/commerce/vendor/products/{$product->id}", [
            'name' => 'New Name',
            'price' => 2000,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Product updated successfully',
                'data' => [
                    'name' => 'New Name',
                    'price' => 2000,
                ]
            ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'New Name',
        ]);
    }

    public function test_vendor_cannot_update_another_vendors_product(): void
    {
        $vendor1 = User::factory()->create();
        $vendor2 = User::factory()->create();
        $product = Product::factory()->create(['vendor_id' => $vendor1->id]);
        
        $this->actingAs($vendor2, 'sanctum');

        $response = $this->putJson("/api/commerce/vendor/products/{$product->id}", [
            'name' => 'Hacker Name',
        ]);

        $response->assertStatus(404);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => $product->name,
        ]);
    }

    public function test_vendor_can_delete_their_own_product(): void
    {
        $vendor = User::factory()->create();
        $product = Product::factory()->create(['vendor_id' => $vendor->id]);
        
        $this->actingAs($vendor, 'sanctum');

        $response = $this->deleteJson("/api/commerce/vendor/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Product deleted successfully']);

        $this->assertSoftDeleted($product);
    }

    public function test_vendor_cannot_delete_another_vendors_product(): void
    {
        $vendor1 = User::factory()->create();
        $vendor2 = User::factory()->create();
        $product = Product::factory()->create(['vendor_id' => $vendor1->id]);
        
        $this->actingAs($vendor2, 'sanctum');

        $response = $this->deleteJson("/api/commerce/vendor/products/{$product->id}");

        $response->assertStatus(404);
        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }
    public function test_vendor_can_update_product_images(): void
    {
        $vendor = User::factory()->create();
        $product = Product::factory()->create(['vendor_id' => $vendor->id]);
        $product->images()->create([
            'image_url' => 'https://example.com/old-image.jpg',
            'is_primary' => true,
            'sort_order' => 0
        ]);
        
        $this->actingAs($vendor, 'sanctum');

        $response = $this->putJson("/api/commerce/vendor/products/{$product->id}", [
            'images' => [
                [
                    'url' => 'https://example.com/new-image-1.jpg',
                    'is_primary' => true,
                ],
                [
                    'url' => 'https://example.com/new-image-2.jpg',
                    'is_primary' => false,
                ],
            ]
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseMissing('product_images', [
            'image_url' => 'https://example.com/old-image.jpg',
        ]);

        $this->assertDatabaseHas('product_images', [
            'product_id' => $product->id,
            'image_url' => 'https://example.com/new-image-1.jpg',
            'is_primary' => true,
        ]);

        $this->assertDatabaseHas('product_images', [
            'product_id' => $product->id,
            'image_url' => 'https://example.com/new-image-2.jpg',
            'is_primary' => false,
        ]);
    }
}
