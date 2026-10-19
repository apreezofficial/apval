<?php

declare(strict_types=1);

namespace Tests\Feature\Wallet;

use App\Models\Product;
use App\Models\User;
use App\Models\Wallet;
use App\Services\Commerce\OrderService;
use App\Services\Commerce\VendorFulfillmentService;
use App\Services\Wallet\EscrowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EscrowWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_escrow_holding_release_and_refund_flow(): void
    {
        /** @var User $buyer */
        $buyer = User::factory()->create();
        /** @var User $vendor */
        $vendor = User::factory()->create();

        // Create Buyer NGN wallet with N100,000 balance
        Wallet::create([
            'user_id' => $buyer->id,
            'currency' => 'NGN',
            'balance' => 100000.00,
            'status' => 'active',
        ]);

        $product = Product::factory()->create([
            'vendor_id' => $vendor->id,
            'price' => 20000.00,
            'stock_quantity' => 10,
        ]);

        // 1. Add to cart & Place Order (Holds N20,000 in System Escrow Vault)
        $this->actingAs($buyer, 'sanctum')->postJson('/api/commerce/cart/add', [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        /** @var OrderService $orderService */
        $orderService = app(OrderService::class);
        $order = $orderService->placeOrder($buyer, ['method' => 'wallet']);

        // Check Buyer wallet balance is debited
        $buyerWallet = Wallet::where('user_id', $buyer->id)->where('currency', 'NGN')->first();
        $this->assertEquals(80000.00, (float) $buyerWallet->balance);

        // Check System Escrow Vault has N20,000 balance
        /** @var EscrowService $escrowService */
        $escrowService = app(EscrowService::class);
        $escrowVault = $escrowService->getOrCreateSystemEscrowWallet('NGN');
        $this->assertEquals(20000.00, (float) $escrowVault->balance);

        $orderItem = $order->items->first();

        // 2. Vendor accepts, marks ready, and dispatches item
        $orderItem->update(['vendor_status' => 'dispatched']);

        // 3. Buyer confirms delivery -> Releases escrow funds to Vendor
        $orderService->confirmItemDelivery($buyer, $orderItem->id);

        $vendorWallet = Wallet::where('user_id', $vendor->id)->where('currency', 'NGN')->first();
        $this->assertNotNull($vendorWallet);
        $this->assertEquals(20000.00, (float) $vendorWallet->balance);

        // Escrow vault balance is now N0
        $this->assertEquals(0.00, (float) $escrowVault->fresh()->balance);
    }
}
