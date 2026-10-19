<?php

declare(strict_types=1);

namespace App\Models\Ads;

use App\Enums\Ads\RefundStatus;
use App\Enums\Ads\RefundType;
use App\Models\Ads\Concerns\GeneratesReference;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdvertisementRefund extends Model
{
    use GeneratesReference, HasUuids;

    protected static string $referencePrefix = 'REF';

    protected static string $referenceColumn = 'reference';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'reference',
        'campaign_id',
        'advertiser_id',
        'payment_id',
        'amount',
        'type',
        'reason',
        'admin_id',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'type' => RefundType::class,
        'status' => RefundStatus::class,
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(AdvertisementCampaign::class, 'campaign_id');
    }

    public function advertiser(): BelongsTo
    {
        return $this->belongsTo(Advertiser::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(AdvertisementPayment::class, 'payment_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}