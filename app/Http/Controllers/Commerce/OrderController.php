<?php

declare(strict_types=1);

namespace App\Http\Controllers\Commerce;

use App\Models\User;
use App\Services\Commerce\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController
{ 
    public function __construct(private OrderService $orderService) {}

    public function checkout(Request $request): JsonResponse
    {
        $request->validate([
            'payment_method' => 'nullable|string|in:wallet,bank_transfer',
            'delivery_location_address' => 'nullable|string',
            'delivery_time_window' => 'nullable|string',
            'items' => 'nullable|array',
            'items.*.product_id' => 'required_with:items|exists:products,id',
            'items.*.quantity' => 'required_with:items|numeric|min:0.01',
        ]);

        /** @var User $user */
        $user = Auth::user();
        
        $order = $this->orderService->placeOrder($user, [
            'method' => $request->payment_method ?? 'wallet',
            'delivery_location_address' => $request->delivery_location_address,
            'delivery_time_window' => $request->delivery_time_window ?? null,
            'items' => $request->items,
        ]);

        if ($request->filled('delivery_location_address')) {
            $this->orderService->initiateLogisticsDispatch($order);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Order placed successfully',
            'data' => $order->load('items.product')
        ], 201);
    }

    public function index(): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $orders = $user->orders()->with(['items.product', 'items.vendor'])->latest()->paginate(10);
        return response()->json(['status' => 'success', 'data' => $orders]);
    }

    public function show(string $id): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $order = $user->orders()->with(['items.product', 'items.vendor', 'items.dispatchJob'])->findOrFail($id);
        return response()->json(['status' => 'success', 'data' => $order]);
    }

    public function confirmDelivery(Request $request, string $itemId): JsonResponse
    {
        $request->validate([
            'confirmation_method' => 'nullable|in:otp,one_tap',
            'otp_code' => 'required_if:confirmation_method,otp|string',
        ]);

        /** @var User $user */
        $user = Auth::user();
        $item = $this->orderService->confirmItemDelivery($user, $itemId);

        return response()->json([
            'status' => 'success',
            'message' => 'Delivery confirmed.',
            'data' => $item->load('order', 'dispatchJob')
        ]);
    }

    public function cancel(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'reason' => 'nullable|string|max:255',
        ]);

        /** @var User $user */
        $user = Auth::user();
        $order = $this->orderService->cancelOrder($user, $id, $request->reason);

        return response()->json([
            'status' => 'success',
            'message' => 'Order cancelled successfully.',
            'data' => $order,
        ]);
    }
}