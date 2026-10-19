<?php

declare(strict_types=1);

namespace App\Http\Controllers\Commerce;

use App\Services\Logistics\DispatchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class TrackingController
{
    public function __construct(private DispatchService $dispatchService) {}

    public function show(string $itemId): JsonResponse
    {
        /** @var \App\Models\User $buyer */
        $buyer = Auth::user();

        $item = $this->dispatchService->getTrackingForBuyer($buyer, $itemId);
        $job  = $item->dispatchJob;

        if (!$job) {
            return response()->json([
                'status'  => 'success',
                'message' => 'No dispatch job found for this item yet.',
                'data'    => [
                    'order_item_id' => $item->id,
                    'vendor_status' => $item->vendor_status,
                    'dispatch_job'  => null,
                ],
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data'   => [
                'order_item_id'       => $item->id,
                'product'             => $item->product?->name,
                'vendor_status'       => $item->vendor_status,
                'tracking_code'       => $job->tracking_code,
                'dispatch_status'     => $job->status,
                'last_location'       => $job->last_location,
                'last_status_message' => $job->last_status_message,
                'estimated_delivery'  => $job->estimated_delivery_at?->toDateTimeString(),
                'timeline'            => [
                    'dispatched'       => $item->dispatched_at?->toDateTimeString(),
                    'picked_up'        => $job->picked_up_at?->toDateTimeString(),
                    'in_transit'       => $job->in_transit_at?->toDateTimeString(),
                    'out_for_delivery' => $job->out_for_delivery_at?->toDateTimeString(),
                    'delivered'        => $job->delivered_at?->toDateTimeString(),
                    'failed'           => $job->failed_at?->toDateTimeString(),
                ],
            ],
        ]);
    }
}
