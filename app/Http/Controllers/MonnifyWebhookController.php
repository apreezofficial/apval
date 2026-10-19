<?php

namespace App\Http\Controllers;


use App\Models\WebhookLog;
use App\Services\Webhook\MonnifyWebhookService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class MonnifyWebhookController 
{
    public function __construct(private MonnifyWebhookService $service) {}

    /**
     * Monnify webhook receiver.
     * IMPORTANT:
     *  - Always return 200 quickly (Monnify retries on non-2xx).
     *  - Log payload FIRST for traceability.
     */
    public function handle(Request $request)
    {
        $rawBody = $request->getContent();
        $signature = $request->header('monnify-signature') ?? $request->header('Monnify-Signature');

        $eventType = $request->input('eventType') ?? 'UNKNOWN';
        $eventData = $request->input('eventData') ?? [];

        // Extract refs (best-effort) so logs are searchable
        $paymentRef = data_get($eventData, 'paymentReference');
        $trxRef = data_get($eventData, 'transactionReference');

        // 1) Always log first (even if signature invalid)
        $log = WebhookLog::create([
            'provider' => 'monnify',
            'event_type' => $eventType,
            'payload' => $request->all(),
            'signature_header' => $signature,
            'signature_valid' => false,
            'processed' => false,
            'provider_reference' => $trxRef ?? $paymentRef,
        ]);

        // 2) Signature validation
        $isValid = $this->service->isValidSignature($rawBody, $signature);

        if (!$isValid) {
            $log->update(['signature_valid' => false]);
            // Return 200 so Monnify doesn't spam retries; you’ll inspect logs
            return response()->json(['status' => 'ignored', 'reason' => 'invalid_signature'], 200);
        }

        $log->update(['signature_valid' => true]);

        // 3) Process (idempotent)
        $this->service->process($log);

        return response()->json(['status' => 'ok'], 200);
    }
}
