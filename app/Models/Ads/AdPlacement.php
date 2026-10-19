<?php

declare(strict_types=1);

namespace App\Models\Ads;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdPlacement extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'key',
        'name',
        'description',
        'requires_approval',
        'is_active',
        'price_per_day',
    ];

    protected $casts = [
        'requires_approval' => 'boolean',
        'is_active' => 'boolean',
        'price_per_day' => 'decimal:2',
    ];

    public function campaigns(): BelongsToMany
    {
        return $this->belongsToMany(
            AdvertisementCampaign::class,
            'advertisement_placements',
            'placement_id',
            'campaign_id'
        )->withTimestamps();
    }

    public function pricingModels(): HasMany
    {
        return $this->hasMany(AdPricingModel::class, 'placement_id');
    }
}