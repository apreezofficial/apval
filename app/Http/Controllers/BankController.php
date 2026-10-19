<?php

namespace App\Http\Controllers;

use App\Services\Banks\BankDirectory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class BankController
{
    public function index(BankDirectory $banks): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $banks->listMonnifyBanks(),
        ]);
    }
}
