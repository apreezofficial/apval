<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Agent\AssignTerritoryRequest;
use App\Http\Requests\Admin\Agent\CreateAgentRequest;
use App\Http\Requests\Admin\Agent\UpdateAgentStatusRequest;
use App\Http\Resources\Agent\AgentProfileResource;
use App\Models\Agent\AgentEarning;
use App\Models\Agent\AgentProfile;
use App\Models\Agent\Lead;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AgentAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = AgentProfile::with('user')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('territory')) {
            $query->where('territory', 'like', '%' . $request->query('territory') . '%');
        }

        return AgentProfileResource::collection($query->paginate(20));
    }

    public function show(string $id)
    {
        $agent = AgentProfile::with(['user', 'manager'])->findOrFail($id);

        return new AgentProfileResource($agent);
    }

    /**
     * Onboard a new Market Agent (staff member) — creates both the
     * underlying User account (role=agent) and its AgentProfile.
     */
    public function store(CreateAgentRequest $request)
    {
        $data = $request->validated();

        $agent = DB::transaction(function () use ($data) {
            $user = User::create([
                'first_name' => $data['first_name'],
                'last_name'  => $data['last_name'],
                'email'      => $data['email'],
                'phone'      => $data['phone'],
                'password'   => Hash::make($data['password']),
                'role'       => 'agent',
                'status'     => 'active',
                'email_verified_at' => now(),
            ]);

            return AgentProfile::create([
                'user_id'    => $user->id,
                'agent_code' => 'AG-' . strtoupper(Str::random(6)),
                'territory'  => $data['territory'] ?? null,
                'assigned_manager_id' => $data['assigned_manager_id'] ?? null,
                'status'     => 'active',
                'onboarded_at' => now(),
            ]);
        });

        return new AgentProfileResource($agent->load('user'));
    }

    public function assignTerritory(AssignTerritoryRequest $request, string $id)
    {
        $agent = AgentProfile::findOrFail($id);
        $agent->update($request->validated());

        return new AgentProfileResource($agent->fresh());
    }

    public function updateStatus(UpdateAgentStatusRequest $request, string $id)
    {
        $agent = AgentProfile::findOrFail($id);
        $agent->update(['status' => $request->validated('status')]);

        return new AgentProfileResource($agent->fresh());
    }

    /**
     * Per-agent performance detail (§12 WEEKLY PERFORMANCE KPI).
     */
    public function performance(string $id)
    {
        $agent = AgentProfile::findOrFail($id);

        $leads = $agent->leads();
        $earnings = $agent->earnings();

        return response()->json([
            'agent_code' => $agent->agent_code,
            'territory'  => $agent->territory,
            'leads' => [
                'total'      => $leads->count(),
                'hot'        => (clone $leads)->where('classification', 'hot')->count(),
                'warm'       => (clone $leads)->where('classification', 'warm')->count(),
                'cold'       => (clone $leads)->where('classification', 'cold')->count(),
                'activated'  => (clone $leads)->where('classification', 'activated')->count(),
            ],
            'earnings' => [
                'total_pending'  => (string) (clone $earnings)->where('status', 'pending')->sum('amount'),
                'total_approved' => (string) (clone $earnings)->where('status', 'approved')->sum('amount'),
                'total_paid'     => (string) (clone $earnings)->where('status', 'paid')->sum('amount'),
            ],
            'recent_reports' => $agent->dailyReports()->latest('report_date')->limit(7)->get(),
        ]);
    }

    /**
     * Cross-agent monitoring dashboard (§19 MANAGEMENT MONITORING).
     */
    public function dashboard()
    {
        return response()->json([
            'total_agents'          => AgentProfile::count(),
            'active_agents'         => AgentProfile::where('status', 'active')->count(),
            'total_leads'           => Lead::count(),
            'activated_leads'       => Lead::where('classification', 'activated')->count(),
            'total_earnings_paid'   => (string) AgentEarning::where('status', 'paid')->sum('amount'),
            'total_earnings_pending'=> (string) AgentEarning::where('status', 'pending')->sum('amount'),
            'top_agents' => AgentProfile::withCount(['leads as activated_leads_count' => function ($q) {
                    $q->where('classification', 'activated');
                }])
                ->orderByDesc('activated_leads_count')
                ->limit(5)
                ->get(['id', 'agent_code', 'territory']),
        ]);
    }

    public function reports(string $id)
    {
        $agent = AgentProfile::findOrFail($id);

        return response()->json(
            $agent->dailyReports()->orderByDesc('report_date')->paginate(30)
        );
    }
}