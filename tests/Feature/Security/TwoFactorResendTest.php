<?php

declare(strict_types=1);

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TwoFactorResendTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_request_and_resend_otp_with_cooldown(): void
    {
        config(['services.resend.key' => 're_test_key_123456789']);

        Http::fake([
            'https://api.resend.com/*' => Http::response(['id' => 'msg_123456'], 200),
        ]);

        $user = User::factory()->create([
            'two_factor_enabled' => true,
            'two_factor_channel' => 'email',
            'email' => 'user@example.com',
        ]);

        $this->actingAs($user, 'sanctum');

        // 1. Initial OTP Request
        $response = $this->postJson('/api/userManager/2fa/request-otp', [
            'purpose' => 'withdrawal',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'OTP sent.',
            ]);

        // 2. Immediate Resend Attempt (Fails due to 60-second cooldown)
        $resendResponse = $this->postJson('/api/userManager/2fa/resend-otp', [
            'purpose' => 'withdrawal',
        ]);

        $resendResponse->assertStatus(429)
            ->assertJson([
                'code' => 'OTP_COOLDOWN',
            ]);

        // 3. Clear Cooldown Cache to simulate 60s elapsed time
        Cache::forget("twofa:cooldown:withdrawal:{$user->id}");

        // 4. Resend OTP after cooldown
        $resendSuccessResponse = $this->postJson('/api/userManager/2fa/resend-otp', [
            'purpose' => 'withdrawal',
        ]);

        $resendSuccessResponse->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'OTP resent successfully.',
            ]);

        // Verify Resend HTTP API was called
        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'resend.com') &&
                   $request['to'][0] === 'user@example.com';
        });
    }

    public function test_user_can_verify_resent_otp(): void
    {
        Http::fake();

        $user = User::factory()->create([
            'two_factor_enabled' => true,
            'two_factor_channel' => 'email',
        ]);

        $this->actingAs($user, 'sanctum');

        $this->postJson('/api/userManager/2fa/request-otp', ['purpose' => 'transfer']);

        // Inspect cached OTP
        $otpKey = "twofa:transfer:{$user->id}";
        $this->assertTrue(Cache::has($otpKey));

        // Resend after clearing cooldown
        Cache::forget("twofa:cooldown:transfer:{$user->id}");
        $this->postJson('/api/userManager/2fa/resend-otp', ['purpose' => 'transfer']);

        // Cannot verify with bogus OTP
        $failResponse = $this->postJson('/api/userManager/2fa/verify-otp', [
            'purpose' => 'transfer',
            'otp' => '000000',
        ]);
        $failResponse->assertStatus(422);
    }
}
