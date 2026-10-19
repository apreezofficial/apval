<?php

namespace App\Services\Webhook;

use App\Http\Controllers\Controller;
use App\Models\LogisticsRequest;
use App\Models\Order;
use App\Services\Logistics\AreaConnectClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AreaConnectWebhookController extends Controller
{
    public function __construct(
        private AreaConnectClient $areaConnectClient
    ) {}

    public function handle(Request $request)
    {
        // 1. Validate Webhook Signature (Security)
        $signature = $request->header('X-AreaConnect-Signature'); // Adjust header name if different
        $rawBody = $request->getContent();

        if (!$this->areaConnectClient->isValidWebhookSignature($rawBody, $signature)) {
            Log::warning('AreaConnect Webhook: Invalid signature received.');
            return response('Invalid signature', 401);
        }

        $payload = $request->json()->all();
        $jobId = $payload['job_id'] ?? null;
        $newStatus = $payload['status'] ?? null;

        if (!$jobId || !$newStatus) {
            return response('Missing job_id or status', 400);
        }

        // 2. Find the Logistics Request
        $logisticsRequest = LogisticsRequest::where('areaconnect_reference_id', $jobId)->first();

        if (!$logisticsRequest) {
            Log::warning('AreaConnect Webhook: Unknown job_id received.', ['job_id' => $jobId]);
            return response('Job not found', 404);
        }

        // 3. Map AreaConnect status to our internal Order status
        $mappedStatus = $this->mapStatus($newStatus);

        // 4. Update Logistics Request with raw payload and new status
        $logisticsRequest->update([
            'status' => $mappedStatus,
            'response_payload' => array_merge($logisticsRequest->response_payload ?? [], ['latest_webhook' => $payload]),
        ]);

        // 5. Update the Order's logistics status
        $logisticsRequest->order->update([
            'logistics_status' => $mappedStatus,
        ]);

        // Optional: Trigger internal events (e.g., notify buyer via Pusher/Firebase)
        // event(new OrderLogisticsUpdated($logisticsRequest->order, $mappedStatus));

        Log::info('AreaConnect Webhook processed successfully.', [
            'job_id' => $jobId, 
            'new_status' => $mappedStatus
        ]);

        // AreaConnect expects a 200 OK to stop retrying
        return response()->json(['success' => true]);
    }

    /**
     * Maps AreaConnect statuses to our internal Order logistics_status.
     */
    private function mapStatus(string $acStatus): string
    {
        return match (strtolower($acStatus)) {
            'created', 'assigned' => 'assigned',
            'picked_up' => 'picked_up',
            'in_cold_room', 'stored' => 'in_cold_room', // Section 4.B
            'in_transit' => 'in_transit',
            'arriving_soon' => 'arriving_soon',
            'delivered' => 'delivered',
            'failed', 'returned', 'cancelled' => 'failed',
            default => 'unknown',
        };
    }
}