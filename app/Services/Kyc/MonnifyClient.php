<?php

namespace App\Services\Kyc;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class MonnifyClient
{
    public function token(): string
    {
        return Cache::remember('monnify_access_token', 55 * 60, function () {

            $apiKey  = config('services.monnify.api_key');
            $secret  = config('services.monnify.secret_key');
            $baseUrl = rtrim(config('services.monnify.base_url'), '/');

            if (!$apiKey || !$secret) {
                throw new \RuntimeException('Monnify keys missing in env/config.');
            }

            $basic = base64_encode($apiKey . ':' . $secret);

            $res = Http::timeout(20)
                ->acceptJson()
                ->withHeaders(['Authorization' => "Basic {$basic}"])
                ->post("{$baseUrl}/api/v1/auth/login");

            $json = $res->json();

            if (!$res->ok() || !($json['requestSuccessful'] ?? false)) {
                $status  = $res->status();
                $respMsg = $json['responseMessage'] ?? ($json['error'] ?? 'Unknown error');
                throw new \RuntimeException("Monnify auth failed (HTTP {$status}): {$respMsg}");
            }

            $token = $json['responseBody']['accessToken'] ?? null;
            if (!$token) {
                throw new \RuntimeException('Monnify auth failed: accessToken missing.');
            }

            // If expiresIn exists, override cache TTL (minus 60s safety)
            $expiresIn = (int) ($json['responseBody']['expiresIn'] ?? 0);
            if ($expiresIn > 120) {
                Cache::put('monnify_access_token', $token, $expiresIn - 60);
                return $token;
            }

            return $token;
        });
    }

    public function post(string $path, array $payload): array
    {
        $baseUrl = rtrim(config('services.monnify.base_url'), '/');
        $token = $this->token();

        $res = Http::timeout(20)
            ->acceptJson()
            ->withToken($token)
            ->post("{$baseUrl}{$path}", $payload);

        if ($res->status() === 401) {
            Cache::forget('monnify_access_token');
            $res = Http::timeout(20)
                ->acceptJson()
                ->withToken($this->token())
                ->post("{$baseUrl}{$path}", $payload);
        }

        return [$res->status(), $res->json()];
    }

    public function validateAccount(string $bankCode, string $accountNumber): array
    {
        [$status, $json] = $this->get(
            '/api/v2/disbursements/account/validate',
            [
                'accountNumber' => $accountNumber,
                'bankCode' => $bankCode
            ]
        );

        if ($status === 0 || $status >= 500) {
            throw new \Exception('Monnify unavailable');
        }

        if (!($json['requestSuccessful'] ?? false)) {
            throw new \Exception($json['responseMessage'] ?? 'Account validation failed');
        }

        return $json['responseBody'];
    }
    public function get(string $path, array $query = []): array
    {
        $baseUrl = rtrim(config('services.monnify.base_url'), '/');

        $token = $this->token();

        $res = \Illuminate\Support\Facades\Http::timeout(15)
            ->acceptJson()
            ->withToken($token)
            ->get($baseUrl . $path, $query);

        if ($res->status() === 401) {

            // token expired → refresh
            \Illuminate\Support\Facades\Cache::forget('monnify_access_token');

            $res = \Illuminate\Support\Facades\Http::timeout(15)
                ->acceptJson()
                ->withToken($this->token())
                ->get($baseUrl . $path, $query);
        }

        return [$res->status(), $res->json()];
    }

    public function walletBalance(string $walletAccountNumber): array
    {
        $path = config('services.monnify.wallet_balance_path');

        if (!$path) {
            throw new \RuntimeException('Monnify wallet balance path not configured.');
        }

        [$status, $json] = $this->get("{$path}/{$walletAccountNumber}");

        if ($status === 0 || $status >= 500) {
            throw new \RuntimeException('Monnify wallet balance unavailable.');
        }

        if (!($json['requestSuccessful'] ?? false)) {
            throw new \RuntimeException($json['responseMessage'] ?? 'Unable to fetch Monnify wallet balance.');
        }

        return $json['responseBody'] ?? [];
    }
}
