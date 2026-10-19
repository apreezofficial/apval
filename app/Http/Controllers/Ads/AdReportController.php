<?php

declare(strict_types=1);

namespace App\Http\Controllers\Ads;

use App\Models\Ads\Advertisement;
use App\Models\Ads\AdvertisementCampaign;
use App\Models\Ads\AdvertisementClick;
use App\Models\Ads\AdvertisementConversion;
use App\Models\Ads\AdvertisementImpression;
use App\Models\Ads\Advertiser;
use App\Services\Ads\AdTrackingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdReportController
{
    public function dashboard(Request $request): JsonResponse
    {
        /** @var Advertiser $advertiser */
        $advertiser = $request->attributes->get('advertiser');

        $adsQuery = Advertisement::query()->where('advertiser_id', $advertiser->id);
        $campaignIds = $advertiser->campaigns()->pluck('id');

        return response()->json([
            'status' => 'success',
            'data' => [
                'total_campaigns' => (clone $adsQuery)->count(),
                'active_campaigns' => (clone $adsQuery)->where('status', 'active')->count(),
                'scheduled_campaigns' => (clone $adsQuery)->where('status', 'scheduled')->count(),
                'completed_campaigns' => (clone $adsQuery)->where('status', 'completed')->count(),
                'pending_campaigns' => (clone $adsQuery)->where('status', 'pending_review')->count(),
                'total_spend' => AdvertisementCampaign::query()->where('advertiser_id', $advertiser->id)->sum('amount_spent'),
                'total_impressions' => AdvertisementImpression::query()->whereIn('campaign_id', $campaignIds)->count(),
                'total_clicks' => AdvertisementClick::query()->whereIn('campaign_id', $campaignIds)->count(),
                'total_conversions' => AdvertisementConversion::query()->whereIn('campaign_id', $campaignIds)->count(),
            ],
        ]);
    }

    public function campaignReport(Request $request, string $campaignId, AdTrackingService $trackingService): JsonResponse
    {
        $campaign = AdvertisementCampaign::query()->with('advertisement')->findOrFail($campaignId);

        $impressions = $campaign->impressions()->count();
        $clicks = $campaign->clicks()->count();
        $conversionsQuery = $campaign->conversions();

        if ($from = $request->date('from')) {
            $conversionsQuery->where('created_at', '>=', $from);
        }

        if ($to = $request->date('to')) {
            $conversionsQuery->where('created_at', '<=', $to);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'campaign' => $campaign->reference,
                'status' => $campaign->status,
                'budget' => $campaign->total_budget,
                'amount_spent' => $campaign->amount_spent,
                'impressions' => $impressions,
                'clicks' => $clicks,
                'ctr' => $trackingService->calculateCtr($clicks, $impressions),
                'conversions' => $conversionsQuery->count(),
                'revenue_generated' => $conversionsQuery->sum('value'),
            ],
        ]);
    }
}