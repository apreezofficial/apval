<?php

declare(strict_types=1);

namespace App\Models\Ads;

use App\Enums\Ads\AdvertisementStatus;
use App\Enums\Ads\CampaignObjective;
use App\Enums\Ads\PricingModel;
use App\Models\Ads\Concerns\GeneratesReference;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AdvertisementCampaign extends Model
{
    use GeneratesReference, HasUuids;

    protected static string $referencePrefix = 'CMP';

    protected static string $referenceColumn = 'reference';

    protected $table = 'advertisement_campaigns';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'reference',
        'advertisement_id',
        'advertiser_id',
        'objective',
        'pricing_model',
        'package_id',
        'daily_budget',
        'total_budget',
        'amount_spent',
        'start_date',
        'end_date',
        'status',
        'paused_at',
        'flagged_at',
        'flag_reason',
    ];

    protected $casts = [
        'objective' => CampaignObjective::class,
        'pricing_model' => PricingModel::class,
        'status' => AdvertisementStatus::class,
        'daily_budget' => 'decimal:2',
        'total_budget' => 'decimal:2',
        'amount_spent' => 'decimal:2',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'paused_at' => 'datetime',
        'flagged_at' => 'datetime',
    ];

    public function advertisement(): BelongsTo
    {
        return $this->belongsTo(Advertisement::class);
    }

    public function advertiser(): BelongsTo
    {
        return $this->belongsTo(Advertiser::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(AdPackage::class, 'package_id');
    }

    public function target(): HasOne
    {
        return $this->hasOne(AdvertisementTarget::class, 'campaign_id');
    }

    public function placements(): BelongsToMany
    {
        return $this->belongsToMany(
            AdPlacement::class,
            'advertisement_placements',
            'campaign_id',
            'placement_id'
        )->withTimestamps();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(AdvertisementPayment::class, 'campaign_id');
    }

    public function impressions(): HasMany
    {
        return $this->hasMany(AdvertisementImpression::class, 'campaign_id');
    }

    public function clicks(): HasMany
    {
        return $this->hasMany(AdvertisementClick::class, 'campaign_id');
    }

    public function conversions(): HasMany
    {
        return $this->hasMany(AdvertisementConversion::class, 'campaign_id');
    }

    public function dailyReports(): HasMany
    {
        return $this->hasMany(AdvertisementReport::class, 'campaign_id');
    }

    public function fraudFlags(): HasMany
    {
        return $this->hasMany(AdvertisementFraudFlag::class, 'campaign_id');
    }

    public function remainingBudget(): float
    {
        return max(0, (float) $this->total_budget - (float) $this->amount_spent);
    }

    public function isBudgetExhausted(): bool
    {
        return (float) $this->amount_spent >= (float) $this->total_budget;
    }
}