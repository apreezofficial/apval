<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class FeedNotification extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'sender_id',
        'type',
        'reference_type',
        'reference_id',
        'message',
        'metadata',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    // Type Constants
    const TYPE_POST_LIKED = 'post_liked';
    const TYPE_POST_COMMENTED = 'post_commented';
    const TYPE_COMMENT_REPLIED = 'comment_replied';
    const TYPE_POST_SHARED = 'post_shared';
    const TYPE_NEW_FOLLOWER = 'new_follower';
    const TYPE_MENTION = 'mention';
    const TYPE_ADMIN_ANNOUNCEMENT = 'admin_announcement';
    const TYPE_POST_FEATURED = 'post_featured';
    const TYPE_COMMENT_LIKED = 'comment_liked';

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class); // recipient
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id'); // actor
    }

    // Scopes
    public function scopeUnread(Builder $query): Builder
    {
        return $query->where('is_read', false);
    }

    public function scopeForUser(Builder $query, string $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    // Helper Methods
    public function isRead(): bool
    {
        return $this->is_read;
    }

    public function markAsRead(): void
    {
        if (!$this->is_read) {
            $this->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }
    }

    public static function markAllAsRead(string $userId): int
    {
        return static::forUser($userId)
            ->unread()
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }
}