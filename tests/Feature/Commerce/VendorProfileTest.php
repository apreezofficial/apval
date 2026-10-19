<?php

namespace Tests\Feature\Commerce;

use App\Models\User;
use App\Models\VendorProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VendorProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_vendor_can_retrieve_their_lazy_initialized_store_settings(): void
    {
        $vendor = User::factory()->create(['role' => 'vendor']);

        $this->actingAs($vendor, 'sanctum');

        $response = $this->getJson('/api/commerce/vendor/settings');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'store_name' => $vendor->first_name . ' ' . $vendor->last_name . "'s Store",
                    'phone' => $vendor->phone,
                    'email' => $vendor->email,
                    'is_open' => true,
                ]
            ]);

        $this->assertDatabaseHas('vendor_profiles', [
            'user_id' => $vendor->id,
            'store_name' => $vendor->first_name . ' ' . $vendor->last_name . "'s Store",
        ]);
    }

    public function test_vendor_can_update_their_store_settings(): void
    {
        $vendor = User::factory()->create(['role' => 'vendor']);
        $profile = VendorProfile::create([
            'id' => \Illuminate\Support\Str::uuid()->toString(),
            'user_id' => $vendor->id,
            'store_name' => 'Old Store Name',
            'is_open' => true,
        ]);

        $this->actingAs($vendor, 'sanctum');

        $response = $this->putJson('/api/commerce/vendor/settings', [
            'store_name' => 'New Store Name',
            'description' => 'We sell fresh farm produce.',
            'phone' => '+2348000000000',
            'email' => 'store@example.com',
            'store_address' => '123 Farm Road, Lagos',
            'return_policy' => 'Return within 7 days in original condition.',
            'delivery_information' => 'Delivery within Lagos in 2-3 days.',
            'store_logo_url' => 'https://example.com/logo.png',
            'is_open' => false,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Store settings updated successfully',
                'data' => [
                    'store_name' => 'New Store Name',
                    'description' => 'We sell fresh farm produce.',
                    'phone' => '+2348000000000',
                    'email' => 'store@example.com',
                    'store_address' => '123 Farm Road, Lagos',
                    'return_policy' => 'Return within 7 days in original condition.',
                    'delivery_information' => 'Delivery within Lagos in 2-3 days.',
                    'store_logo_url' => 'https://example.com/logo.png',
                    'is_open' => false,
                ]
            ]);

        $this->assertDatabaseHas('vendor_profiles', [
            'user_id' => $vendor->id,
            'store_name' => 'New Store Name',
            'is_open' => false,
        ]);
    }

    public function test_vendor_can_retrieve_their_lazy_initialized_store_settings_singular(): void
    {
        $vendor = User::factory()->create(['role' => 'vendor']);

        $this->actingAs($vendor, 'sanctum');

        $response = $this->getJson('/api/commerce/vendor/setting');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'store_name' => $vendor->first_name . ' ' . $vendor->last_name . "'s Store",
                    'phone' => $vendor->phone,
                    'email' => $vendor->email,
                    'is_open' => true,
                ]
            ]);
    }

    public function test_vendor_can_update_their_store_settings_singular(): void
    {
        $vendor = User::factory()->create(['role' => 'vendor']);
        $profile = VendorProfile::create([
            'id' => \Illuminate\Support\Str::uuid()->toString(),
            'user_id' => $vendor->id,
            'store_name' => 'Old Store Name',
            'is_open' => true,
        ]);

        $this->actingAs($vendor, 'sanctum');

        $response = $this->putJson('/api/commerce/vendor/setting', [
            'store_name' => 'New Store Name',
            'description' => 'We sell fresh farm produce.',
            'phone' => '+2348000000000',
            'email' => 'store@example.com',
            'store_address' => '123 Farm Road, Lagos',
            'return_policy' => 'Return within 7 days in original condition.',
            'delivery_information' => 'Delivery within Lagos in 2-3 days.',
            'store_logo_url' => 'https://example.com/logo.png',
            'is_open' => false,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Store settings updated successfully',
            ]);
    }

    public function test_customer_cannot_retrieve_or_update_vendor_settings(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $this->actingAs($customer, 'sanctum');

        // GET request should be forbidden
        $responseGet = $this->getJson('/api/commerce/vendor/settings');
        $responseGet->assertStatus(403);

        // PUT request should be forbidden
        $responsePut = $this->putJson('/api/commerce/vendor/settings', [
            'store_name' => 'Should fail',
        ]);
        $responsePut->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_access_settings(): void
    {
        // GET request should be unauthorized
        $responseGet = $this->getJson('/api/commerce/vendor/settings');
        $responseGet->assertStatus(401);

        // PUT request should be unauthorized
        $responsePut = $this->putJson('/api/commerce/vendor/settings', [
            'store_name' => 'Should fail',
        ]);
        $responsePut->assertStatus(401);
    }
}
