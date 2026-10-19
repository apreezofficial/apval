<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class ChatBlockedUser extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'blocked_user_id',
        'reason',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function blockedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'blocked_user_id');
    }

    // Scopes
    public function scopeBlockedBy(Builder $query, string $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeBlocking(Builder $query, string $userId): Builder
    {
        return $query->where('blocked_user_id', $userId);
    }

    // Static Helper Methods
    public static function isBlocked(string $userId, string $blockedUserId): bool
    {
        return static::where('user_id', $userId)
            ->where('blocked_user_id', $blockedUserId)
            ->exists();
    }

    public static function block(string $userId, string $blockedUserId, ?string $reason = null): self
    {
        return static::firstOrCreate(
            [
                'user_id' => $userId,
                'blocked_user_id' => $blockedUserId,
            ],
            ['reason' => $reason]
        );
    }

    public static function unblock(string $userId, string $blockedUserId): bool
    {
        return static::where('user_id', $userId)
            ->where('blocked_user_id', $blockedUserId)
            ->delete() > 0;
    }

    public static function getBlockedUserIds(string $userId): array
    {
        return static::where('user_id', $userId)
            ->pluck('blocked_user_id')
            ->toArray();
    }
}