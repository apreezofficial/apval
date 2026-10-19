<?php

declare(strict_types=1);

namespace App\Http\Controllers\Ads;

use App\Enums\Ads\AdvertisementStatus;
use App\Enums\Ads\PricingModel;
use App\Http\Requests\Ads\StoreAdvertisementRequest;
use App\Http\Requests\Ads\UpdateAdvertisementRequest;
use App\Http\Requests\Ads\UpdateCampaignRequest;
use App\Http\Requests\Ads\UpdatePlacementsRequest;
use App\Http\Requests\Ads\UpdateTargetingRequest;
use App\Models\Ads\Advertisement;
use App\Models\Ads\AdvertisementCampaign;
use App\Models\Ads\AdvertisementTarget;
use App\Models\Ads\Advertiser;
use App\Services\Ads\AdPricingService;
use App\Services\Ads\AdWorkflowService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class AdvertisementController
{
    public function index(Request $request): JsonResponse
    {
        /** @var Advertiser $advertiser */
        $advertiser = $request->attributes->get('advertiser');

        $advertisements = Advertisement::query()
            ->with(['media', 'campaign.target', 'campaign.placements'])
            ->where('advertiser_id', $advertiser->id)
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return response()->json([
            'status' => 'success',
            'data' => $advertisements,
        ]);
    }

    public function show(Request $request, Advertisement $advertisement): JsonResponse
    {
        Gate::authorize('view', $advertisement);

        $advertisement->load(['media', 'campaign.target', 'campaign.placements']);

        return response()->json([
            'status' => 'success',
            'data' => $advertisement,
        ]);
    }

    public function store(StoreAdvertisementRequest $request): JsonResponse
    {
        /** @var Advertiser $advertiser */
        $advertiser = $request->attributes->get('advertiser');

        $advertisement = Advertisement::create([
            ...$request->validated(),
            'advertiser_id' => $advertiser->id,
            'user_id' => $request->user()->id,
            'status' => AdvertisementStatus::Draft,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Advertisement draft created.',
            'data' => $advertisement,
        ], 201);
    }

    public function update(UpdateAdvertisementRequest $request, Advertisement $advertisement): JsonResponse
    {
        Gate::authorize('update', $advertisement);

        if (! $advertisement->isEditable()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Advertisement cannot be edited in its current status.',
            ], 422);
        }

        $advertisement->update($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Advertisement updated.',
            'data' => $advertisement,
        ]);
    }

    public function updateTargeting(UpdateTargetingRequest $request, Advertisement $advertisement): JsonResponse
    {
        Gate::authorize('update', $advertisement);

        $campaign = $advertisement->campaign ?? $this->initCampaign($advertisement);

        $target = AdvertisementTarget::query()->updateOrCreate(
            ['campaign_id' => $campaign->id],
            $request->validated()
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Target audience saved.',
            'data' => $target,
        ]);
    }

    public function updatePlacements(UpdatePlacementsRequest $request, Advertisement $advertisement): JsonResponse
    {
        Gate::authorize('update', $advertisement);

        $campaign = $advertisement->campaign ?? $this->initCampaign($advertisement);
        $campaign->placements()->sync($request->validated()['placement_ids']);

        return response()->json([
            'status' => 'success',
            'message' => 'Placements saved.',
            'data' => $campaign->load('placements'),
        ]);
    }

    public function updateCampaign(
        UpdateCampaignRequest $request,
        Advertisement $advertisement,
        AdPricingService $pricingService,
    ): JsonResponse {
        Gate::authorize('update', $advertisement);

        $data = $request->validated();

        $start = Carbon::parse("{$data['start_date']} {$data['start_time']}");
        $end = Carbon::parse("{$data['end_date']} {$data['end_time']}");

        $budget = $pricingService->resolveBudget(
            PricingModel::from($data['pricing_model']),
            $data['package_id'] ?? null,
            isset($data['daily_budget']) ? (float) $data['daily_budget'] : null,
            $start,
            $end,
        );

        $campaign = $advertisement->campaign ?? $this->initCampaign($advertisement);

        $campaign->update([
            'objective' => $data['objective'],
            'pricing_model' => $data['pricing_model'],
            'package_id' => $data['package_id'] ?? null,
            'daily_budget' => $budget['daily_budget'],
            'total_budget' => $budget['total_budget'],
            'start_date' => $start,
            'end_date' => $end,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Campaign details saved.',
            'data' => $campaign,
        ]);
    }

    public function preview(Request $request, Advertisement $advertisement): JsonResponse
    {
        Gate::authorize('view', $advertisement);

        $advertisement->load('media');

        return response()->json([
            'status' => 'success',
            'data' => [
                'label' => 'SPONSORED',
                'title' => $advertisement->title,
                'description' => $advertisement->description,
                'media' => $advertisement->media,
                'cta' => $advertisement->cta,
            ],
        ]);
    }

    public function summary(Request $request, Advertisement $advertisement, AdPricingService $pricingService): JsonResponse
    {
        Gate::authorize('view', $advertisement);

        $advertisement->load(['campaign.target', 'campaign.placements', 'media']);
        $campaign = $advertisement->campaign;

        return response()->json([
            'status' => 'success',
            'data' => [
                'advertisement_type' => $advertisement->type,
                'title' => $advertisement->title,
                'media' => $advertisement->media,
                'target_audience' => $campaign?->target,
                'placements' => $campaign?->placements,
                'objective' => $campaign?->objective,
                'start_date' => $campaign?->start_date,
                'end_date' => $campaign?->end_date,
                'daily_budget' => $campaign?->daily_budget,
                'total_budget' => $campaign?->total_budget,
                'estimated_reach' => $campaign ? $pricingService->estimateReach((float) $campaign->total_budget) : 0,
                'total_cost' => $campaign?->total_budget,
            ],
        ]);
    }

    public function pause(Request $request, Advertisement $advertisement, AdWorkflowService $workflowService): JsonResponse
    {
        Gate::authorize('update', $advertisement);

        return response()->json([
            'status' => 'success',
            'message' => 'Campaign paused.',
            'data' => $workflowService->pause($advertisement),
        ]);
    }

    public function resume(Request $request, Advertisement $advertisement, AdWorkflowService $workflowService): JsonResponse
    {
        Gate::authorize('update', $advertisement);

        return response()->json([
            'status' => 'success',
            'message' => 'Campaign resumed.',
            'data' => $workflowService->resume($advertisement),
        ]);
    }

    public function cancel(Request $request, Advertisement $advertisement, AdWorkflowService $workflowService): JsonResponse
    {
        Gate::authorize('update', $advertisement);

        return response()->json([
            'status' => 'success',
            'message' => 'Campaign cancelled.',
            'data' => $workflowService->cancel($advertisement),
        ]);
    }

    public function duplicate(Request $request, Advertisement $advertisement): JsonResponse
    {
        Gate::authorize('view', $advertisement);

        $clone = DB::transaction(function () use ($advertisement) {
            $new = $advertisement->replicate(['reference', 'status', 'reviewed_by', 'reviewed_at']);
            $new->status = AdvertisementStatus::Draft;
            $new->save();

            foreach ($advertisement->media as $media) {
                $new->media()->create($media->only([
                    'type', 'path', 'thumbnail_path', 'mime_type',
                    'size_bytes', 'width', 'height', 'duration_seconds', 'order',
                ]));
            }

            return $new;
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Advertisement duplicated.',
            'data' => $clone,
        ], 201);
    }

    public function destroy(Request $request, Advertisement $advertisement): JsonResponse
    {
        Gate::authorize('delete', $advertisement);

        $deletable = in_array($advertisement->status, [
            AdvertisementStatus::Draft,
            AdvertisementStatus::Rejected,
            AdvertisementStatus::Cancelled,
        ], true);

        if (! $deletable) {
            return response()->json([
                'status' => 'error',
                'message' => 'Only draft, rejected or cancelled advertisements can be deleted.',
            ], 422);
        }

        $advertisement->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Advertisement deleted.',
        ]);
    }

    protected function initCampaign(Advertisement $advertisement): AdvertisementCampaign
    {
        return AdvertisementCampaign::create([
            'advertisement_id' => $advertisement->id,
            'advertiser_id' => $advertisement->advertiser_id,
            'status' => AdvertisementStatus::Draft,
        ]);
    }
}