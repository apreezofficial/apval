<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Http\Requests\Agent\InitiateActivationPaymentRequest;
use App\Http\Resources\Agent\ActivationPaymentResource;
use App\Models\Agent\ActivationPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ActivationPaymentController extends Controller
{
    /**
     * Create a pending ₦2,000 activation fee record for a farmer/vendor (§8).
     *
     * NOTE: This intentionally does NOT call out to Monnify directly.
     * Actual payment collection should be wired through the existing
     * payment infrastructure (see WalletController::getOrCreateNgnVirtualAccount
     * or a dedicated Monnify service) in a follow-up integration task,
     * without modifying MonnifyWebhookController. This endpoint only
     * establishes the system-of-record entry and reference number.
     */
    public function initiate(InitiateActivationPaymentRequest $request)
    {
        $agent = $request->user()->agentProfile()->firstOrFail();
        $data = $request->validated();

        $payment = ActivationPayment::create([
            'user_id'   => $data['user_id'],
            'agent_id'  => $agent->id,
            'lead_id'   => $data['lead_id'] ?? null,
            'amount'    => ActivationPayment::FIXED_AMOUNT,
            'currency'  => 'NGN',
            'reference' => 'ACT-' . strtoupper(Str::random(10)),
            'provider'  => 'monnify',
            'status'    => ActivationPayment::STATUS_PENDING,
        ]);

        return new ActivationPaymentResource($payment);
    }

    public function status(Request $request, string $reference)
    {
        $agent = $request->user()->agentProfile()->firstOrFail();

        $payment = ActivationPayment::where('agent_id', $agent->id)
            ->where('reference', $reference)
            ->firstOrFail();

        return new ActivationPaymentResource($payment);
    }
}