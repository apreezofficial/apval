<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaction extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'initiated_by',
        'from_wallet_id',
        'to_wallet_id',
        'amount',
        'currency',
        'reference',
        'idempotency_key',
        'description',
        'type',
        'status',
        'provider',
        'provider_reference',
        'meta',
        'completed_at',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'completed_at' => 'datetime',
        'amount' => 'decimal:8',
    ];

    // Relationships
    public function initiator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }

    public function fromWallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'from_wallet_id');
    }

    public function toWallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'to_wallet_id');
    }

    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(LedgerEntry::class);
    }
}
