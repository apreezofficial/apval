<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class FeedComment extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'post_id',
        'user_id',
        'parent_id',
        'content',
        'status',
        'likes_count',
        'replies_count',
        'is_flagged',
    ];

    protected $casts = [
        'likes_count' => 'integer',
        'replies_count' => 'integer',
        'is_flagged' => 'boolean',
    ];

    // Status Constants
    const STATUS_PUBLISHED = 'published';
    const STATUS_HIDDEN = 'hidden';
    const STATUS_REMOVED = 'removed';

    // Relationships
    public function post(): BelongsTo
    {
        return $this->belongsTo(FeedPost::class, 'post_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(FeedComment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(FeedComment::class, 'parent_id');
    }

    public function likes(): HasMany
    {
        return $this->hasMany(FeedCommentLike::class, 'comment_id');
    }

    public function reports(): MorphMany
    {
        return $this->morphMany(FeedReport::class, 'target');
    }

    // Scopes
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    public function scopeTopLevel(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    // Helper Methods
    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    public function isReply(): bool
    {
        return !is_null($this->parent_id);
    }

    public function isLikedBy(string $userId): bool
    {
        return $this->likes()->where('user_id', $userId)->exists();
    }

    public function like(string $userId): FeedCommentLike
    {
        $like = FeedCommentLike::firstOrCreate([
            'user_id' => $userId,
            'comment_id' => $this->id,
        ]);

        if ($like->wasRecentlyCreated) {
            $this->increment('likes_count');
        }

        return $like;
    }

    public function unlike(string $userId): bool
    {
        $deleted = FeedCommentLike::where('user_id', $userId)
            ->where('comment_id', $this->id)
            ->delete();

        if ($deleted) {
            $this->decrement('likes_count');
        }

        return $deleted > 0;
    }

    public function hide(): void
    {
        $this->update(['status' => self::STATUS_HIDDEN]);
    }

    public function remove(): void
    {
        $this->update(['status' => self::STATUS_REMOVED]);
    }

    // Boot method
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($comment) {
            // Increment post comments count
            if ($comment->post) {
                $comment->post->increment('comments_count');
            }

            // Increment parent reply count if this is a reply
            if ($comment->parent_id && $parent = FeedComment::find($comment->parent_id)) {
                $parent->increment('replies_count');
            }
        });

        static::deleting(function ($comment) {
            // Decrement post comments count
            if ($comment->post) {
                $comment->post->decrement('comments_count');
            }

            // Decrement parent reply count if this is a reply
            if ($comment->parent_id && $parent = FeedComment::find($comment->parent_id)) {
                $parent->decrement('replies_count');
            }
        });
    }
}