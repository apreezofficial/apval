<?php

declare(strict_types=1);

namespace App\Http\Controllers\Ads;

use App\Enums\Ads\AdvertiserStatus;
use App\Http\Requests\Ads\ApplyAdvertiserRequest;
use App\Models\Ads\Advertiser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdvertiserController
{
    public function show(Request $request): JsonResponse
    {
        $advertiser = Advertiser::query()->where('user_id', $request->user()->id)->first();

        return response()->json([
            'status' => 'success',
            'data' => $advertiser,
        ]);
    }

    public function apply(ApplyAdvertiserRequest $request): JsonResponse
    {
        $data = $request->validated();

        $advertiser = Advertiser::query()->updateOrCreate(
            ['user_id' => $request->user()->id],
            [
                'business_name' => $data['business_name'],
                'industry' => $data['industry'] ?? null,
                'status' => AdvertiserStatus::Pending,
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Advertiser application submitted successfully.',
            'data' => $advertiser,
        ], 201);
    }
}