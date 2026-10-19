<?php

declare(strict_types=1);

namespace App\Http\Controllers\Commerce;

use App\Http\Requests\Commerce\AssignInspectionRequest;
use App\Http\Requests\Commerce\SubmitInspectionRequest;
use App\Services\Commerce\InspectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class InspectionController
{
    public function __construct(private InspectionService $inspectionService) {}

    public function assign(AssignInspectionRequest $request): JsonResponse
    {
        $data = $request->validated();

        $inspection = $this->inspectionService->assignInspector(
            $data['order_item_id'],
            $data['inspector_id']
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Inspector successfully assigned.',
            'data' => $inspection
        ]);
    }

    public function submit(SubmitInspectionRequest $request, string $id): JsonResponse
    {
        $data = $request->validated();

        /** @var \App\Models\User $inspector */
        $inspector = Auth::user();

        $inspection = $this->inspectionService->submitInspection(
            $inspector->id,
            $id,
            $data['evidence_urls'] ?? [],
            $data['notes'] ?? '',
            $data['passed']
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Inspection report submitted.',
            'data' => $inspection->load('orderItem')
        ]);
    }
}
