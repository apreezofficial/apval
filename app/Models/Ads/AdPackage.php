<?php

declare(strict_types=1);

namespace App\Models\Ads;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdPackage extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'name',
        'slug',
        'price',
        'duration_hours',
        'description',
        'placement_keys',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'placement_keys' => 'array',
        'is_active' => 'boolean',
    ];

    public function campaigns(): HasMany
    {
        return $this->hasMany(AdvertisementCampaign::class, 'package_id');
    }
}