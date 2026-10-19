<?php

declare(strict_types=1);

namespace App\Models\Ads;

use App\Enums\Ads\Gender;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdvertisementTarget extends Model
{
    use HasUuids;

    protected $table = 'advertisement_targets';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'campaign_id',
        'countries',
        'states',
        'cities',
        'delivery_areas',
        'min_age',
        'max_age',
        'gender',
        'interests',
        'marketplace_categories',
    ];

    protected $casts = [
        'countries' => 'array',
        'states' => 'array',
        'cities' => 'array',
        'delivery_areas' => 'array',
        'interests' => 'array',
        'marketplace_categories' => 'array',
        'gender' => Gender::class,
        'min_age' => 'integer',
        'max_age' => 'integer',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(AdvertisementCampaign::class, 'campaign_id');
    }
}