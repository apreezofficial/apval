<?php

declare(strict_types=1);

namespace App\Services\Logistics;

use App\Models\Order;
use App\Models\LogisticsRequest;
use Illuminate\Support\Facades\Log;

class LogisticsService
{ 
    public function __construct(
        private AreaConnectClient $areaConnectClient
    ) {}

    public function calculateAndDispatch(Order $order): LogisticsRequest
    {
        $requirements = $this->calculateRequirements($order);
        $order->update($requirements);

        return $this->dispatchToAreaConnect($order);
    }

    private function calculateRequirements(Order $order): array
    {
        $items = $order->items()->with('product')->get();
        $totalWeight = 0;
        $totalVolume = 0;
        $isPerishable = false;

        foreach ($items as $item) {
            $product = $item->product;
            $quantity = (float) $item->quantity;
            $totalWeight += ((float) ($product->weight_kg ?? 0.5)) * $quantity;
            $totalVolume += ((float) ($product->volume_m3 ?? 0.001)) * $quantity;
            if ($product->is_perishable) {
                $isPerishable = true;
            }
        }

        return [
            'estimated_weight_kg' => $totalWeight,
            'estimated_volume_m3' => $totalVolume,
            'is_perishable'       => $isPerishable,
            'transport_type'      => $this->assignTransportType($totalWeight, $totalVolume),
            'cold_chain_required' => $isPerishable,
        ];
    }

    private function assignTransportType(float $weight, float $volume): string
    {
        if ($weight > 500 || $volume > 2.0) return 'truck';
        if ($weight > 20 || $volume > 0.5) return 'van';
        return 'bike';
    }

        private function dispatchToAreaConnect(Order $order): LogisticsRequest
    {
        $buyer = $order->buyer;
        $firstItem = $order->items()->with(['product.vendor'])->first();
        
        $vendor = $firstItem?->product?->vendor;
        $pickupAddress = $vendor?->vendorProfile?->pickup_address ?? 'AgreonPay Warehouse';
        $deliveryAddress = $order->delivery_location_address ?? $buyer->address ?? '';
        
        $packageDescription = $order->items->map(fn($i) => "{$i->quantity}x {$i->product->name}")->implode(', ');
        $vehicleType = ucfirst($order->transport_type);

        $payload = [
            'partnerId'      => 145,
            'partnerName'    => 'AgreonPay',
            'pickupZone'     => '1',
            'dropoffZone'    => '2',
            'pickupAddress'  => $pickupAddress,
            'dropoffAddress' => $deliveryAddress,
            'item'           => $packageDescription,
            'vehicleType'    => in_array($vehicleType, ['Bike', 'Car', 'Van', 'Truck']) ? $vehicleType : 'Bike',
            'weightKg'       => (float) $order->estimated_weight_kg,
            'referenceId'    => (string) $order->id,
            'notes'          => $order->cold_chain_required ? 'PERISHABLE: Requires cold chain handling.' : 'Standard delivery.',
        ];

        $logisticsRequest = LogisticsRequest::create([
            'order_id'            => $order->id,
            'pickup_location'     => $pickupAddress,
            'dropoff_location'    => $deliveryAddress,
            'transport_type'      => $order->transport_type,
            'cold_chain_required' => $order->cold_chain_required,
            'request_payload'     => $payload,
            'status'              => 'initiated',
        ]);

        try {
            $response = $this->areaConnectClient->createDispatch($payload);
            $areaConnectOrderId = (string) $response['job_id']; // e.g., "385"

            // 1. Update the main Logistics Request
            $logisticsRequest->update([
                'areaconnect_reference_id' => $areaConnectOrderId,
                'tracking_code'            => $response['tracking_code'],
                'response_payload'         => $response,
                'status'                   => 'confirmed',
            ]);

            // 2. 🔒 CRITICAL: Create a DispatchJob record for EACH order item
            // This is what the webhook handler will look up!
            foreach ($order->items as $item) {
                \App\Models\DispatchJob::create([
                    'order_item_id'       => $item->id,
                    'area_connect_job_id' => $areaConnectOrderId, // "385"
                    'tracking_code'       => $response['tracking_code'],
                    'status'              => 'created',
                    'pickup_address'      => $payload['pickupAddress'],
                    'delivery_address'    => $payload['dropoffAddress'],
                    'estimated_delivery_at' => null,
                    'raw_webhook'         => $payload,
                ]);
            }

            // 3. Update Order status
            $order->update([
                'areaconnect_reference_id' => $areaConnectOrderId,
                'logistics_status'         => 'assigned',
            ]);

            Log::info('AreaConnect dispatch successful, DispatchJobs created', [
                'order_id' => $order->id,
                'area_connect_order_id' => $areaConnectOrderId,
                'items_count' => $order->items->count(),
            ]);

        } catch (\Exception $e) {
            Log::error('AreaConnect Dispatch Failed', ['order_id' => $order->id, 'error' => $e->getMessage()]);
            $logisticsRequest->update(['status' => 'error', 'error_message' => $e->getMessage()]);
        }

        return $logisticsRequest->fresh();
    }

    private function extractArea(string $address): string
    {
        // Simple fallback: return full address if no specific area parsing is implemented
        return $address; 
    }
}