<?php

declare(strict_types=1);

namespace App\Models\Ads;

use App\Enums\Ads\ReviewAction;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdvertisementReview extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'campaign_id',
        'advertisement_id',
        'admin_id',
        'action',
        'reason',
        'created_at',
    ];

    protected $casts = [
        'action' => ReviewAction::class,
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

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}