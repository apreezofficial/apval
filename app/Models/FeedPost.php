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

class FeedPost extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'category_id',
        'post_type',
        'content',
        'hashtags',
        'mentions',
        'product_id',
        'event_id',
        'location',
        'status',
        'is_pinned',
        'is_featured',
        'pinned_at',
        'featured_at',
        'scheduled_at',
        'published_at',
        'likes_count',
        'comments_count',
        'shares_count',
        'saves_count',
        'views_count',
        'is_flagged',
        'flag_reason',
    ];

    protected $casts = [
        'hashtags' => 'array',
        'mentions' => 'array',
        'location' => 'array',
        'is_pinned' => 'boolean',
        'is_featured' => 'boolean',
        'pinned_at' => 'datetime',
        'featured_at' => 'datetime',
        'scheduled_at' => 'datetime',
        'published_at' => 'datetime',
        'likes_count' => 'integer',
        'comments_count' => 'integer',
        'shares_count' => 'integer',
        'saves_count' => 'integer',
        'views_count' => 'integer',
        'is_flagged' => 'boolean',
    ];

    // Post Type Constants
    const TYPE_NORMAL = 'normal';
    const TYPE_IMAGE = 'image';
    const TYPE_VIDEO = 'video';
    const TYPE_PRODUCT = 'product';
    const TYPE_EVENT = 'event';
    const TYPE_ANNOUNCEMENT = 'announcement';
    const TYPE_SPONSORED = 'sponsored';

    // Status Constants
    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING = 'pending';
    const STATUS_PUBLISHED = 'published';
    const STATUS_HIDDEN = 'hidden';
    const STATUS_REJECTED = 'rejected';
    const STATUS_REMOVED = 'removed';

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(FeedCategory::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(FeedPostMedia::class, 'post_id')->orderBy('sort_order');
    }

    public function likes(): HasMany
    {
        return $this->hasMany(FeedLike::class, 'post_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(FeedComment::class, 'post_id')->whereNull('parent_id');
    }

    public function allComments(): HasMany
    {
        return $this->hasMany(FeedComment::class, 'post_id');
    }

    public function shares(): HasMany
    {
        return $this->hasMany(FeedShare::class, 'post_id');
    }

    public function saves(): HasMany
    {
        return $this->hasMany(FeedSavedPost::class, 'post_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function sponsored(): HasMany
    {
        return $this->hasMany(FeedSponsoredPost::class, 'post_id');
    }

    public function reports(): MorphMany
    {
        return $this->morphMany(FeedReport::class, 'target');
    }

    // Scopes
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED)
            ->where(function ($q) {
                $q->whereNull('scheduled_at')
                  ->orWhere('scheduled_at', '<=', now());
            });
    }

    public function scopePinned(Builder $query): Builder
    {
        return $query->where('is_pinned', true);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeByCategory(Builder $query, $categoryId): Builder
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeByUser(Builder $query, $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeWithType(Builder $query, string $type): Builder
    {
        return $query->where('post_type', $type);
    }

    public function scopeNotFlagged(Builder $query): Builder
    {
        return $query->where('is_flagged', false);
    }

    public function scopeWithHashtag(Builder $query, string $hashtag): Builder
    {
        return $query->whereJsonContains('hashtags', $hashtag);
    }

    // Helper Methods
    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    public function isPinned(): bool
    {
        return $this->is_pinned;
    }

    public function isFeatured(): bool
    {
        return $this->is_featured;
    }

    public function isProductPost(): bool
    {
        return $this->post_type === self::TYPE_PRODUCT;
    }

    public function hasMedia(): bool
    {
        return $this->media()->exists();
    }

    public function isLikedBy(string $userId): bool
    {
        return $this->likes()->where('user_id', $userId)->exists();
    }

    public function isSavedBy(string $userId): bool
    {
        return $this->saves()->where('user_id', $userId)->exists();
    }

    public function like(string $userId): FeedLike
    {
        $like = FeedLike::firstOrCreate([
            'user_id' => $userId,
            'post_id' => $this->id,
        ]);

        if ($like->wasRecentlyCreated) {
            $this->increment('likes_count');
        }

        return $like;
    }

    public function unlike(string $userId): bool
    {
        $deleted = FeedLike::where('user_id', $userId)
            ->where('post_id', $this->id)
            ->delete();

        if ($deleted) {
            $this->decrement('likes_count');
        }

        return $deleted > 0;
    }

    public function save_post(string $userId, ?string $folderName = null): FeedSavedPost
    {
        return FeedSavedPost::firstOrCreate([
            'user_id' => $userId,
            'post_id' => $this->id,
        ], ['folder_name' => $folderName]);
    }

    public function unsave(string $userId): bool
    {
        return FeedSavedPost::where('user_id', $userId)
            ->where('post_id', $this->id)
            ->delete() > 0;
    }

    public function pin(): void
    {
        $this->update([
            'is_pinned' => true,
            'pinned_at' => now(),
        ]);
    }

    public function unpin(): void
    {
        $this->update([
            'is_pinned' => false,
            'pinned_at' => null,
        ]);
    }

    public function feature(): void
    {
        $this->update([
            'is_featured' => true,
            'featured_at' => now(),
        ]);
    }

    public function unfeature(): void
    {
        $this->update([
            'is_featured' => false,
            'featured_at' => null,
        ]);
    }

    public function hide(): void
    {
        $this->update(['status' => self::STATUS_HIDDEN]);
    }

    public function publish(): void
    {
        $this->update([
            'status' => self::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);
    }

    public function flag(string $reason): void
    {
        $this->update([
            'is_flagged' => true,
            'flag_reason' => $reason,
        ]);
    }

    public function incrementViewCount(): void
    {
        $this->increment('views_count');
    }

    // Boot method
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($post) {
            if (empty($post->published_at) && $post->status === self::STATUS_PUBLISHED) {
                $post->published_at = now();
            }
        });

        static::deleting(function ($post) {
            // Decrement category post count
            if ($post->category) {
                $post->category->decrementPostCount();
            }
        });
    }
}