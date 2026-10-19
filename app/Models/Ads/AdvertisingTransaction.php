<?php

declare(strict_types=1);

namespace App\Models\Ads;

use App\Enums\Ads\TransactionDirection;
use App\Enums\Ads\WalletTransactionType;
use App\Models\Ads\Concerns\GeneratesReference;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdvertisingTransaction extends Model
{
    use GeneratesReference, HasUuids;

    protected static string $referencePrefix = 'TXN';

    protected static string $referenceColumn = 'reference';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'reference',
        'wallet_id',
        'type',
        'direction',
        'amount',
        'balance_after',
        'campaign_id',
        'payment_id',
        'description',
        'meta',
        'created_by',
    ];

    protected $casts = [
        'type' => WalletTransactionType::class,
        'direction' => TransactionDirection::class,
        'amount' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'meta' => 'array',
    ];

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(AdvertisingWallet::class, 'wallet_id');
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(AdvertisementCampaign::class, 'campaign_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}