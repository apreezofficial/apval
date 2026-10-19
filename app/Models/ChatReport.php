<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class ChatReport extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'reporter_id',
        'reported_user_id',
        'target_type',
        'target_id',
        'conversation_id',
        'message_id',
        'reason',
        'description',
        'details',
        'status',
        'reviewed_by',
        'resolution_notes',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    // Target Type Constants
    const TARGET_USER = 'user';
    const TARGET_MESSAGE = 'message';
    const TARGET_COMMUNITY = 'community';

    // Reason Constants
    const REASON_SPAM = 'spam';
    const REASON_SCAM = 'scam';
    const REASON_FRAUD = 'fraud';
    const REASON_HARASSMENT = 'harassment';
    const REASON_FAKE_PRODUCT = 'fake_product';
    const REASON_OFFENSIVE_CONTENT = 'offensive_content';
    const REASON_ILLEGAL_ACTIVITY = 'illegal_activity';
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

    public function reportedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_user_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function chatConversation(): BelongsTo
    {
        return $this->belongsTo(ChatConversation::class, 'conversation_id');
    }

    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }

    public function chatMessage(): BelongsTo
    {
        return $this->belongsTo(ChatMessage::class, 'message_id');
    }

    // Scopes
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeReviewing(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_REVIEWING);
    }

    public function scopeResolved(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_RESOLVED);
    }

    public function scopeForUser(Builder $query): Builder
    {
        return $query->where('target_type', self::TARGET_USER);
    }

    public function scopeForMessage(Builder $query): Builder
    {
        return $query->where('target_type', self::TARGET_MESSAGE);
    }

    public function scopeForCommunity(Builder $query): Builder
    {
        return $query->where('target_type', self::TARGET_COMMUNITY);
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

    public function isDismissed(): bool
    {
        return $this->status === self::STATUS_DISMISSED;
    }

    public function markAsReviewing(string $reviewerId): void
    {
        $this->update([
            'status' => self::STATUS_REVIEWING,
            'reviewed_by' => $reviewerId,
        ]);
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

    public function getTarget(): ?Model
    {
        return match ($this->target_type) {
            self::TARGET_USER => User::find($this->target_id) ?? User::find($this->reported_user_id),
            self::TARGET_MESSAGE => ChatMessage::find($this->target_id) ?? Message::find($this->message_id),
            self::TARGET_COMMUNITY => ChatCommunity::find($this->target_id) ?? Community::find($this->target_id),
            default => null,
        };
    }
}
