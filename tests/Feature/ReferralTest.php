<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Referral;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReferralTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_referral_summary(): void
    {
        $user = User::factory()->create();
        Wallet::factory()->create(['user_id' => $user->id, 'currency' => 'NGN']);

        $this->actingAs($user, 'sanctum');

        $response = $this->getJson('/api/userManager/referral');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'referral_code' => $user->referral_code,
                    'total_referrals' => 0,
                ],
            ]);
    }

    public function test_registration_with_referral_code_links_referrer_and_creates_pending_referral(): void
    {
        $referrer = User::factory()->create();
        Wallet::factory()->create(['user_id' => $referrer->id, 'currency' => 'NGN']);

        $registerResponse = $this->postJson('/api/userManager/register', [
            'first_name' => 'Friend',
            'last_name' => 'Referee',
            'email' => 'referee@example.com',
            'phone' => '08099887766',
            'password' => 'Password1!',
            'role' => 'customer',
            'referral_code' => $referrer->referral_code,
        ]);

        $registerResponse->assertStatus(200);

        // Retrieve cached OTP and verify registration
        $cached = \Illuminate\Support\Facades\Cache::get('pending_reg:referee@example.com');
        $creator = app(\Laravel\Fortify\Contracts\CreatesNewUsers::class);
        $user = $creator->create($cached['input']);

        $refereeId = $user->id;

        $this->assertDatabaseHas('users', [
            'id' => $refereeId,
            'referred_by' => $referrer->id,
        ]);

        $this->assertDatabaseHas('referrals', [
            'referrer_id' => $referrer->id,
            'referee_id' => $refereeId,
            'status' => 'pending',
        ]);
    }

    public function test_qualifying_delivery_confirmation_rewards_both_referrer_and_referee(): void
    {
        $referrer = User::factory()->create();
        $referrerWallet = Wallet::factory()->create(['user_id' => $referrer->id, 'currency' => 'NGN', 'agro_points' => '0.0000']);

        $referee = User::factory()->create(['referred_by' => $referrer->id]);
        $refereeWallet = Wallet::factory()->create(['user_id' => $referee->id, 'currency' => 'NGN', 'agro_points' => '0.0000', 'balance' => '20000.00']);

        Referral::create([
            'referrer_id' => $referrer->id,
            'referee_id' => $referee->id,
            'status' => 'pending',
        ]);

        // Vendor & product setup
        $vendor = User::factory()->create(['role' => 'vendor']);
        Wallet::factory()->create(['user_id' => $vendor->id, 'currency' => 'NGN']);
        $product = Product::factory()->create(['vendor_id' => $vendor->id, 'price' => 5000, 'stock_quantity' => 10]);

        // Referee places order via cart
        $this->actingAs($referee, 'sanctum')->postJson('/api/commerce/cart/add', [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        /** @var \App\Services\Commerce\OrderService $orderService */
        $orderService = app(\App\Services\Commerce\OrderService::class);
        $order = $orderService->placeOrder($referee, ['method' => 'wallet']);
        $orderItem = $order->items->first();

        // Vendor dispatches item
        $orderItem->update(['vendor_status' => 'dispatched']);

        // Referee confirms delivery
        $confirmResponse = $this->putJson("/api/commerce/orders/items/{$orderItem->id}/confirm-delivery");

        $confirmResponse->assertStatus(200);

        // Assert referral rewarded
        $this->assertDatabaseHas('referrals', [
            'referrer_id' => $referrer->id,
            'referee_id' => $referee->id,
            'status' => 'rewarded',
        ]);

        // Assert points credited
        $this->assertEquals('50.0000', $referrerWallet->fresh()->agro_points);
        $this->assertEquals('20.0000', $refereeWallet->fresh()->agro_points);
    }
}
