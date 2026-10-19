<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Http\Requests\Agent\StoreDailyReportRequest;
use App\Http\Resources\Agent\AgentDailyReportResource;
use Illuminate\Http\Request;

class AgentDailyReportController extends Controller
{
    /**
     * Create or update the agent's report for a given date (§13).
     * One report per agent per day — upserts against the unique constraint.
     */
    public function store(StoreDailyReportRequest $request)
    {
        $agent = $request->user()->agentProfile()->firstOrFail();
        $data = $request->validated();

        $report = $agent->dailyReports()->updateOrCreate(
            ['report_date' => $data['report_date']],
            $data + ['territory' => $data['territory'] ?? $agent->territory]
        );

        return new AgentDailyReportResource($report);
    }

    public function index(Request $request)
    {
        $agent = $request->user()->agentProfile()->firstOrFail();

        $query = $agent->dailyReports()->orderByDesc('report_date');

        if ($request->filled('from')) {
            $query->whereDate('report_date', '>=', $request->query('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('report_date', '<=', $request->query('to'));
        }

        return AgentDailyReportResource::collection($query->paginate(30));
    }

    public function show(Request $request, string $id)
    {
        $agent = $request->user()->agentProfile()->firstOrFail();

        $report = $agent->dailyReports()->findOrFail($id);

        return new AgentDailyReportResource($report);
    }
}