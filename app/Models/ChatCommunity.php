<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ChatCommunity extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'owner_id',
        'conversation_id',
        'name',
        'slug',
        'description',
        'banner_url',
        'avatar_url',
        'category',
        'tags',
        'privacy',
        'approval_required',
        'members_can_post',
        'is_locked',
        'members_count',
        'posts_count',
        'rules',
        'settings',
    ];

    protected $casts = [
        'tags' => 'array',
        'rules' => 'array',
        'settings' => 'array',
        'approval_required' => 'boolean',
        'members_can_post' => 'boolean',
        'is_locked' => 'boolean',
        'members_count' => 'integer',
        'posts_count' => 'integer',
    ];

    // Privacy Constants
    const PRIVACY_PUBLIC = 'public';
    const PRIVACY_PRIVATE = 'private';
    const PRIVACY_INVITE_ONLY = 'invite_only';

    // Relationships
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(ChatConversation::class, 'conversation_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(ChatCommunityMember::class);
    }

    public function admins(): HasMany
    {
        return $this->hasMany(ChatCommunityMember::class)->whereIn('role', ['admin', 'owner']);
    }

    public function moderators(): HasMany
    {
        return $this->hasMany(ChatCommunityMember::class)->where('role', 'moderator');
    }

    // Scopes
    public function scopePublic(Builder $query): Builder
    {
        return $query->where('privacy', self::PRIVACY_PUBLIC);
    }

    public function scopePrivate(Builder $query): Builder
    {
        return $query->where('privacy', self::PRIVACY_PRIVATE);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_locked', false);
    }

    public function scopeByCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    public function scopeWithMember(Builder $query, string $userId): Builder
    {
        return $query->whereHas('members', function ($q) use ($userId) {
            $q->where('user_id', $userId)->where('is_banned', false);
        });
    }

    // Helper Methods
    public function isPublic(): bool
    {
        return $this->privacy === self::PRIVACY_PUBLIC;
    }

    public function isPrivate(): bool
    {
        return $this->privacy === self::PRIVACY_PRIVATE;
    }

    public function isInviteOnly(): bool
    {
        return $this->privacy === self::PRIVACY_INVITE_ONLY;
    }

    public function isLocked(): bool
    {
        return $this->is_locked;
    }

    public function hasMember(string $userId): bool
    {
        return $this->members()
            ->where('user_id', $userId)
            ->where('is_banned', false)
            ->exists();
    }

    public function isAdmin(string $userId): bool
    {
        return $this->members()
            ->where('user_id', $userId)
            ->whereIn('role', ['admin', 'owner'])
            ->exists();
    }

    public function isModerator(string $userId): bool
    {
        return $this->members()
            ->where('user_id', $userId)
            ->whereIn('role', ['moderator', 'admin', 'owner'])
            ->exists();
    }

    public function isBanned(string $userId): bool
    {
        return $this->members()
            ->where('user_id', $userId)
            ->where('is_banned', true)
            ->exists();
    }

    public function addMember(string $userId, string $role = 'member'): ChatCommunityMember
    {
        $member = $this->members()->firstOrCreate(
            ['user_id' => $userId],
            ['role' => $role, 'joined_at' => now()]
        );

        $this->increment('members_count');

        return $member;
    }

    public function removeMember(string $userId): void
    {
        $member = $this->members()->where('user_id', $userId)->first();
        
        if ($member) {
            $member->delete();
            $this->decrement('members_count');
        }
    }

    public function banMember(string $userId, string $reason): void
    {
        $this->members()
            ->where('user_id', $userId)
            ->update([
                'is_banned' => true,
                'ban_reason' => $reason,
            ]);

        $this->decrement('members_count');
    }

    public function unbanMember(string $userId): void
    {
        $this->members()
            ->where('user_id', $userId)
            ->update([
                'is_banned' => false,
                'ban_reason' => null,
            ]);

        $this->increment('members_count');
    }

    public function incrementPostCount(): void
    {
        $this->increment('posts_count');
    }

    // Boot method to auto-generate slug
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($community) {
            if (empty($community->slug)) {
                $community->slug = Str::slug($community->name) . '-' . Str::random(6);
            }
        });
    }
}