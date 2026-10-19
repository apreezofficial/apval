<?php

declare(strict_types=1);

namespace Tests\Feature\Wallet;

use App\Models\User;
use App\Models\Wallet;
use App\Services\Wallet\AgroPointsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgroPointsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_earn_and_redeem_agro_points(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::create([
            'user_id' => $user->id,
            'currency' => 'NGN',
            'balance' => 5000.00,
            'agro_points' => 0.0,
            'status' => 'active',
        ]);

        /** @var AgroPointsService $pointsService */
        $pointsService = app(AgroPointsService::class);

        // Earn 50 points by spending N50,000
        $pointsEarned = $pointsService->awardPointsForSpend($wallet, '50000.00');
        $this->assertEquals(50.0, $pointsEarned);
        $this->assertEquals(50.0, (float) $wallet->fresh()->agro_points);

        // Redeem 10 points (10 * N10 = N100 cash balance credit)
        $this->actingAs($user, 'sanctum');
        $response = $this->postJson('/api/walletManager/points/redeem', [
            'points' => 10,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'points_redeemed' => 10,
                    'cash_credited' => '100.00',
                    'remaining_points' => 40,
                ],
            ]);

        $this->assertEquals(5100.00, (float) $wallet->fresh()->balance);
    }
}
