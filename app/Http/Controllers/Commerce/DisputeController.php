<?php

namespace App\Http\Controllers\Commerce;

use App\Http\Controllers\Controller;
use App\Models\Dispute;
use App\Models\DisputeMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DisputeController extends Controller
{
    /**
     * List all disputes for the authenticated user.
     */
    public function index(): JsonResponse
    {
        $disputes = Dispute::where('user_id', Auth::id())
            ->with(['messages', 'order'])
            ->latest()
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $disputes,
        ]);
    }

    /**
     * Create a new dispute.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_id'    => 'required|exists:orders,id',
            'reason'      => 'required|string|max:255',
            'description' => 'nullable|string',
            'attachments' => 'nullable|array',
        ]);

        $dispute = Dispute::create([
            'user_id'     => Auth::id(),
            'order_id'    => $validated['order_id'],
            'reason'      => $validated['reason'],
            'description' => $validated['description'] ?? null,
            'status'      => 'open',
        ]);

        DisputeMessage::create([
            'dispute_id'  => $dispute->id,
            'user_id'     => Auth::id(),
            'from'        => 'customer',
            'message'     => $validated['description'] ?? $validated['reason'],
            'attachments' => $validated['attachments'] ?? null,
        ]);

        $dispute->load(['messages', 'order']);

        return response()->json([
            'status'   => 'success',
            'message'  => 'Dispute submitted successfully',
            'data'     => $dispute,
            'timeline' => $dispute->messages,
            ...$dispute->toArray(),
        ], 201);
    }

    /**
     * Show a specific dispute with its timeline messages.
     */
    public function show($id): JsonResponse
    {
        $dispute = Dispute::where('id', $id)
            ->where('user_id', Auth::id())
            ->with(['messages', 'order'])
            ->firstOrFail();

        $data = $dispute->toArray();
        $data['timeline'] = $dispute->messages;

        return response()->json([
            'status'   => 'success',
            'data'     => $dispute,
            'timeline' => $dispute->messages,
            ...$data,
        ]);
    }

    /**
     * Add a message / reply to an open dispute.
     */
    public function addMessage(Request $request, $id): JsonResponse
    {
        $dispute = Dispute::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $validated = $request->validate([
            'message'     => 'required|string',
            'attachments' => 'nullable|array',
        ]);

        $message = DisputeMessage::create([
            'dispute_id'  => $dispute->id,
            'user_id'     => Auth::id(),
            'from'        => 'customer',
            'message'     => $validated['message'],
            'attachments' => $validated['attachments'] ?? null,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Message sent',
            'data'    => $message,
        ], 201);
    }
}
