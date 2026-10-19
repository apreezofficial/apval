<?php

declare(strict_types=1);

namespace App\Models\Ads;

use App\Enums\Ads\AdvertiserStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Advertiser extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'business_name',
        'industry',
        'status',
        'restricted_categories',
        'approved_at',
        'approved_by',
        'rejection_reason',
        'violation_count',
    ];

    protected $casts = [
        'restricted_categories' => 'array',
        'approved_at' => 'datetime',
        'status' => AdvertiserStatus::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function advertisements(): HasMany
    {
        return $this->hasMany(Advertisement::class);
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(AdvertisementCampaign::class);
    }

    public function wallet(): HasOne
    {
        return $this->hasOne(AdvertisingWallet::class);
    }

    public function isApproved(): bool
    {
        return $this->status === AdvertiserStatus::Approved;
    }
}