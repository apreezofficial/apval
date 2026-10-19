<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Agent\AgentEarningResource;
use App\Models\Agent\AgentEarning;
use Illuminate\Http\Request;

class AgentEarningAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = AgentEarning::with('agent')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        return AgentEarningResource::collection($query->paginate(30));
    }

    public function approve(string $id)
    {
        $earning = AgentEarning::findOrFail($id);
        $earning->update(['status' => AgentEarning::STATUS_APPROVED]);

        return new AgentEarningResource($earning->fresh());
    }

    public function markPaid(string $id)
    {
        $earning = AgentEarning::findOrFail($id);
        $earning->update([
            'status'  => AgentEarning::STATUS_PAID,
            'paid_at' => now(),
        ]);

        return new AgentEarningResource($earning->fresh());
    }
}