<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Http\Requests\Agent\ClassifyLeadRequest;
use App\Http\Requests\Agent\LinkLeadCustomerRequest;
use App\Http\Requests\Agent\LogFollowUpRequest;
use App\Http\Requests\Agent\StoreLeadRequest;
use App\Http\Requests\Agent\UpdateLeadRequest;
use App\Http\Resources\Agent\LeadResource;
use App\Models\Agent\Lead;
use App\Models\Agent\LeadFollowUp;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        $agent = $request->user()->agentProfile()->firstOrFail();

        $query = $agent->leads()->latest();

        if ($request->filled('classification')) {
            $query->where('classification', $request->query('classification'));
        }

        return LeadResource::collection($query->paginate(20));
    }

    public function store(StoreLeadRequest $request)
    {
        $agent = $request->user()->agentProfile()->firstOrFail();

        $lead = $agent->leads()->create($request->validated());

        return new LeadResource($lead);
    }

    public function show(Request $request, string $id)
    {
        $lead = $this->ownedLead($request, $id, ['followUps']);

        return new LeadResource($lead);
    }

    public function update(UpdateLeadRequest $request, string $id)
    {
        $lead = $this->ownedLead($request, $id);

        $lead->update($request->validated());

        return new LeadResource($lead->fresh());
    }

    /**
     * Update a lead's pipeline classification.
     * Applies §14 default follow-up windows unless an explicit
     * next_follow_up_at override was supplied.
     */
    public function classify(ClassifyLeadRequest $request, string $id)
    {
        $lead = $this->ownedLead($request, $id);
        $data = $request->validated();

        $nextFollowUp = $data['next_follow_up_at'] ?? match ($data['classification']) {
            'hot'   => now(),                 // same day
            'warm'  => now()->addHours(48),   // 24-48 hrs
            'cold'  => now()->addWeeks(2),     // later
            default => $lead->next_follow_up_at,
        };

        $lead->update([
            'classification'    => $data['classification'],
            'next_follow_up_at' => $nextFollowUp,
        ]);

        return new LeadResource($lead->fresh());
    }

    /**
     * Log a follow-up interaction against a lead (§10, §14).
     */
    public function logFollowUp(LogFollowUpRequest $request, string $id)
    {
        $lead = $this->ownedLead($request, $id);
        $data = $request->validated();

        $followUp = DB::transaction(function () use ($lead, $data) {
            $followUp = LeadFollowUp::create([
                'lead_id'               => $lead->id,
                'agent_id'              => $lead->agent_id,
                'classification_before' => $lead->classification,
                'classification_after'  => $data['classification_after'] ?? $lead->classification,
                'channel'               => $data['channel'],
                'outcome'               => $data['outcome'],
                'note'                  => $data['note'] ?? null,
                'followed_up_at'        => now(),
            ]);

            if (! empty($data['classification_after'])) {
                $lead->update(['classification' => $data['classification_after']]);
            }

            return $followUp;
        });

        return response()->json([
            'message'   => 'Follow-up logged successfully.',
            'follow_up' => $followUp,
            'lead'      => new LeadResource($lead->fresh()),
        ], 201);
    }

    /**
     * Link a lead to an already self-registered platform user.
     *
     * IMPORTANT (§7): The agent never creates the account. This endpoint
     * only attaches an existing users.id to the lead once the customer
     * has completed their own registration.
     */
    public function linkCustomer(LinkLeadCustomerRequest $request, string $id)
    {
        $lead = $this->ownedLead($request, $id);
        $user = User::findOrFail($request->validated('user_id'));

        $lead->markLinked($user);

        return new LeadResource($lead->fresh());
    }

    /**
     * Scope a lead lookup to the authenticated agent's own leads only.
     */
    private function ownedLead(Request $request, string $id, array $with = []): Lead
    {
        $agent = $request->user()->agentProfile()->firstOrFail();

        return $agent->leads()->with($with)->findOrFail($id);
    }
}