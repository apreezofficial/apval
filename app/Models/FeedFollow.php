<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class FeedFollow extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'follower_id',
        'following_id',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    // Relationships
    public function follower(): BelongsTo
    {
        return $this->belongsTo(User::class, 'follower_id');
    }

    public function following(): BelongsTo
    {
        return $this->belongsTo(User::class, 'following_id');
    }

    // Scopes
    public function scopeFollowersOf(Builder $query, string $userId): Builder
    {
        return $query->where('following_id', $userId);
    }

    public function scopeFollowingBy(Builder $query, string $userId): Builder
    {
        return $query->where('follower_id', $userId);
    }

    // Static Helper Methods
    public static function isFollowing(string $followerId, string $followingId): bool
    {
        return static::where('follower_id', $followerId)
            ->where('following_id', $followingId)
            ->exists();
    }

    public static function follow(string $followerId, string $followingId): self
    {
        return static::firstOrCreate([
            'follower_id' => $followerId,
            'following_id' => $followingId,
        ]);
    }

    public static function unfollow(string $followerId, string $followingId): bool
    {
        return static::where('follower_id', $followerId)
            ->where('following_id', $followingId)
            ->delete() > 0;
    }

    public static function getFollowerCount(string $userId): int
    {
        return static::followersOf($userId)->count();
    }

    public static function getFollowingCount(string $userId): int
    {
        return static::followingBy($userId)->count();
    }
}