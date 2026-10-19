<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class WithdrawalRequest extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'wallet_id',
        'reference',
        'idempotency_key',
        'currency',
        'amount',
        'fee',
        'net_amount',
        'status',
        'payout_mode',
        'bank_code',
        'account_number',
        'account_name',
        'provider',
        'provider_reference',
        'provider_response',
        'meta',
        'completed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:8',
        'fee' => 'decimal:8',
        'net_amount' => 'decimal:8',
        'provider_response' => 'array',
        'meta' => 'array',
        'completed_at' => 'datetime',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function wallet() { return $this->belongsTo(Wallet::class); }
}
