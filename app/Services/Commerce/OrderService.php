<?php

declare(strict_types=1);

namespace App\Services\Commerce;

use App\Exceptions\Domain\EmptyCartException;
use App\Exceptions\Domain\InvalidOrderStateException;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Transaction;
use App\Models\User;
use App\Services\Wallet\EscrowService;
use App\Services\Wallet\WalletBalanceService;
use App\Services\Logistics\LogisticsService;
use App\Services\Logistics\SettlementService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OrderService
{
    public function __construct(
        private CartService $cartService,
        private InventoryService $inventoryService,
        private WalletBalanceService $walletBalanceService,
        private LogisticsService $logisticsService,
        private SettlementService $settlementService,
        private ?EscrowService $escrowService = null,
        private ?\App\Services\Referral\ReferralService $referralService = null
    ) {
        $this->escrowService = $escrowService ?? app(EscrowService::class);
        $this->referralService = $referralService ?? app(\App\Services\Referral\ReferralService::class);
    }

    public function placeOrder(User $user, array $paymentDetails): Order
    {
        return DB::transaction(function () use ($user, $paymentDetails) {
            $cartItems = $this->cartService->getCart($user);

            if ($cartItems->isEmpty() && !empty($paymentDetails['items']) && is_array($paymentDetails['items'])) {
                foreach ($paymentDetails['items'] as $item) {
                    $productId = $item['product_id'] ?? $item['id'] ?? null;
                    $quantity = (float) ($item['quantity'] ?? 1);
                    if ($productId && $quantity > 0) {
                        $this->cartService->addItem($user, (string) $productId, $quantity);
                    }
                }
                $cartItems = $this->cartService->getCart($user);
            }

            if ($cartItems->isEmpty()) {
                throw new EmptyCartException();
            }

            $totalAmount = $cartItems->reduce(function (string $carry, $item) {
                $lineTotal = bcmul((string) $item->quantity, (string) $item->product->price, 8);
                return bcadd($carry, $lineTotal, 8);
            }, '0');

            $paymentMethod = $paymentDetails['method'] ?? 'wallet';
            $paymentStatus = 'pending';
            $transaction = null;

            if ($paymentMethod === 'wallet' && bccomp($totalAmount, '0', 8) > 0) {
                $wallet = $this->walletBalanceService->lockUserWallet($user->id, 'NGN');
                $transaction = $this->escrowService->holdInEscrow($wallet, $totalAmount, 'pending');
                $paymentStatus = 'paid';
            }

            $order = Order::create([
                'id' => (string) Str::uuid(),
                'user_id' => $user->id,
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'payment_status' => $paymentStatus,
                'payment_method' => $paymentMethod,
                'delivery_location_address' => $paymentDetails['delivery_location_address'] ?? null,
                'delivery_time_window' => $paymentDetails['delivery_time_window'] ?? null,
            ]);

            if ($transaction) {
                $transaction->update([
                    'meta' => array_merge($transaction->meta ?? [], ['order_id' => $order->id])
                ]);
            }

            foreach ($cartItems as $cartItem) {
                $lineTotal = bcmul((string) $cartItem->quantity, (string) $cartItem->product->price, 8);

                $orderItem = OrderItem::create([
                    'id' => (string) Str::uuid(),
                    'order_id' => $order->id,
                    'product_id' => $cartItem->product_id,
                    'vendor_id' => $cartItem->product->vendor_id,
                    'quantity' => $cartItem->quantity,
                    'unit_price' => $cartItem->product->price,
                    'total_price' => $lineTotal,
                    'vendor_status' => 'pending',
                ]);

                $vendor = User::find($cartItem->product->vendor_id);
                if ($vendor) {
                    $vendor->notify(new \App\Notifications\NewVendorOrderNotification($orderItem));
                }

                $this->inventoryService->adjustStock(
                    $cartItem->product,
                    'subtraction',
                    (float) $cartItem->quantity,
                    'order',
                    $order->id
                );
            }

            $this->cartService->clearCart($user);
            $user->notify(new \App\Notifications\OrderPlacedNotification($order));

            return $order;
        });
    }

    public function initiateLogisticsDispatch(Order $order): void
    {
        try {
            $this->logisticsService->calculateAndDispatch($order);
        } catch (\Exception $e) {
            Log::error('Logistics dispatch failed after order placement', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function confirmItemDelivery(User $user, string $orderItemId): OrderItem
    {
        return DB::transaction(function () use ($user, $orderItemId) {
            $item = OrderItem::where('id', $orderItemId)
                ->whereHas('order', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->firstOrFail();

            if (!in_array($item->vendor_status, ['dispatched', 'in_transit', 'out_for_delivery', 'arriving_soon', 'assigned', 'picked_up'])) {
                throw new InvalidOrderStateException(
                    'Order item must be in transit or dispatched before delivery can be confirmed.'
                );
            }

            $item->update([
                'vendor_status' => 'delivered',
                'delivered_at' => now(),
            ]);

            if ($this->escrowService) {
                $this->escrowService->releaseEscrowToVendor($item);
            }

            if ($this->referralService) {
                $this->referralService->processQualifyingOrder($user);
            }

            $order = $item->order;
            $allDelivered = $order->items()->where('vendor_status', '!=', 'delivered')->count() === 0;

            if ($allDelivered) {
                $order->update([
                    'status' => 'completed',
                    'payment_status' => 'completed',
                    'logistics_status' => 'delivered',
                ]);

                $this->settlementService->calculateAndRecordSettlement($order);
            }

            return $item->fresh();
        });
    }

    public function cancelOrder(User $user, string $orderId, ?string $reason = null): Order
    {
        return DB::transaction(function () use ($user, $orderId, $reason) {
            $order = $user->orders()->with('items.product')->findOrFail($orderId);

            if (!in_array($order->status, ['pending', 'placed'])) {
                throw new \DomainException('Only pending orders can be cancelled.');
            }

            $order->update([
                'status' => 'cancelled',
            ]);

            // Release escrow back to buyer for each item if paid with wallet
            if ($this->escrowService && $order->payment_method === 'wallet' && $order->payment_status === 'paid') {
                foreach ($order->items as $item) {
                    try {
                        $this->escrowService->refundEscrowToBuyer($item);
                    } catch (\Throwable $e) {
                        Log::warning('Escrow refund error on order cancellation', [
                            'item_id' => $item->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }

            // Restore inventory
            foreach ($order->items as $item) {
                $item->update(['vendor_status' => 'cancelled']);
                if ($item->product) {
                    $this->inventoryService->adjustStock(
                        $item->product,
                        'addition',
                        (float) $item->quantity,
                        'order_cancellation',
                        $order->id
                    );
                }
            }

            return $order->fresh(['items.product']);
        });
    }
}