<?php

namespace App\Http\Resources\Agent;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AgentProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'agent_code'  => $this->agent_code,
            'territory'   => $this->territory,
            'status'      => $this->status,
            'onboarded_at'=> $this->onboarded_at?->toDateString(),
            'user'        => [
                'id'         => $this->user?->id,
                'first_name' => $this->user?->first_name,
                'last_name'  => $this->user?->last_name,
                'phone'      => $this->user?->phone,
                'email'      => $this->user?->email,
            ],
            'manager' => $this->whenLoaded('manager', fn () => [
                'id'   => $this->manager?->id,
                'name' => trim(($this->manager?->first_name ?? '') . ' ' . ($this->manager?->last_name ?? '')),
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}