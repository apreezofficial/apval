<?php

declare(strict_types=1);

namespace App\Exceptions\Ads;

use Exception;


class InvalidAdvertisementStateException extends Exception
{
    public function render(): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => $this->getMessage(),
        ], 422);
    }
}