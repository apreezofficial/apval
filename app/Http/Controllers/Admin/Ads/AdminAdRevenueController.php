<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Ads;

use App\Models\Ads\AdvertisementCampaign;
use App\Models\Ads\AdvertisementClick;
use App\Models\Ads\AdvertisementConversion;
use App\Models\Ads\AdvertisementImpression;
use App\Models\Ads\AdvertisementPayment;
use App\Models\Ads\Advertiser;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class AdminAdRevenueController
{
    public function revenue(): JsonResponse
    {
        $successful = AdvertisementPayment::query()->where('status', 'successful');

        return response()->json([
            'status' => 'success',
            'data' => [
                'total_revenue' => (clone $successful)->sum('net_revenue'),
                'today_revenue' => (clone $successful)->whereDate('created_at', today())->sum('net_revenue'),
                'weekly_revenue' => (clone $successful)->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->sum('net_revenue'),
                'monthly_revenue' => (clone $successful)->whereMonth('created_at', now()->month)->sum('net_revenue'),
            ],
        ]);
    }

    public function analytics(): JsonResponse
    {
        $impressions = AdvertisementImpression::query()->count();
        $clicks = AdvertisementClick::query()->count();

        $topAdvertisers = AdvertisementPayment::query()
            ->select('advertiser_id', DB::raw('SUM(amount) as total_spent'))
            ->where('status', 'successful')
            ->groupBy('advertiser_id')
            ->orderByDesc('total_spent')
            ->limit(10)
            ->with('advertiser')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'total_campaigns' => AdvertisementCampaign::query()->count(),
                'active_campaigns' => AdvertisementCampaign::query()->where('status', 'active')->count(),
                'total_advertisers' => Advertiser::query()->count(),
                'total_impressions' => $impressions,
                'total_reach' => AdvertisementImpression::query()->distinct('user_id')->count('user_id'),
                'total_clicks' => $clicks,
                'average_ctr' => $impressions > 0 ? round(($clicks / $impressions) * 100, 2) : 0,
                'total_conversions' => AdvertisementConversion::query()->count(),
                'top_advertisers' => $topAdvertisers,
            ],
        ]);
    }
}