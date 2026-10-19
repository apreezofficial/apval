<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Ads;

use App\Models\Ads\AdPricingModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminAdPricingController
{
    public function index(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => AdPricingModel::query()->with('placement')->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'placement_id' => ['nullable', 'uuid', 'exists:ad_placements,id'],
            'category' => ['nullable', 'string'],
            'pricing_model' => ['required', 'in:cpm,cpc,sponsored_listing'],
            'rate' => ['required', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        $pricing = AdPricingModel::create($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Pricing rule created.',
            'data' => $pricing,
        ], 201);
    }

    public function update(Request $request, AdPricingModel $adPricingModel): JsonResponse
    {
        $data = $request->validate([
            'placement_id' => ['nullable', 'uuid', 'exists:ad_placements,id'],
            'category' => ['nullable', 'string'],
            'rate' => ['sometimes', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        $adPricingModel->update($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Pricing rule updated.',
            'data' => $adPricingModel,
        ]);
    }

    public function destroy(AdPricingModel $adPricingModel): JsonResponse
    {
        $adPricingModel->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Pricing rule deleted.',
        ]);
    }
}