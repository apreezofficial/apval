<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class ChatCommunityMember extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'community_id',
        'user_id',
        'role',
        'joined_at',
        'is_muted',
        'muted_until',
        'is_banned',
        'ban_reason',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
        'is_muted' => 'boolean',
        'muted_until' => 'datetime',
        'is_banned' => 'boolean',
    ];

    // Role Constants
    const ROLE_MEMBER = 'member';
    const ROLE_MODERATOR = 'moderator';
    const ROLE_ADMIN = 'admin';
    const ROLE_OWNER = 'owner';

    // Relationships
    public function community(): BelongsTo
    {
        return $this->belongsTo(ChatCommunity::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_banned', false);
    }

    public function scopeBanned(Builder $query): Builder
    {
        return $query->where('is_banned', true);
    }

    public function scopeAdmins(Builder $query): Builder
    {
        return $query->whereIn('role', [self::ROLE_ADMIN, self::ROLE_OWNER]);
    }

    public function scopeModerators(Builder $query): Builder
    {
        return $query->whereIn('role', [self::ROLE_MODERATOR, self::ROLE_ADMIN, self::ROLE_OWNER]);
    }

    // Helper Methods
    public function isAdmin(): bool
    {
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_OWNER]);
    }

    public function isModerator(): bool
    {
        return in_array($this->role, [self::ROLE_MODERATOR, self::ROLE_ADMIN, self::ROLE_OWNER]);
    }

    public function isOwner(): bool
    {
        return $this->role === self::ROLE_OWNER;
    }

    public function isBanned(): bool
    {
        return $this->is_banned;
    }

    public function isMuted(): bool
    {
        if (!$this->is_muted) {
            return false;
        }

        if ($this->muted_until && $this->muted_until->isPast()) {
            $this->update(['is_muted' => false, 'muted_until' => null]);
            return false;
        }

        return true;
    }

    public function mute(?int $minutes = null): void
    {
        $this->update([
            'is_muted' => true,
            'muted_until' => $minutes ? now()->addMinutes($minutes) : null,
        ]);
    }

    public function unmute(): void
    {
        $this->update([
            'is_muted' => false,
            'muted_until' => null,
        ]);
    }

    public function promote(string $newRole): void
    {
        $this->update(['role' => $newRole]);
    }

    public function demote(): void
    {
        $this->update(['role' => self::ROLE_MEMBER]);
    }
}