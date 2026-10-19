<?php

declare(strict_types=1);

namespace App\Models\Ads;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdPricingModel extends Model
{
    use HasUuids;

    protected $table = 'ad_pricing_models';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'placement_id',
        'category',
        'pricing_model',
        'rate',
        'is_active',
    ];

    protected $casts = [
        'rate' => 'decimal:4',
        'is_active' => 'boolean',
    ];

    public function placement(): BelongsTo
    {
        return $this->belongsTo(AdPlacement::class, 'placement_id');
    }
}