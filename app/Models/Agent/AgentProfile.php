<?php

namespace App\Models\Agent;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AgentProfile extends Model
{
    use HasUuids, HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'agent_code',
        'territory',
        'assigned_manager_id',
        'status',
        'onboarded_at',
        'last_login_at',
        'notes',
    ];

    protected $casts = [
        'onboarded_at' => 'date',
        'last_login_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_manager_id');
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class, 'agent_id');
    }

    public function followUps(): HasMany
    {
        return $this->hasMany(LeadFollowUp::class, 'agent_id');
    }

    public function activationPayments(): HasMany
    {
        return $this->hasMany(ActivationPayment::class, 'agent_id');
    }

    public function dailyReports(): HasMany
    {
        return $this->hasMany(AgentDailyReport::class, 'agent_id');
    }

    public function kpiSnapshots(): HasMany
    {
        return $this->hasMany(AgentKpiSnapshot::class, 'agent_id');
    }

    public function earnings(): HasMany
    {
        return $this->hasMany(AgentEarning::class, 'agent_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}