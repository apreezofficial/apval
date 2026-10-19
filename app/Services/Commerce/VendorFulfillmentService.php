<?php

declare(strict_types=1);

namespace App\Services\Commerce;

use App\Exceptions\Domain\InvalidOrderStateException;
use App\Models\Inspection;
use App\Models\OrderItem;
use App\Models\User;
use App\Services\Logistics\DispatchService;
use Illuminate\Support\Facades\DB;

class VendorFulfillmentService
{
    public function __construct(private DispatchService $dispatchService) {}

    public function acceptOrderItem(User $vendor, string $orderItemId): OrderItem
    {
        $item = OrderItem::where('id', $orderItemId)->where('vendor_id', $vendor->id)->firstOrFail();

        if ($item->vendor_status !== 'pending') {
            throw new InvalidOrderStateException(
                'Order item must be in pending status to be accepted.'
            );
        }

        $item->update([
            'vendor_status' => 'accepted',
            'accepted_at' => now(),
        ]);

        return $item;
    }

    public function rejectOrderItem(User $vendor, string $orderItemId): OrderItem
    {
        return DB::transaction(function () use ($vendor, $orderItemId) {
            $item = OrderItem::where('id', $orderItemId)->where('vendor_id', $vendor->id)->firstOrFail();

            if ($item->vendor_status !== 'pending') {
                throw new InvalidOrderStateException(
                    "Order item can only be rejected if it's currently pending."
                );
            }

            $item->update([
                'vendor_status' => 'rejected',
            ]);

            // Reverse inventory deduction lock
            /** @var \App\Services\Commerce\InventoryService $inventoryService */
            $inventoryService = app(InventoryService::class);
            $inventoryService->adjustStock($item->product, 'addition', floatval($item->quantity), 'order_rejected', $item->order_id);

            // Refund escrow funds back to buyer
            /** @var \App\Services\Wallet\EscrowService $escrowService */
            $escrowService = app(\App\Services\Wallet\EscrowService::class);
            $escrowService->refundEscrowToBuyer($item);

            // Notify buyer of refund
            if ($item->order && $item->order->user) {
                $item->order->user->notify(new \App\Notifications\EscrowRefundedNotification($item, (string)$item->total_price));
            }

            return $item;
        });
    }

    public function markReadyForInspection(User $vendor, string $orderItemId): OrderItem
    {
        return DB::transaction(function () use ($vendor, $orderItemId) {
            $item = OrderItem::where('id', $orderItemId)->where('vendor_id', $vendor->id)->firstOrFail();

            if ($item->vendor_status !== 'accepted') {
                throw new InvalidOrderStateException(
                    'Order item must be accepted before marking ready for inspection.'
                );
            }

            $item->update([
                'vendor_status' => 'ready_for_inspection',
                'prepared_at' => now(),
            ]);

            // Trigger inspection queue logic via establishing an empty Inspection record
            Inspection::create([
                'order_item_id' => $item->id,
                'status' => 'pending',
            ]);

            return $item;
        });
    }

    public function dispatchOrderItem(
        User   $vendor,
        string $orderItemId,
        string $pickupAddress,
        string $deliveryAddress
    ): array {
        return DB::transaction(function () use ($vendor, $orderItemId, $pickupAddress, $deliveryAddress) {
            $item = OrderItem::where('id', $orderItemId)
                ->where('vendor_id', $vendor->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($item->vendor_status !== 'inspected_approved') {
                throw new InvalidOrderStateException(
                    'Order item must be inspected and approved before it can be dispatched.'
                );
            }

            $item->update([
                'vendor_status' => 'dispatched',
                'dispatched_at' => now(),
            ]);

            $dispatchJob = $this->dispatchService->createForOrderItem(
                $item->load(['order.buyer', 'product']),
                $pickupAddress,
                $deliveryAddress
            );

            // Notify Buyer of Dispatch
            if ($item->order && $item->order->user) {
                $item->order->user->notify(new \App\Notifications\ItemDispatchedNotification($item));
            }

            return ['item' => $item->fresh(), 'dispatch_job' => $dispatchJob];
        });
    }
}
