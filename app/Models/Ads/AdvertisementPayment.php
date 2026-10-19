<?php

declare(strict_types=1);

namespace App\Models\Ads;

use App\Enums\Ads\PaymentMethod;
use App\Enums\Ads\PaymentStatus;
use App\Models\Ads\Concerns\GeneratesReference;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdvertisementPayment extends Model
{
    use GeneratesReference, HasUuids;

    protected static string $referencePrefix = 'PAY';

    protected static string $referenceColumn = 'reference';

    protected $table = 'advertisement_payments';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'reference',
        'advertiser_id',
        'campaign_id',
        'advertisement_id',
        'transaction_id',
        'amount',
        'gateway_fee',
        'net_revenue',
        'payment_method',
        'status',
        'payment_reference',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'gateway_fee' => 'decimal:2',
        'net_revenue' => 'decimal:2',
        'payment_method' => PaymentMethod::class,
        'status' => PaymentStatus::class,
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(AdvertisementCampaign::class, 'campaign_id');
    }

    public function advertisement(): BelongsTo
    {
        return $this->belongsTo(Advertisement::class);
    }

    public function advertiser(): BelongsTo
    {
        return $this->belongsTo(Advertiser::class);
    }
}