<?php

declare(strict_types=1);

namespace App\Services\Commerce;

use App\Exceptions\Domain\InvalidOrderStateException;
use App\Exceptions\Domain\InvalidRequestException;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\SplitBuyContribution;
use App\Models\SplitBuySession;
use App\Models\User;
use App\Services\Wallet\EscrowService;
use App\Services\Wallet\WalletBalanceService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SplitBuyService
{
    public function __construct(
        private WalletBalanceService $walletBalanceService,
        private EscrowService $escrowService,
        private InventoryService $inventoryService
    ) {}

    /**
     * Create a new Split Buy session for group purchasing.
     */
    public function createSession(User $creator, string $productId, float $quantity, float $initialContribution): SplitBuySession
    {
        return DB::transaction(function () use ($creator, $productId, $quantity, $initialContribution) {
            $product = Product::findOrFail($productId);
            $totalAmount = bcmul((string)$quantity, (string)$product->price, 2);

            if (floatval($initialContribution) > floatval($totalAmount)) {
                throw new InvalidRequestException('Initial contribution cannot exceed total order amount.');
            }

            $session = SplitBuySession::create([
                'id' => (string) Str::uuid(),
                'creator_id' => $creator->id,
                'product_id' => $product->id,
                'quantity' => $quantity,
                'total_amount' => $totalAmount,
                'current_amount' => '0.00',
                'status' => 'pending',
                'expires_at' => now()->addDays(3),
            ]);

            if ($initialContribution > 0) {
                $this->contribute($creator, $session->id, $initialContribution);
            }

            return $session->fresh(['contributions', 'product', 'creator']);
        });
    }

    /**
     * Contribute wallet funds to an active Split Buy session.
     */
    public function contribute(User $user, string $sessionId, float $amount): SplitBuyContribution
    {
        return DB::transaction(function () use ($user, $sessionId, $amount) {
            /** @var SplitBuySession $session */
            $session = SplitBuySession::where('id', $sessionId)->lockForUpdate()->firstOrFail();

            if ($session->status !== 'pending') {
                throw new InvalidOrderStateException('This Split Buy session is no longer active.');
            }

            if ($session->expires_at->isPast()) {
                $session->update(['status' => 'expired']);
                throw new InvalidOrderStateException('This Split Buy session has expired.');
            }

            $formattedAmount = number_format($amount, 2, '.', '');
            $remainingNeeded = bcsub((string)$session->total_amount, (string)$session->current_amount, 2);

            if (bccomp($formattedAmount, $remainingNeeded, 2) > 0) {
                throw new InvalidRequestException("Contribution exceeds remaining amount required (N{$remainingNeeded}).");
            }

            // Lock user's NGN wallet and transfer contribution into System Escrow
            $wallet = $this->walletBalanceService->lockUserWallet($user->id, 'NGN');
            $transaction = $this->escrowService->holdInEscrow($wallet, $formattedAmount, $session->id);

            $contribution = SplitBuyContribution::create([
                'id' => (string) Str::uuid(),
                'split_buy_session_id' => $session->id,
                'user_id' => $user->id,
                'amount' => $formattedAmount,
                'status' => 'paid',
                'transaction_id' => $transaction->id,
            ]);

            $newCurrentAmount = bcadd((string)$session->current_amount, $formattedAmount, 2);
            $session->update(['current_amount' => $newCurrentAmount]);

            // If target total amount reached -> Complete session and auto-generate Order!
            if (bccomp($newCurrentAmount, (string)$session->total_amount, 2) >= 0) {
                $this->finalizeSplitBuyOrder($session);
            }

            return $contribution;
        });
    }

    /**
     * Finalize order generation when group payment reaches 100%.
     */
    private function finalizeSplitBuyOrder(SplitBuySession $session): Order
    {
        $product = $session->product;

        $order = Order::create([
            'id' => (string) Str::uuid(),
            'user_id' => $session->creator_id,
            'total_amount' => $session->total_amount,
            'status' => 'pending',
            'payment_status' => 'paid',
            'payment_method' => 'split_buy',
        ]);

        OrderItem::create([
            'id' => (string) Str::uuid(),
            'order_id' => $order->id,
            'product_id' => $product->id,
            'vendor_id' => $product->vendor_id,
            'quantity' => $session->quantity,
            'unit_price' => $product->price,
            'total_price' => $session->total_amount,
        ]);

        // Deduct product inventory stock
        $this->inventoryService->adjustStock(
            $product,
            'subtraction',
            (float) $session->quantity,
            'split_buy_order',
            $order->id
        );

        $session->update(['status' => 'completed']);

        return $order;
    }
}
