<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class LiquidityAccount extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'currency',
        'available_balance',
        'reserved_balance',
    ];

    protected $casts = [
        'available_balance' => 'decimal:8',
        'reserved_balance' => 'decimal:8',
    ];
}
