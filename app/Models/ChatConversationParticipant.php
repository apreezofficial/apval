<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class ChatConversationParticipant extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'conversation_id',
        'user_id',
        'role',
        'nickname',
        'is_muted',
        'muted_until',
        'joined_at',
        'left_at',
        'last_read_at',
    ];

    protected $casts = [
        'is_muted' => 'boolean',
        'muted_until' => 'datetime',
        'joined_at' => 'datetime',
        'left_at' => 'datetime',
        'last_read_at' => 'datetime',
    ];

    // Role Constants
    const ROLE_MEMBER = 'member';
    const ROLE_ADMIN = 'admin';
    const ROLE_MODERATOR = 'moderator';
    const ROLE_OWNER = 'owner';

    // Relationships
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(ChatConversation::class, 'conversation_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('left_at');
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
    public function isActive(): bool
    {
        return is_null($this->left_at);
    }

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

    public function leave(): void
    {
        $this->update(['left_at' => now()]);
    }

    public function rejoin(): void
    {
        $this->update(['left_at' => null]);
    }

    public function updateLastRead(): void
    {
        $this->update(['last_read_at' => now()]);
    }

    public function getUnreadCount(): int
    {
        if (!$this->last_read_at) {
            return $this->conversation->messages()->notDeleted()->count();
        }

        return $this->conversation->messages()
            ->notDeleted()
            ->where('sender_id', '!=', $this->user_id)
            ->where('created_at', '>', $this->last_read_at)
            ->count();
    }
}