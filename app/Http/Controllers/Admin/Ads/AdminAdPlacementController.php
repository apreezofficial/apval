<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Ads;

use App\Models\Ads\AdPlacement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminAdPlacementController
{
    public function index(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => AdPlacement::query()->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'key' => ['required', 'string', 'unique:ad_placements,key'],
            'name' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'requires_approval' => ['boolean'],
            'is_active' => ['boolean'],
            'price_per_day' => ['nullable', 'numeric'],
        ]);

        $placement = AdPlacement::create($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Placement created.',
            'data' => $placement,
        ], 201);
    }

    public function update(Request $request, AdPlacement $adPlacement): JsonResponse
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string'],
            'description' => ['nullable', 'string'],
            'requires_approval' => ['boolean'],
            'is_active' => ['boolean'],
            'price_per_day' => ['nullable', 'numeric'],
        ]);

        $adPlacement->update($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Placement updated.',
            'data' => $adPlacement,
        ]);
    }
}