<?php

declare(strict_types=1);

namespace App\Http\Controllers\Commerce;

use App\Http\Requests\Commerce\DispatchOrderItemRequest;
use App\Models\OrderItem;
use App\Services\Commerce\VendorFulfillmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VendorOrderController
{
    public function __construct(private VendorFulfillmentService $fulfillmentService) {}

    public function index(Request $request): JsonResponse
    {
        /** @var \App\Models\User $vendor */
        $vendor = Auth::user();

        // Get order items belonging to this vendor
        $items = OrderItem::where('vendor_id', $vendor->id)
            ->with(['order', 'product'])
            ->latest()
            ->paginate(20);

        return response()->json(['status' => 'success', 'data' => $items]);
    }

    public function accept(string $itemId): JsonResponse
    {
        /** @var \App\Models\User $vendor */
        $vendor = Auth::user();
        $item = $this->fulfillmentService->acceptOrderItem($vendor, $itemId);

        return response()->json([
            'status' => 'success',
            'message' => 'Order item accepted successfully.',
            'data' => $item
        ]);
    }

    public function reject(string $itemId): JsonResponse
    {
        /** @var \App\Models\User $vendor */
        $vendor = Auth::user();
        $item = $this->fulfillmentService->rejectOrderItem($vendor, $itemId);

        return response()->json([
            'status' => 'success',
            'message' => 'Order item rejected, inventory reversed.',
            'data' => $item
        ]);
    }

    public function markReady(string $itemId): JsonResponse
    {
        /** @var \App\Models\User $vendor */
        $vendor = Auth::user();
        $item = $this->fulfillmentService->markReadyForInspection($vendor, $itemId);

        return response()->json([
            'status' => 'success',
            'message' => 'Product is prepared and staged for inspection.',
            'data' => $item
        ]);
    }

    public function dispatch(DispatchOrderItemRequest $request, string $itemId): JsonResponse
    {
        /** @var \App\Models\User $vendor */
        $vendor = Auth::user();

        $result = $this->fulfillmentService->dispatchOrderItem(
            $vendor,
            $itemId,
            $request->validated('pickup_address'),
            $request->validated('delivery_address')
        );

        return response()->json([
            'status'  => 'success',
            'message' => 'Order item successfully dispatched.',
            'data'    => [
                'order_item'   => $result['item'],
                'dispatch_job' => $result['dispatch_job'],
            ],
        ]);
    }
}
