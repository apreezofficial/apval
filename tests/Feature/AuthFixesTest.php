<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthFixesTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_with_uppercase_and_whitespace_normalizes(): void
    {
        $payload = [
            'first_name' => '  Jane  ',
            'last_name'  => '  Doe  ',
            'email'      => '  JaneDoe@Example.Com  ',
            'phone'      => ' 08022334455 ',
            'password'   => 'Password123!',
            'role'       => 'customer',
        ];

        $regResponse = $this->postJson('/api/userManager/register', $payload);
        $regResponse->assertStatus(201);

        $this->assertDatabaseHas('users', [
            'first_name' => 'Jane',
            'last_name'  => 'Doe',
            'email'      => 'janedoe@example.com',
            'phone'      => '08022334455',
        ]);
    }

    public function test_login_supports_both_login_and_email_keys(): void
    {
        User::factory()->create([
            'email'    => 'buyer@agreonpay.com',
            'phone'    => '08012345678',
            'password' => Hash::make('Secret123!'),
            'status'   => 'active',
        ]);

        // 1. Using 'email' key
        $response1 = $this->postJson('/api/userManager/login', [
            'email'    => 'buyer@agreonpay.com',
            'password' => 'Secret123!',
        ]);
        $response1->assertStatus(200)->assertJson(['status' => 'success']);

        // 2. Using 'login' key with mixed-case and whitespace
        $response2 = $this->postJson('/api/userManager/login', [
            'login'    => '  BUYER@agreonpay.com  ',
            'password' => 'Secret123!',
        ]);
        $response2->assertStatus(200)->assertJson(['status' => 'success']);

        // 3. Using Nigerian phone format with +234
        $response3 = $this->postJson('/api/userManager/login', [
            'login'    => '+2348012345678',
            'password' => 'Secret123!',
        ]);
        $response3->assertStatus(200)->assertJson(['status' => 'success']);
    }

    public function test_reset_password_web_page_renders(): void
    {
        $response = $this->get('/reset-password?token=sample-token&email=test@agreonpay.com');
        $response->assertStatus(200)
            ->assertSee('Set New Password')
            ->assertSee('sample-token')
            ->assertSee('test@agreonpay.com');
    }

    public function test_user_can_request_and_verify_otp_for_email_verification(): void
    {
        \Illuminate\Support\Facades\Http::fake();

        $user = User::factory()->create([
            'email' => 'newuser@agreonpay.com',
            'email_verified_at' => null,
            'two_factor_enabled' => false,
        ]);

        // Request verification OTP
        $reqResponse = $this->actingAs($user)->postJson('/api/userManager/2fa/request-otp', [
            'purpose' => 'verification',
        ]);
        $reqResponse->assertStatus(200)->assertJson(['status' => 'success']);
    }
}
