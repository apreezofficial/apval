<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Ads;

use App\Services\Ads\AdSettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminAdSettingController
{
    public function index(AdSettingService $settings): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $settings->all(),
        ]);
    }

    public function update(Request $request, AdSettingService $settings): JsonResponse
    {
        $data = $request->validate([
            'key' => ['required', 'string'],
            'value' => ['required'],
            'group' => ['nullable', 'string'],
        ]);

        $settings->set($data['key'], $data['value'], $data['group'] ?? null);

        return response()->json([
            'status' => 'success',
            'message' => 'Setting updated.',
        ]);
    }
}