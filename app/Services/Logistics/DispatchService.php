<?php

declare(strict_types=1);

namespace App\Services\Logistics;

use App\Models\DispatchJob;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DispatchService
{
    private const STATUS_MAP = [
        'pending_assignment' => 'assigned',
        'assigned'           => 'assigned',
        'picked_up'          => 'picked_up',
        'in_transit'         => 'in_transit',
        'delivered'          => 'delivered',
        'completed'          => 'delivered',
        'cancelled'          => 'delivery_failed',
    ];

    private const TIMESTAMP_MAP = [
        'picked_up'  => 'picked_up_at',
        'in_transit' => 'in_transit_at',
        'delivered'  => 'delivered_at',
        'cancelled'  => 'failed_at',
    ];

    public function __construct(
        private AreaConnectClient $client,
        private SettlementService $settlementService
    ) {}

    /**
     * Process an inbound webhook from AreaConnect and update the dispatch job
     * and order item accordingly.
     */
    public function applyWebhookUpdate(array $payload): DispatchJob
    {
        $areaConnectOrderId = isset($payload['orderId']) ? (string) $payload['orderId'] : null;
        $newStatus = strtolower($payload['status'] ?? '');
        $referenceId = $payload['referenceId'] ?? $payload['externalRef'] ?? null;

        if (!$areaConnectOrderId || !$newStatus) {
            throw new \InvalidArgumentException('AreaConnect webhook missing orderId or status.');
        }

        // ✅ FIX: Added $payload to the `use` clause below
        return DB::transaction(function () use ($areaConnectOrderId, $newStatus, $referenceId, $payload) {
            
            // 1. Find the job by AreaConnect's orderId
            $job = DispatchJob::where('area_connect_job_id', $areaConnectOrderId)
                ->lockForUpdate()
                ->firstOrFail();

            $internalStatus = self::STATUS_MAP[$newStatus] ?? 'unknown';
            $timestampColumn = self::TIMESTAMP_MAP[$newStatus] ?? null;

            $jobUpdates = [
                'status'              => $newStatus,
                'last_status_message' => $payload['message'] ?? null,
                'raw_webhook'         => array_merge($job->raw_webhook ?? [], ['latest_update' => $payload]),
            ];

            if ($timestampColumn) {
                $jobUpdates[$timestampColumn] = now();
            }

            $job->update($jobUpdates);

            // 2. Mirror the mapped status onto the specific order item
            $item = $job->orderItem;
            $item->update(['vendor_status' => $internalStatus]);

            // 3. Update Parent Order status so the buyer sees the global status
            $order = $item->order;
            $order->update(['logistics_status' => $internalStatus]);

            // 4. 🔒 CRITICAL: Trigger settlement ONLY when ALL items in the order are delivered
            if (in_array($newStatus, ['delivered', 'completed'])) {
                $item->update(['delivered_at' => now()]);
                
                $undeliveredCount = $order->items()->where('vendor_status', '!=', 'delivered')->count();
                
                if ($undeliveredCount === 0) {
                    $order->update(['status' => 'completed']);
                    
                    // Trigger Settlement Logic (Section 7 of PDF)
                    $this->settlementService->calculateAndRecordSettlement($order);
                    
                    Log::info('Order fully delivered. Settlement calculated.', [
                        'order_id' => $order->id
                    ]);
                }
            }

            // 5. PDF Section 9: Exception Handling - Auto alert on failure
            if (in_array($newStatus, ['cancelled', 'failed', 'returned'])) {
                Log::error('AreaConnect Delivery Exception', [
                    'order_id'      => $order->id,
                    'job_id'        => $areaConnectOrderId,
                    'reason'        => $payload['message'] ?? 'Unknown',
                    'action_needed' => $order->cold_chain_required 
                        ? 'Return to warehouse/cold room (Perishable)' 
                        : 'Retry scheduling OR Return to warehouse'
                ]);
            }

            Log::info('AreaConnect webhook applied successfully', [
                'job_id'              => $areaConnectOrderId,
                'area_connect_status' => $newStatus,
                'internal_status'     => $internalStatus,
            ]);

            return $job->fresh(['orderItem.order']);
        });
    }
}