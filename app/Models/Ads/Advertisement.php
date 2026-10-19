<?php

declare(strict_types=1);

namespace App\Models\Ads;

use App\Enums\Ads\AdvertisementStatus;
use App\Enums\Ads\AdvertisementType;
use App\Enums\Ads\DestinationType;
use App\Models\Ads\Concerns\GeneratesReference;
use App\Models\Product;
use App\Models\User;
use App\Models\VendorProfile;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Advertisement extends Model
{
    use GeneratesReference, HasFactory, HasUuids, SoftDeletes;

    protected static string $referencePrefix = 'ADV';

    protected static string $referenceColumn = 'reference';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'reference',
        'advertiser_id',
        'user_id',
        'vendor_id',
        'product_id',
        'type',
        'title',
        'description',
        'cta',
        'destination_type',
        'destination_url',
        'promo_code',
        'discount_percentage',
        'discount_amount',
        'product_price',
        'offer_start_date',
        'offer_end_date',
        'contact_information',
        'status',
        'rejection_reason',
        'admin_notes',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'type' => AdvertisementType::class,
        'destination_type' => DestinationType::class,
        'status' => AdvertisementStatus::class,
        'discount_percentage' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'product_price' => 'decimal:2',
        'offer_start_date' => 'date',
        'offer_end_date' => 'date',
        'reviewed_at' => 'datetime',
    ];

    public function advertiser(): BelongsTo
    {
        return $this->belongsTo(Advertiser::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(VendorProfile::class, 'vendor_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function media(): HasMany
    {
        return $this->hasMany(AdvertisementMedia::class)->orderBy('order');
    }

    public function campaign(): HasOne
    {
        return $this->hasOne(AdvertisementCampaign::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(AdvertisementPayment::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(AdvertisementReview::class);
    }

    public function isEditable(): bool
    {
        return in_array($this->status, [
            AdvertisementStatus::Draft,
            AdvertisementStatus::ChangesRequired,
        ], true);
    }
}