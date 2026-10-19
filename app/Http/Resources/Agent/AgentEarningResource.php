<?php

namespace App\Http\Resources\Agent;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AgentEarningResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'earning_type' => $this->earning_type,
            'amount'       => (string) $this->amount,
            'status'       => $this->status,
            'description'  => $this->description,
            'paid_at'      => $this->paid_at?->toIso8601String(),
            'created_at'   => $this->created_at?->toIso8601String(),
        ];
    }
}