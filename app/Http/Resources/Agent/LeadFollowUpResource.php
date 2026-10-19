<?php

namespace App\Http\Resources\Agent;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeadFollowUpResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                     => $this->id,
            'channel'                => $this->channel,
            'outcome'                => $this->outcome,
            'classification_before'  => $this->classification_before,
            'classification_after'   => $this->classification_after,
            'note'                   => $this->note,
            'followed_up_at'         => $this->followed_up_at?->toIso8601String(),
        ];
    }
}