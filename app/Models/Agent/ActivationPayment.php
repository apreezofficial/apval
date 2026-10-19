<?php

namespace App\Models\Agent;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivationPayment extends Model
{
    use HasUuids, HasFactory;

    protected $fillable = [
        'user_id',
        'agent_id',
        'lead_id',
        'amount',
        'currency',
        'reference',
        'provider',
        'provider_reference',
        'status',
        'receipt_number',
        'paid_at',
        'meta',
    ];

    protected $casts = [
        'amount'  => 'decimal:2',
        'paid_at' => 'datetime',
        'meta'    => 'array',
    ];

    const STATUS_PENDING   = 'pending';
    const STATUS_PAID      = 'paid';
    const STATUS_FAILED    = 'failed';
    const STATUS_CANCELLED = 'cancelled';

    // Fixed fee per §8 of the Market Agent doc — application-level guard,
    // not just a DB default, so it can't be silently overridden.
   const FIXED_AMOUNT = '2000.00';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(AgentProfile::class, 'agent_id');
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, 'lead_id');
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }
}