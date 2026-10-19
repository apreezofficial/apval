<?php

namespace App\Models\Agent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadFollowUp extends Model
{
    use HasUuids, HasFactory;

    protected $fillable = [
        'lead_id',
        'agent_id',
        'classification_before',
        'classification_after',
        'channel',
        'outcome',
        'note',
        'followed_up_at',
    ];

    protected $casts = [
        'followed_up_at' => 'datetime',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, 'lead_id');
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(AgentProfile::class, 'agent_id');
    }
}