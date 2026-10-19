<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Builder;

class FeedReport extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'reporter_id',
        'target_type',
        'target_id',
        'reason',
        'description',
        'status',
        'reviewed_by',
        'resolution_notes',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    // Target Type Constants
    const TARGET_POST = 'post';
    const TARGET_COMMENT = 'comment';
    const TARGET_USER = 'user';

    // Reason Constants
    const REASON_SPAM = 'spam';
    const REASON_SCAM_FRAUD = 'scam_fraud';
    const REASON_HARASSMENT = 'harassment';
    const REASON_HATE_ABUSE = 'hate_abuse';
    const REASON_SEXUAL_INAPPROPRIATE = 'sexual_inappropriate';
    const REASON_FALSE_INFORMATION = 'false_information';
    const REASON_ILLEGAL_CONTENT = 'illegal_content';
    const REASON_OTHER = 'other';

    // Status Constants
    const STATUS_PENDING = 'pending';
    const STATUS_REVIEWING = 'reviewing';
    const STATUS_RESOLVED = 'resolved';
    const STATUS_DISMISSED = 'dismissed';

    // Relationships
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function target(): MorphTo
    {
        return $this->morphTo();
    }

    // Scopes
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeResolved(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_RESOLVED);
    }

    public function scopeForPosts(Builder $query): Builder
    {
        return $query->where('target_type', self::TARGET_POST);
    }

    public function scopeForComments(Builder $query): Builder
    {
        return $query->where('target_type', self::TARGET_COMMENT);
    }

    // Helper Methods
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isResolved(): bool
    {
        return $this->status === self::STATUS_RESOLVED;
    }

    public function resolve(string $reviewerId, string $notes): void
    {
        $this->update([
            'status' => self::STATUS_RESOLVED,
            'reviewed_by' => $reviewerId,
            'resolution_notes' => $notes,
            'resolved_at' => now(),
        ]);
    }

    public function dismiss(string $reviewerId, string $notes): void
    {
        $this->update([
            'status' => self::STATUS_DISMISSED,
            'reviewed_by' => $reviewerId,
            'resolution_notes' => $notes,
            'resolved_at' => now(),
        ]);
    }
}