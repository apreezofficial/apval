<?php

namespace App\Http\Controllers;

use App\Services\Banks\AccountResolutionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountResolutionController
{
     public function __construct(private AccountResolutionService $resolver) {}

    public function resolve(Request $request): JsonResponse
    {
        $data = $request->validate([
            'bank_code' => ['required', 'string', 'max:20'],
            'account_number' => ['required', 'digits:10'],
        ]);

        try {
            $result = $this->resolver->resolve(
                $data['bank_code'],
                $data['account_number']
            );

            return response()->json([
                'status' => 'success',
                'data' => $result,
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Account resolution failed', [
                'bank_code' => $data['bank_code'],
                'account_number' => $data['account_number'],
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => 'error',
                'code' => 'INVALID_ACCOUNT',
                'message' => $e->getMessage() ?: 'Unable to verify account details. Please check the bank and account number.',
            ], 422);
        }
    }

    public function suggest(Request $request): JsonResponse
    {
        $data = $request->validate([
            'account_number' => ['required', 'digits:10'],
        ]);

        $result = $this->resolver->suggest($data['account_number']);

        return response()->json([
            'status' => 'success',
            'data' => $result,
        ]);
    }

}
