<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class FeedSponsoredPost extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'post_id',
        'advertiser_id',
        'campaign_name',
        'budget',
        'spent_amount',
        'start_date',
        'end_date',
        'target_location',
        'target_category',
        'target_audience',
        'impressions',
        'clicks',
        'conversions',
        'billing_model',
        'cost_per_unit',
        'status',
    ];

    protected $casts = [
        'budget' => 'decimal:2',
        'spent_amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'target_location' => 'array',
        'target_category' => 'array',
        'target_audience' => 'array',
        'impressions' => 'integer',
        'clicks' => 'integer',
        'conversions' => 'integer',
        'cost_per_unit' => 'decimal:2',
    ];

    // Status Constants
    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING_APPROVAL = 'pending_approval';
    const STATUS_ACTIVE = 'active';
    const STATUS_PAUSED = 'paused';
    const STATUS_COMPLETED = 'completed';
    const STATUS_REJECTED = 'rejected';

    // Billing Model Constants
    const BILLING_CPM = 'cpm';
    const BILLING_CPC = 'cpc';
    const BILLING_FIXED = 'fixed';

    // Relationships
    public function post(): BelongsTo
    {
        return $this->belongsTo(FeedPost::class);
    }

    public function advertiser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'advertiser_id');
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now());
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING_APPROVAL);
    }

    // Helper Methods
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE 
            && $this->start_date->isPast() 
            && $this->end_date->isFuture();
    }

    public function isExpired(): bool
    {
        return $this->end_date->isPast();
    }

    public function hasBudgetRemaining(): bool
    {
        return $this->spent_amount < $this->budget;
    }

    public function incrementImpressions(int $count = 1): void
    {
        $this->increment('impressions', $count);
    }

    public function incrementClicks(int $count = 1): void
    {
        $this->increment('clicks', $count);
    }

    public function incrementConversions(int $count = 1): void
    {
        $this->increment('conversions', $count);
    }

    public function activate(): void
    {
        $this->update(['status' => self::STATUS_ACTIVE]);
    }

    public function pause(): void
    {
        $this->update(['status' => self::STATUS_PAUSED]);
    }

    public function complete(): void
    {
        $this->update(['status' => self::STATUS_COMPLETED]);
    }

    public function reject(): void
    {
        $this->update(['status' => self::STATUS_REJECTED]);
    }
}