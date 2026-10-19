<?php

declare(strict_types=1);

namespace App\Services\Logistics;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AreaConnectClient
{
    private string $baseUrl;
    private string $apiKey;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('services.areaconnect.base_url'), '/');
        $this->apiKey = trim((string) config('services.areaconnect.api_key'));
    }

    /**
     * Create a new delivery order with AreaConnect.
     * Based on the provided JS example: POST /api/partner/orders
     */
        public function createDispatch(array $payload): array
    {
        // ✅ CORRECT URL based on your JS example (No /public/ in the path)
        $url = "https://www.areaconnect.com.ng/api/partner/orders"; 
        
        Log::info('AreaConnect Create Order Request', ['url' => $url, 'payload' => $payload]);

        $response = Http::retry(3, 200)
            ->timeout(20)
            ->withHeaders($this->authHeaders())
            ->post($url, $payload);

        Log::info('AreaConnect API Response', [
            'status' => $response->status(),
            'body_preview' => substr($response->body(), 0, 500), // Log first 500 chars to avoid massive HTML logs
        ]);

        // If it returns HTML again, it means the route is still wrong or requires a different subdomain
        if (str_contains($response->body(), '<!DOCTYPE html>')) {
            throw new \RuntimeException('AreaConnect returned HTML instead of JSON. The API endpoint URL is incorrect.');
        }

        if ($response->failed()) {
            Log::error('AreaConnect createDispatch failed', [
                'status'  => $response->status(),
                'body'    => $response->body(),
            ]);
            throw new \RuntimeException(
                'AreaConnect order creation failed: ' . ($response->json('error') ?? $response->body())
            );
        }

        $json = $response->json();
        
        // Parse the successful response
        if (isset($json['orderId']) || isset($json['id']) || isset($json['data']['orderId'])) {
            $data = $json['data'] ?? $json;
            return [
                'job_id'        => (string) ($data['orderId'] ?? $data['id']),
                'tracking_code' => $data['trackingUrl'] ?? null,
                'status'        => $data['status'] ?? 'PENDING_ASSIGNMENT',
                'price'         => $data['price'] ?? 0,
            ];
        }

        throw new \RuntimeException('AreaConnect returned an unexpected format: ' . json_encode($json));
    }

    private function authHeaders(): array
    {
        return [
            'X-Area-Key'   => $this->apiKey, // ✅ Correct Header
            'Accept'       => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    /**
     * Validate an inbound webhook signature from AreaConnect.
     * Secret is sha256(raw_api_key). Header format: sha256=<hmac_hex>
     */
    public function isValidWebhookSignature(string $rawBody, ?string $signatureHeader): bool
    {
        if (!$signatureHeader || empty($this->apiKey)) {
            return false;
        }

        $signingSecret = hash('sha256', $this->apiKey);
        $expected = 'sha256=' . hash_hmac('sha256', $rawBody, $signingSecret);

        return hash_equals($expected, trim($signatureHeader));
    }

}