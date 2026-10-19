<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Ads;

use App\Enums\Ads\AdvertiserStatus;
use App\Models\Ads\Advertiser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminAdvertiserController
{
    public function index(Request $request): JsonResponse
    {
        $advertisers = Advertiser::query()
            ->with('user')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->latest()
            ->paginate($request->integer('per_page', 20));

        return response()->json([
            'status' => 'success',
            'data' => $advertisers,
        ]);
    }

    public function approve(Request $request, Advertiser $advertiser): JsonResponse
    {
        $advertiser->update([
            'status' => AdvertiserStatus::Approved,
            'approved_at' => now(),
            'approved_by' => $request->user()->id,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Advertiser approved.',
            'data' => $advertiser,
        ]);
    }

    public function reject(Request $request, Advertiser $advertiser): JsonResponse
    {
        $data = $request->validate(['reason' => ['required', 'string', 'max:500']]);

        $advertiser->update([
            'status' => AdvertiserStatus::Rejected,
            'rejection_reason' => $data['reason'],
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Advertiser rejected.',
            'data' => $advertiser,
        ]);
    }

    public function suspend(Advertiser $advertiser): JsonResponse
    {
        $advertiser->update(['status' => AdvertiserStatus::Suspended]);

        return response()->json([
            'status' => 'success',
            'message' => 'Advertiser suspended.',
            'data' => $advertiser,
        ]);
    }
}