<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AgentKpiController extends Controller
{
    /**
     * Today's KPI snapshot, computed live from today's daily report (§11).
     */
    public function daily(Request $request)
    {
        $agent = $request->user()->agentProfile()->firstOrFail();

        $today = $agent->dailyReports()
            ->whereDate('report_date', now()->toDateString())
            ->first();

        return response()->json([
            'period' => 'daily',
            'date'   => now()->toDateString(),
            'data'   => $today ? [
                'calls'                 => $today->calls_count,
                'qualified_prospects'   => $today->qualified_prospects,
                'registrations'         => $today->registrations,
                'completed_onboardings' => $today->completed_registrations,
                'activated_users'       => $today->activated_users,
                'products_listed'       => $today->products_uploaded_count,
                'first_transactions'    => $today->first_time_transactions,
            ] : null,
            'targets' => [
                'calls'                 => '30-50',
                'qualified_prospects'   => '10-15',
                'registrations'         => '5-10',
                'completed_onboardings' => '3-5',
            ],
        ]);
    }

    /**
     * Weekly rollup computed live from the last 7 days of daily reports (§12).
     * Falls back to a cached AgentKpiSnapshot if present for the current week.
     */
    public function weekly(Request $request)
    {
        $agent = $request->user()->agentProfile()->firstOrFail();

        $start = now()->startOfWeek();
        $end = now()->endOfWeek();

        $reports = $agent->dailyReports()
            ->whereBetween('report_date', [$start->toDateString(), $end->toDateString()])
            ->get();

        return response()->json([
            'period'       => 'weekly',
            'period_start' => $start->toDateString(),
            'period_end'   => $end->toDateString(),
            'data'         => [
                'prospects_contacted'    => $reports->sum('calls_count'),
                'qualified_prospects'    => $reports->sum('qualified_prospects'),
                'registrations'          => $reports->sum('registrations'),
                'completed_onboardings'  => $reports->sum('completed_registrations'),
                'activated_users'        => $reports->sum('activated_users'),
                'products_listed'        => $reports->sum('products_uploaded_count'),
                'first_transactions'     => $reports->sum('first_time_transactions'),
                'follow_ups_completed'   => $reports->sum('customers_followed_up'),
            ],
        ]);
    }
}