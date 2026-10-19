<?php

namespace App\Http\Resources\Agent;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeadResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'full_name'         => $this->full_name,
            'phone'             => $this->phone,
            'business_name'     => $this->business_name,
            'category'          => $this->category,
            'sub_category'      => $this->sub_category,
            'location'          => $this->location,
            'state'             => $this->state,
            'classification'    => $this->classification,
            'source'            => $this->source,
            'needs_discovery'   => $this->needs_discovery,
            'notes'             => $this->notes,
            'linked_user_id'    => $this->linked_user_id,
            'is_activated'      => $this->isActivated(),
            'next_follow_up_at' => $this->next_follow_up_at?->toIso8601String(),
            'registered_at'     => $this->registered_at?->toIso8601String(),
            'activated_at'      => $this->activated_at?->toIso8601String(),
            'follow_ups'        => LeadFollowUpResource::collection($this->whenLoaded('followUps')),
            'created_at'        => $this->created_at?->toIso8601String(),
        ];
    }
}