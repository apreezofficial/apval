<?php

namespace App\Models\Agent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgentKpiSnapshot extends Model
{
    use HasUuids, HasFactory;

    protected $fillable = [
        'agent_id',
        'period_type',
        'period_start',
        'period_end',
        'prospects_contacted',
        'qualified_prospects',
        'registrations',
        'completed_onboardings',
        'activated_users',
        'products_listed',
        'first_transactions',
        'transaction_value',
        'follow_ups_completed',
        'performance_status',
    ];

    protected $casts = [
        'period_start'      => 'date',
        'period_end'        => 'date',
        'transaction_value' => 'decimal:2',
    ];

    public function agent(): BelongsTo
    {
        return $this->belongsTo(AgentProfile::class, 'agent_id');
    }
}