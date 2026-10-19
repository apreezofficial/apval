<?php

declare(strict_types=1);

namespace App\Http\Controllers\Ads;

use App\Enums\Ads\AdvertisementStatus;
use App\Enums\Ads\ConversionType;
use App\Models\Ads\AdvertisementCampaign;
use App\Services\Ads\AdTrackingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdTrackingController
{
    public function impression(Request $request, AdvertisementCampaign $campaign, AdTrackingService $trackingService): JsonResponse
    {
        if ($campaign->status !== AdvertisementStatus::Active) {
            return response()->json([
                'status' => 'error',
                'message' => 'Campaign is not active.',
            ], 422);
        }

        $trackingService->recordImpression($campaign, $request, $request->input('placement_id'));

        return response()->json([
            'status' => 'success',
            'message' => 'Impression recorded.',
        ]);
    }

    public function click(Request $request, AdvertisementCampaign $campaign, AdTrackingService $trackingService): JsonResponse
    {
        if ($campaign->status !== AdvertisementStatus::Active) {
            return response()->json([
                'status' => 'error',
                'message' => 'Campaign is not active.',
            ], 422);
        }

        $trackingService->recordClick($campaign, $request, $request->input('placement_id'));

        return response()->json([
            'status' => 'success',
            'message' => 'Click recorded.',
        ]);
    }

    public function conversion(Request $request, AdvertisementCampaign $campaign, AdTrackingService $trackingService): JsonResponse
    {
        $data = $request->validate([
            'conversion_type' => ['required', Rule::enum(ConversionType::class)],
            'reference_id' => ['nullable', 'string'],
            'value' => ['nullable', 'numeric'],
        ]);

        $trackingService->recordConversion(
            $campaign,
            ConversionType::from($data['conversion_type']),
            $request->user()?->id,
            $data['reference_id'] ?? null,
            $data['value'] ?? null,
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Conversion recorded.',
        ]);
    }
}