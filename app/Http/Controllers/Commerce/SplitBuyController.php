<?php

declare(strict_types=1);

namespace App\Http\Controllers\Commerce;

use App\Models\SplitBuySession;
use App\Services\Commerce\SplitBuyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SplitBuyController
{
    public function __construct(
        private SplitBuyService $splitBuyService
    ) {}

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => ['required', 'string', 'exists:products,id'],
            'quantity' => ['required', 'numeric', 'min:0.1'],
            'initial_contribution' => ['nullable', 'numeric', 'min:0'],
        ]);

        $session = $this->splitBuyService->createSession(
            $request->user(),
            $request->product_id,
            (float) $request->quantity,
            (float) ($request->initial_contribution ?? 0)
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Split Buy group session created successfully.',
            'data' => $session,
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $session = SplitBuySession::with(['creator', 'product', 'contributions.user'])->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $session,
        ]);
    }

    public function contribute(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
        ]);

        $contribution = $this->splitBuyService->contribute(
            $request->user(),
            $id,
            (float) $request->amount
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Contribution added to Split Buy session.',
            'data' => $contribution,
        ]);
    }
}
