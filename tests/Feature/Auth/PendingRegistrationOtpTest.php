<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Models\Wallet;
use App\Services\Security\ResendEmailService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Mockery\MockInterface;
use Tests\TestCase;

class PendingRegistrationOtpTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock ResendEmailService so outbound network call is bypassed in tests
        $this->mock(ResendEmailService::class, function (MockInterface $mock) {
            $mock->shouldReceive('sendOtpEmail')->andReturn(true);
        });
    }

    public function test_registration_initiates_and_does_not_save_user_to_database(): void
    {
        $payload = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.pending@example.com',
            'phone' => '+2348011223344',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'role' => 'customer',
        ];

        $response = $this->postJson('/api/userManager/register', $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'data' => [
                'email' => 'john.pending@example.com',
                'requires_otp' => true,
            ],
        ]);

        // CRITICAL ASSERTION: User must NOT be in the database yet!
        $this->assertDatabaseMissing('users', [
            'email' => 'john.pending@example.com',
        ]);

        // Assert pending registration is held in cache
        $this->assertTrue(Cache::has('pending_reg:john.pending@example.com'));
    }

    public function test_invalid_otp_rejects_and_does_not_create_user(): void
    {
        $payload = [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane.pending@example.com',
            'phone' => '+2348022334455',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'role' => 'customer',
        ];

        $this->postJson('/api/userManager/register', $payload)->assertStatus(200);

        // Attempt verify with bogus OTP 000000
        $verifyResponse = $this->postJson('/api/userManager/register/verify-otp', [
            'email' => 'jane.pending@example.com',
            'otp' => '000000',
        ]);

        $verifyResponse->assertStatus(422);

        // User must NOT be created in database
        $this->assertDatabaseMissing('users', [
            'email' => 'jane.pending@example.com',
        ]);
    }

    public function test_valid_otp_creates_user_and_verifies_email(): void
    {
        $payload = [
            'first_name' => 'Alex',
            'last_name' => 'Farmer',
            'email' => 'alex.farmer@example.com',
            'phone' => '+2348033445566',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'role' => 'farmer',
            'market' => 'Mile 12 Market',
        ];

        $this->postJson('/api/userManager/register', $payload)->assertStatus(200);

        // Inject known OTP hash into cache for testing
        $cached = Cache::get('pending_reg:alex.farmer@example.com');
        $this->assertNotNull($cached);
        $testOtp = '123456';
        $cached['otp_hash'] = password_hash($testOtp, PASSWORD_BCRYPT);
        Cache::put('pending_reg:alex.farmer@example.com', $cached, now()->addMinutes(15));

        // Submit correct OTP
        $verifyResponse = $this->postJson('/api/userManager/register/verify-otp', [
            'email' => 'alex.farmer@example.com',
            'otp' => $testOtp,
        ]);

        $verifyResponse->assertStatus(201);
        $verifyResponse->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'token',
                'user' => [
                    'id',
                    'first_name',
                    'last_name',
                    'email',
                    'wallets',
                ],
            ],
        ]);

        // User MUST now exist in the database!
        $this->assertDatabaseHas('users', [
            'email' => 'alex.farmer@example.com',
            'role' => 'farmer',
            'market' => 'Mile 12 Market',
        ]);

        $user = User::where('email', 'alex.farmer@example.com')->first();
        $this->assertNotNull($user->email_verified_at);

        // Wallets must be provisioned
        $this->assertEquals(2, $user->wallets()->count());

        // Cache must be cleared
        $this->assertFalse(Cache::has('pending_reg:alex.farmer@example.com'));
    }

    public function test_resend_registration_otp_enforces_cooldown(): void
    {
        $payload = [
            'first_name' => 'Cooldown',
            'last_name' => 'User',
            'email' => 'cooldown.user@example.com',
            'phone' => '+2348044556677',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'role' => 'customer',
        ];

        $this->postJson('/api/userManager/register', $payload)->assertStatus(200);

        // Immediate resend should trigger 429 cooldown
        $resendResponse = $this->postJson('/api/userManager/register/resend-otp', [
            'email' => 'cooldown.user@example.com',
        ]);

        $resendResponse->assertStatus(429);

        // Clear cooldown
        Cache::forget('pending_reg_cooldown:cooldown.user@example.com');

        // Resend should now succeed
        $resendResponse2 = $this->postJson('/api/userManager/register/resend-otp', [
            'email' => 'cooldown.user@example.com',
        ]);

        $resendResponse2->assertStatus(200);
    }
}
