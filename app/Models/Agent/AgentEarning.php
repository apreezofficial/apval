<?php

namespace App\Models\Agent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AgentEarning extends Model
{
    use HasUuids, HasFactory;

    protected $fillable = [
        'agent_id',
        'earning_type',
        'source_type',
        'source_id',
        'amount',
        'status',
        'description',
        'paid_at',
    ];

    protected $casts = [
        'amount'  => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    const TYPE_ACTIVATION       = 'activation';
    const TYPE_FIRST_TRANSACTION = 'first_transaction';
    const TYPE_BONUS            = 'bonus';
    const TYPE_ADJUSTMENT       = 'adjustment';

    const STATUS_PENDING  = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_PAID     = 'paid';
    const STATUS_REVERSED = 'reversed';

    public function agent(): BelongsTo
    {
        return $this->belongsTo(AgentProfile::class, 'agent_id');
    }

    public function source(): MorphTo
    {
        return $this->morphTo();
    }
}