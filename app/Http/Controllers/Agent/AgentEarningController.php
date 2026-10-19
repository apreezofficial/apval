<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Http\Resources\Agent\AgentEarningResource;
use Illuminate\Http\Request;

class AgentEarningController extends Controller
{
    public function show(Request $request)
    {
        $agent = $request->user()->agentProfile()->firstOrFail();

        $earnings = $agent->earnings()->latest()->paginate(30);

        return response()->json([
            'summary' => [
                'total_pending'  => (string) $agent->earnings()->where('status', 'pending')->sum('amount'),
                'total_approved' => (string) $agent->earnings()->where('status', 'approved')->sum('amount'),
                'total_paid'     => (string) $agent->earnings()->where('status', 'paid')->sum('amount'),
            ],
            'data' => AgentEarningResource::collection($earnings),
        ]);
    }
}