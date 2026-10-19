<?php

declare(strict_types=1);

namespace Tests\Feature\Wallet;

use App\Models\KycRecord;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KycTierEnforcementTest extends TestCase
{
    use RefreshDatabase;

    // ---------------------------------------------------------------
    // TRANSFER TIER ENFORCEMENT
    // ---------------------------------------------------------------

    public function test_tier0_user_cannot_transfer(): void
    {
        $sender   = User::factory()->create();
        $receiver = User::factory()->create();

        // Give sender a wallet with balance but NO KYC
        Wallet::factory()->create([
            'user_id'  => $sender->id,
            'currency' => 'NGN',
            'balance'  => '10000.00',
            'status'   => 'active',
        ]);

        $this->actingAs($sender, 'sanctum');

        $response = $this->postJson('/api/walletManager/transfer', [
            'to'              => $receiver->email,
            'amount'          => '1000',
            'currency'        => 'NGN',
            'idempotency_key' => 'test-tier0-transfer',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'code' => 'KYC_REQUIRED',
            ]);
    }

    public function test_tier1_user_can_transfer_within_daily_limit(): void
    {
        [$sender, $receiver] = $this->makeTier1Pair();

        $this->actingAs($sender, 'sanctum');

        $response = $this->postJson('/api/walletManager/transfer', [
            'to'              => $receiver->email,
            'amount'          => '5000',
            'currency'        => 'NGN',
            'idempotency_key' => 'test-tier1-ok-' . uniqid(),
        ]);

        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);
    }

    public function test_tier1_user_blocked_when_daily_limit_exceeded(): void
    {
        [$sender, $receiver] = $this->makeTier1Pair(walletBalance: '60000.00');

        $this->actingAs($sender, 'sanctum');

        // Tier 1 daily limit is ₦50,000 — attempt ₦55,000
        $response = $this->postJson('/api/walletManager/transfer', [
            'to'              => $receiver->email,
            'amount'          => '55000',
            'currency'        => 'NGN',
            'idempotency_key' => 'test-tier1-overlimit-' . uniqid(),
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'code' => 'LIMIT_EXCEEDED',
            ]);
    }

    // ---------------------------------------------------------------
    // KYC TIER METADATA ENDPOINT
    // ---------------------------------------------------------------

    public function test_unauthenticated_user_cannot_view_kyc(): void
    {
        $this->getJson('/api/userManager/kyc')->assertStatus(401);
    }

    public function test_tier0_user_gets_correct_tier_info(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $response = $this->getJson('/api/userManager/kyc');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data'   => [
                    'kyc_status' => 'pending',
                    'level'      => 0,
                    'bvn_verified' => false,
                ],
            ]);
    }

    public function test_tier1_user_gets_correct_kyc_status(): void
    {
        $user = User::factory()->create();

        KycRecord::create([
            'user_id'      => $user->id,
            'status'       => 'verified',
            'level'        => 1,
            'bvn_verified' => true,
            'verified_at'  => now(),
        ]);

        $this->actingAs($user, 'sanctum');

        $response = $this->getJson('/api/userManager/kyc');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data'   => [
                    'kyc_status'   => 'verified',
                    'level'        => 1,
                    'bvn_verified' => true,
                ],
            ]);
    }

    // ---------------------------------------------------------------
    // BALANCE CAP ENFORCEMENT
    // ---------------------------------------------------------------

    public function test_kyc_tier_service_returns_correct_limits(): void
    {
        $tierService = app(\App\Services\Kyc\KycTierService::class);

        $tier0 = $tierService->getTierLimits(0);
        $tier1 = $tierService->getTierLimits(1);
        $tier2 = $tierService->getTierLimits(2);
        $tier3 = $tierService->getTierLimits(3);

        // Tier 0 — no transfer / withdrawal allowed
        $this->assertFalse($tier0['can_transfer']);
        $this->assertFalse($tier0['can_withdraw']);
        $this->assertEquals('0.00', $tier0['daily_limit']);

        // Tier 1 — limited
        $this->assertTrue($tier1['can_transfer']);
        $this->assertTrue($tier1['can_withdraw']);
        $this->assertEquals('50000.00', $tier1['daily_limit']);
        $this->assertEquals('300000.00', $tier1['max_balance']);

        // Tier 2 — higher limits
        $this->assertEquals('500000.00', $tier2['daily_limit']);
        $this->assertEquals('1000000.00', $tier2['max_balance']);

        // Tier 3 — highest limits
        $this->assertEquals('10000000.00', $tier3['daily_limit']);
        $this->assertEquals('100000000.00', $tier3['max_balance']);
    }

    // ---------------------------------------------------------------
    // HELPERS
    // ---------------------------------------------------------------

    private function makeTier1Pair(string $walletBalance = '20000.00'): array
    {
        $sender = User::factory()->create();

        KycRecord::create([
            'user_id'      => $sender->id,
            'status'       => 'verified',
            'level'        => 1,
            'bvn_verified' => true,
            'verified_at'  => now(),
        ]);

        Wallet::factory()->create([
            'user_id'  => $sender->id,
            'currency' => 'NGN',
            'balance'  => $walletBalance,
            'status'   => 'active',
        ]);

        $receiver = User::factory()->create();

        Wallet::factory()->create([
            'user_id'  => $receiver->id,
            'currency' => 'NGN',
            'balance'  => '0.00',
            'status'   => 'active',
        ]);

        return [$sender, $receiver];
    }
}
