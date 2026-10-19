<?php

namespace Tests\Feature\Commerce;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class VendorProductContractTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * This test documents the API contract for product creation.
     * Frontend developers can use this as a reference for required fields and response structure.
     */
    public function test_vendor_can_create_product_with_required_and_optional_fields(): void
    {
        // 1. Setup: Create a vendor and authenticate
        $vendor = User::factory()->create();
        $this->actingAs($vendor, 'sanctum');

        // 2. Define the Request Body (What frontend sends)
        $requestData = [
            'name' => 'Fresh Organic Tomatoes',
            'description' => 'Locally grown organic tomatoes, harvested daily.', // Optional
            'category' => 'Vegetables',
            'price' => 1500.50,
            'unit' => 'kg',
            'min_order_quantity' => 5, // Optional, defaults to 1
            'pickup_location' => 'Farm Gate, Ibadan', // Optional
            'is_perishable' => true, // Optional, defaults to false
            'logistics_preference' => 'Vendor Delivery', // Optional
            'images' => [ // Optional
                [
                    'url' => 'https://example.com/tomato1.jpg',
                    'is_primary' => true
                ],
                [
                    'url' => 'https://example.com/tomato2.jpg',
                    'is_primary' => false
                ]
            ]
        ];

        // 3. Action: Send POST request
        $response = $this->postJson('/api/commerce/vendor/products', $requestData);

        // 4. Assert Response Structure (What backend returns)
        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'id',
                    'vendor_id',
                    'name',
                    'description',
                    'category',
                    'price',
                    'unit',
                    'stock_quantity',
                    'min_order_quantity',
                    'pickup_location',
                    'is_perishable',
                    'logistics_preference',
                    'status',
                    'created_at',
                    'updated_at',
                    'images' => [
                        '*' => [
                            'id',
                            'product_id',
                            'image_url',
                            'is_primary',
                            'sort_order',
                            'created_at',
                            'updated_at'
                        ]
                    ]
                ]
            ]);

        // 5. Assert Data Integrity
        $response->assertJson([
            'status' => 'success',
            'message' => 'Product created successfully',
            'data' => [
                'name' => $requestData['name'],
                'vendor_id' => $vendor->id,
                'status' => 'active',
                'stock_quantity' => '0.00', // Default value
            ]
        ]);

        $this->assertCount(2, $response->json('data.images'));
        $this->assertEquals('https://example.com/tomato1.jpg', $response->json('data.images.0.image_url'));
    }

    /**
     * Test validation for required fields.
     */
    public function test_create_product_requires_mandatory_fields(): void
    {
        $vendor = User::factory()->create();
        $this->actingAs($vendor, 'sanctum');

        $response = $this->postJson('/api/commerce/vendor/products', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'category', 'price', 'unit']);
    }
}
