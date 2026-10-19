<?php

namespace App\Models\Agent;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use HasUuids, HasFactory, SoftDeletes;

    protected $fillable = [
        'agent_id',
        'full_name',
        'phone',
        'business_name',
        'category',
        'sub_category',
        'location',
        'state',
        'classification',
        'source',
        'needs_discovery',
        'notes',
        'linked_user_id',
        'next_follow_up_at',
        'registered_at',
        'activated_at',
    ];

    protected $casts = [
        'needs_discovery'   => 'array',
        'next_follow_up_at' => 'datetime',
        'registered_at'     => 'datetime',
        'activated_at'      => 'datetime',
    ];

    const CLASSIFICATION_NEW       = 'new';
    const CLASSIFICATION_HOT       = 'hot';
    const CLASSIFICATION_WARM      = 'warm';
    const CLASSIFICATION_COLD      = 'cold';
    const CLASSIFICATION_ACTIVATED = 'activated';

    public function agent(): BelongsTo
    {
        return $this->belongsTo(AgentProfile::class, 'agent_id');
    }

    public function linkedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'linked_user_id');
    }

    public function followUps(): HasMany
    {
        return $this->hasMany(LeadFollowUp::class, 'lead_id')->orderByDesc('followed_up_at');
    }

    public function activationPayments(): HasMany
    {
        return $this->hasMany(ActivationPayment::class, 'lead_id');
    }

    public function isActivated(): bool
    {
        return $this->classification === self::CLASSIFICATION_ACTIVATED;
    }

    public function markLinked(User $user): void
    {
        $this->update([
            'linked_user_id' => $user->id,
            'registered_at'  => now(),
        ]);
    }
}