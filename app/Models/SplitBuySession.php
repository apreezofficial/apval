<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SplitBuySession extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'creator_id',
        'product_id',
        'quantity',
        'total_amount',
        'current_amount',
        'status',
        'expires_at',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'total_amount' => 'decimal:2',
        'current_amount' => 'decimal:2',
        'expires_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function contributions(): HasMany
    {
        return $this->hasMany(SplitBuyContribution::class);
    }
}
