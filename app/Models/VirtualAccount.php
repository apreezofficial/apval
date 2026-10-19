<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class VirtualAccount extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'wallet_id',
        'provider',
        'currency',
        'account_reference',
        'reservation_reference',
        'account_name',
        'accounts',
        'status',
        'provider_response',
    ];

    protected $casts = [
        'accounts' => 'array',
        'provider_response' => 'array',
    ];
}
