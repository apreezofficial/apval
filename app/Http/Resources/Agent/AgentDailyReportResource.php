<?php

namespace App\Http\Resources\Agent;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AgentDailyReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                            => $this->id,
            'report_date'                   => $this->report_date?->toDateString(),
            'territory'                     => $this->territory,
            'calls_count'                   => $this->calls_count,
            'market_visits_count'           => $this->market_visits_count,
            'new_prospects'                 => $this->new_prospects,
            'qualified_prospects'           => $this->qualified_prospects,
            'registrations'                 => $this->registrations,
            'completed_registrations'       => $this->completed_registrations,
            'pending_registrations'         => $this->pending_registrations,
            'activated_users'               => $this->activated_users,
            'vendors_with_products'         => $this->vendors_with_products,
            'products_uploaded_count'       => $this->products_uploaded_count,
            'first_time_transactions'       => $this->first_time_transactions,
            'repeat_transactions'           => $this->repeat_transactions,
            'customers_followed_up'         => $this->customers_followed_up,
            'customers_requiring_followup'  => $this->customers_requiring_followup,
            'challenges'                    => $this->challenges,
            'remarks'                       => $this->remarks,
        ];
    }
}