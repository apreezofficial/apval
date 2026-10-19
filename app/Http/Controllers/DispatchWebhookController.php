<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Logistics\AreaConnectClient;
use App\Services\Logistics\DispatchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
 
class DispatchWebhookController
{
    public function __construct(
        private AreaConnectClient $client,
        private DispatchService $dispatchService
    ) {}

    public function handle(Request $request): JsonResponse
    {
        $rawBody = $request->getContent();
        $signature = $request->header('X-AreaConnect-Signature');

        // if (!$this->client->isValidWebhookSignature($rawBody, $signature)) {
        //     Log::warning('AreaConnect webhook: invalid signature', ['ip' => $request->ip()]);
        //     return response()->json(['message' => 'Unauthorized'], 401);
        // }

        $payload = $request->json()->all();

        try {
            $this->dispatchService->applyWebhookUpdate($payload);
        } catch (\InvalidArgumentException $e) {
            Log::warning('AreaConnect webhook: bad payload', ['error' => $e->getMessage()]);
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['status' => 'ok']);
    }
}