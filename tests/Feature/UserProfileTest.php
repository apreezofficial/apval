<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_profile_details()
    {
        $user = User::factory()->create([
            'first_name' => 'John',
            'last_name'  => 'Doe',
            'email'      => 'john@example.com',
            'phone'      => '08012345678',
        ]);

        $response = $this->actingAs($user)
            ->getJson('/api/userManager/profile');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data'   => [
                    'id'         => $user->id,
                    'first_name' => 'John',
                    'last_name'  => 'Doe',
                    'email'      => 'john@example.com',
                    'phone'      => '08012345678',
                ],
            ]);
    }

    public function test_user_can_update_profile_info()
    {
        $user = User::factory()->create([
            'first_name' => 'John',
            'last_name'  => 'Doe',
            'phone'      => '08012345678',
        ]);

        $response = $this->actingAs($user)
            ->putJson('/api/userManager/profile', [
                'first_name' => 'Johnny',
                'last_name'  => 'Smith',
                'phone'      => '08099998888',
                'avatar_url' => 'https://cdn.agreonpay.test/avatars/john.jpg',
                'address'    => '12 Farm Settlement Road, Ibadan',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status'  => 'success',
                'message' => 'Profile updated successfully.',
                'data'    => [
                    'first_name' => 'Johnny',
                    'last_name'  => 'Smith',
                    'phone'      => '08099998888',
                    'avatar_url' => 'https://cdn.agreonpay.test/avatars/john.jpg',
                    'address'    => '12 Farm Settlement Road, Ibadan',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'id'         => $user->id,
            'first_name' => 'Johnny',
            'last_name'  => 'Smith',
            'phone'      => '08099998888',
            'avatar_url' => 'https://cdn.agreonpay.test/avatars/john.jpg',
            'address'    => '12 Farm Settlement Road, Ibadan',
        ]);
    }

    public function test_profile_update_validates_phone_uniqueness()
    {
        User::factory()->create(['phone' => '08099998888']);
        $user = User::factory()->create(['phone' => '08012345678']);

        $response = $this->actingAs($user)
            ->putJson('/api/userManager/profile', [
                'phone' => '08099998888', // Duplicate
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['phone']);
    }

    public function test_unauthenticated_user_cannot_access_profile()
    {
        $this->getJson('/api/userManager/profile')->assertStatus(401);
        $this->putJson('/api/userManager/profile', ['first_name' => 'Test'])->assertStatus(401);
    }

    public function test_user_can_update_profile_via_post_with_photo()
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $user = User::factory()->create([
            'address' => null,
            'avatar_url' => null,
        ]);

        $file = \Illuminate\Http\UploadedFile::fake()->image('avatar.jpg', 400, 400);

        $response = $this->actingAs($user)
            ->postJson('/api/userManager/profile', [
                'address' => 'Plot 4, Commercial Road, Ikeja',
                'avatar'  => $file,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status'  => 'success',
                'message' => 'Profile updated successfully.',
            ]);

        $user->refresh();
        $this->assertEquals('Plot 4, Commercial Road, Ikeja', $user->address);
        $this->assertNotNull($user->avatar_url);
        $this->assertStringContainsString('uploads/images', $user->avatar_url);
    }
}
