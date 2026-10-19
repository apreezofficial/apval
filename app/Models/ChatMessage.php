<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class ChatMessage extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'conversation_id',
        'sender_id',
        'reply_to_message_id',
        'type',
        'content',
        'media_url',
        'media_type',
        'media_duration',
        'media_metadata',
        'product_id',
        'order_id',
        'payment_request_id',
        'profile_user_id',
        'latitude',
        'longitude',
        'location_name',
        'reactions',
        'delivered_at',
        'read_at',
        'is_edited',
        'is_deleted',
        'deleted_at',
        'flagged_suspicious',
        'flag_reason',
    ];

    protected $casts = [
        'media_metadata' => 'array',
        'reactions' => 'array',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'delivered_at' => 'datetime',
        'read_at' => 'datetime',
        'is_edited' => 'boolean',
        'is_deleted' => 'boolean',
        'deleted_at' => 'datetime',
        'flagged_suspicious' => 'boolean',
    ];

    // Message Type Constants
    const TYPE_TEXT = 'text';
    const TYPE_IMAGE = 'image';
    const TYPE_VIDEO = 'video';
    const TYPE_VOICE = 'voice';
    const TYPE_DOCUMENT = 'document';
    const TYPE_LOCATION = 'location';
    const TYPE_PRODUCT_CARD = 'product_card';
    const TYPE_ORDER_CARD = 'order_card';
    const TYPE_PAYMENT_REQUEST = 'payment_request';
    const TYPE_PROFILE_CARD = 'profile_card';
    const TYPE_SYSTEM = 'system';
    const TYPE_AI_RESPONSE = 'ai_response';

    // Relationships
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(ChatConversation::class, 'conversation_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function replyTo(): BelongsTo
    {
        return $this->belongsTo(ChatMessage::class, 'reply_to_message_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'reply_to_message_id');
    }

    public function reads(): HasMany
    {
        return $this->hasMany(ChatMessageRead::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function profileUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'profile_user_id');
    }

    // Scopes
    public function scopeText(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_TEXT);
    }

    public function scopeMedia(Builder $query): Builder
    {
        return $query->whereIn('type', [
            self::TYPE_IMAGE,
            self::TYPE_VIDEO,
            self::TYPE_VOICE,
            self::TYPE_DOCUMENT,
        ]);
    }

    public function scopeNotDeleted(Builder $query): Builder
    {
        return $query->where('is_deleted', false);
    }

    public function scopeAfter(Builder $query, $timestamp): Builder
    {
        return $query->where('created_at', '>', $timestamp);
    }

    public function scopeBefore(Builder $query, $timestamp): Builder
    {
        return $query->where('created_at', '<', $timestamp);
    }

    // Helper Methods
    public function isText(): bool
    {
        return $this->type === self::TYPE_TEXT;
    }

    public function isMedia(): bool
    {
        return in_array($this->type, [
            self::TYPE_IMAGE,
            self::TYPE_VIDEO,
            self::TYPE_VOICE,
            self::TYPE_DOCUMENT,
        ]);
    }

    public function isProductCard(): bool
    {
        return $this->type === self::TYPE_PRODUCT_CARD;
    }

    public function isOrderCard(): bool
    {
        return $this->type === self::TYPE_ORDER_CARD;
    }

    public function isSystem(): bool
    {
        return $this->type === self::TYPE_SYSTEM;
    }

    public function markAsDelivered(): void
    {
        if (!$this->delivered_at) {
            $this->update(['delivered_at' => now()]);
        }
    }

    public function markAsRead(string $userId): void
    {
        ChatMessageRead::firstOrCreate(
            ['message_id' => $this->id, 'user_id' => $userId],
            ['read_at' => now()]
        );

        if (!$this->read_at) {
            $this->update(['read_at' => now()]);
        }
    }

    public function addReaction(string $userId, string $emoji): void
    {
        $reactions = $this->reactions ?? [];
        $reactions[$userId] = $emoji;
        $this->update(['reactions' => $reactions]);
    }

    public function removeReaction(string $userId): void
    {
        $reactions = $this->reactions ?? [];
        unset($reactions[$userId]);
        $this->update(['reactions' => $reactions]);
    }

    public function softDelete(): void
    {
        $this->update([
            'is_deleted' => true,
            'deleted_at' => now(),
            'content' => 'This message was deleted',
        ]);
    }

    public function flagAsSuspicious(string $reason): void
    {
        $this->update([
            'flagged_suspicious' => true,
            'flag_reason' => $reason,
        ]);
    }

    public function getPreviewAttribute(): string
    {
        if ($this->is_deleted) {
            return 'This message was deleted';
        }

        return match ($this->type) {
            self::TYPE_TEXT => substr($this->content ?? '', 0, 100),
            self::TYPE_IMAGE => '📷 Photo',
            self::TYPE_VIDEO => '🎥 Video',
            self::TYPE_VOICE => '🎤 Voice message',
            self::TYPE_DOCUMENT => '📄 Document',
            self::TYPE_LOCATION => '📍 Location',
            self::TYPE_PRODUCT_CARD => '🛍️ Product',
            self::TYPE_ORDER_CARD => '📦 Order',
            self::TYPE_PAYMENT_REQUEST => '💰 Payment request',
            self::TYPE_PROFILE_CARD => '👤 Profile',
            self::TYPE_SYSTEM => 'ℹ️ System message',
            self::TYPE_AI_RESPONSE => '🤖 AI response',
            default => 'Message',
        };
    }
}