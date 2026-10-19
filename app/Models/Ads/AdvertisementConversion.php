<?php

declare(strict_types=1);

namespace App\Models\Ads;

use App\Enums\Ads\ConversionType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdvertisementConversion extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'campaign_id',
        'advertisement_id',
        'user_id',
        'conversion_type',
        'reference_id',
        'value',
        'created_at',
    ];

    protected $casts = [
        'conversion_type' => ConversionType::class,
        'value' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(AdvertisementCampaign::class, 'campaign_id');
    }

    public function advertisement(): BelongsTo
    {
        return $this->belongsTo(Advertisement::class);
    }
}