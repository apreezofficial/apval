<?php

namespace App\Http\Resources\Agent;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivationPaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'reference'          => $this->reference,
            'amount'             => (string) $this->amount,
            'currency'           => $this->currency,
            'status'             => $this->status,
            'provider'           => $this->provider,
            'provider_reference' => $this->provider_reference,
            'receipt_number'     => $this->receipt_number,
            'paid_at'            => $this->paid_at?->toIso8601String(),
            'created_at'         => $this->created_at?->toIso8601String(),
        ];
    }
}