<?php

namespace App\Models\Agent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgentDailyReport extends Model
{
    use HasUuids, HasFactory;

    protected $fillable = [
        'agent_id',
        'report_date',
        'territory',
        'calls_count',
        'market_visits_count',
        'new_prospects',
        'qualified_prospects',
        'registrations',
        'completed_registrations',
        'pending_registrations',
        'activated_users',
        'vendors_with_products',
        'products_uploaded_count',
        'first_time_transactions',
        'repeat_transactions',
        'customers_followed_up',
        'customers_requiring_followup',
        'challenges',
        'remarks',
    ];

    protected $casts = [
        'report_date' => 'date',
    ];

    public function agent(): BelongsTo
    {
        return $this->belongsTo(AgentProfile::class, 'agent_id');
    }
}