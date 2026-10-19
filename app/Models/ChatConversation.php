<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class ChatConversation extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'type',
        'title',
        'avatar_url',
        'description',
        'related_order_id',
        'related_product_id',
        'related_user_id',
        'metadata',
        'is_locked',
        'is_archived',
        'last_message_preview',
        'last_message_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_locked' => 'boolean',
        'is_archived' => 'boolean',
        'last_message_at' => 'datetime',
    ];

    // Relationships
   
    public function participants(): HasMany
    {
        return $this->hasMany(ChatConversationParticipant::class, 'conversation_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'conversation_id');
    }

    public function latestMessage(): HasOne
    {
        return $this->hasOne(ChatMessage::class, 'conversation_id')->latestOfMany();
    }


    public function community(): HasOne
    {
        return $this->hasOne(ChatCommunity::class, 'conversation_id');
    }

    public function relatedOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'related_order_id');
    }

    public function relatedProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'related_product_id');
    }

    public function relatedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'related_user_id');
    }

    // Scopes
    public function scopeDirect(Builder $query): Builder
    {
        return $query->where('type', 'direct');
    }

    public function scopeGroup(Builder $query): Builder
    {
        return $query->where('type', 'group');
    }

    public function scopeMarketplace(Builder $query): Builder
    {
        return $query->where('type', 'marketplace');
    }

    public function scopeOrder(Builder $query): Builder
    {
        return $query->where('type', 'order');
    }

    public function scopeSupport(Builder $query): Builder
    {
        return $query->where('type', 'support');
    }

    public function scopeWithParticipant(Builder $query, string $userId): Builder
    {
        return $query->whereHas('participants', function ($q) use ($userId) {
            $q->where('user_id', $userId)->whereNull('left_at');
        });
    }

    public function scopeWithUnreadMessages(Builder $query, string $userId): Builder
    {
        return $query->whereHas('participants', function ($q) use ($userId) {
            $q->where('user_id', $userId)
              ->whereNull('left_at')
              ->where(function ($subQ) {
                  $subQ->whereColumn('last_message_at', '>', 'last_read_at')
                       ->orWhereNull('last_read_at');
              });
        });
    }

    // Helper Methods
    public function isDirect(): bool
    {
        return $this->type === 'direct';
    }

    public function isGroup(): bool
    {
        return in_array($this->type, ['group', 'community']);
    }

    public function isLocked(): bool
    {
        return $this->is_locked;
    }

    public function addParticipant(string $userId, string $role = 'member'): ChatConversationParticipant
    {
        return $this->participants()->create([
            'user_id' => $userId,
            'role' => $role,
            'joined_at' => now(),
        ]);
    }

    public function hasParticipant(string $userId): bool
    {
        return $this->participants()
            ->where('user_id', $userId)
            ->whereNull('left_at')
            ->exists();
    }

    public function getOtherParticipant(string $userId): ?ChatConversationParticipant
    {
        if (!$this->isDirect()) {
            return null;
        }

        return $this->participants()
            ->where('user_id', '!=', $userId)
            ->whereNull('left_at')
            ->first();
    }

    public function updateLastMessage(string $preview): void
    {
        $this->update([
            'last_message_preview' => $preview,
            'last_message_at' => now(),
        ]);
    }
}