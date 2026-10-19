<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Ads;

use App\Models\Ads\Advertisement;
use App\Services\Ads\AdWorkflowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminAdvertisementController
{
    public function index(Request $request): JsonResponse
    {
        $advertisements = Advertisement::query()
            ->with(['advertiser', 'campaign'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->latest()
            ->paginate($request->integer('per_page', 20));

        return response()->json([
            'status' => 'success',
            'data' => $advertisements,
        ]);
    }

    public function show(Advertisement $advertisement): JsonResponse
    {
        $advertisement->load(['advertiser.user', 'campaign.target', 'campaign.placements', 'media']);

        return response()->json([
            'status' => 'success',
            'data' => $advertisement,
        ]);
    }

    public function approve(Request $request, Advertisement $advertisement, AdWorkflowService $workflowService): JsonResponse
    {
        $updated = $workflowService->approve($advertisement, $request->user());

        return response()->json([
            'status' => 'success',
            'message' => 'Advertisement approved.',
            'data' => $updated,
        ]);
    }

    public function reject(Request $request, Advertisement $advertisement, AdWorkflowService $workflowService): JsonResponse
    {
        $data = $request->validate(['reason' => ['required', 'string', 'max:500']]);

        $updated = $workflowService->reject($advertisement, $request->user(), $data['reason']);

        return response()->json([
            'status' => 'success',
            'message' => 'Advertisement rejected.',
            'data' => $updated,
        ]);
    }

    public function requestChanges(Request $request, Advertisement $advertisement, AdWorkflowService $workflowService): JsonResponse
    {
        $data = $request->validate(['reason' => ['required', 'string', 'max:500']]);

        $updated = $workflowService->requestChanges($advertisement, $request->user(), $data['reason']);

        return response()->json([
            'status' => 'success',
            'message' => 'Changes requested from advertiser.',
            'data' => $updated,
        ]);
    }

    public function pause(Advertisement $advertisement, AdWorkflowService $workflowService): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Advertisement paused.',
            'data' => $workflowService->pause($advertisement),
        ]);
    }

    public function resume(Advertisement $advertisement, AdWorkflowService $workflowService): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Advertisement resumed.',
            'data' => $workflowService->resume($advertisement),
        ]);
    }

    public function suspend(Request $request, Advertisement $advertisement, AdWorkflowService $workflowService): JsonResponse
    {
        $data = $request->validate(['reason' => ['nullable', 'string', 'max:500']]);

        $updated = $workflowService->suspend($advertisement, $request->user(), $data['reason'] ?? null);

        return response()->json([
            'status' => 'success',
            'message' => 'Advertisement suspended.',
            'data' => $updated,
        ]);
    }

    public function destroy(Advertisement $advertisement): JsonResponse
    {
        $advertisement->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Advertisement deleted.',
        ]);
    }
}