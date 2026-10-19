<?php

declare(strict_types=1);

namespace App\Models\Ads;

use App\Enums\Ads\FraudIndicator;
use App\Enums\Ads\FraudStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdvertisementFraudFlag extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'campaign_id',
        'indicator',
        'details',
        'status',
    ];

    protected $casts = [
        'indicator' => FraudIndicator::class,
        'details' => 'array',
        'status' => FraudStatus::class,
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(AdvertisementCampaign::class, 'campaign_id');
    }
}