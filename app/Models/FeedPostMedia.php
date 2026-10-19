<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeedPostMedia extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'post_id',
        'media_type',
        'media_url',
        'thumbnail_url',
        'alt_text',
        'mime_type',
        'file_size',
        'width',
        'height',
        'duration',
        'sort_order',
        'metadata',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'duration' => 'integer',
        'sort_order' => 'integer',
        'metadata' => 'array',
    ];

    // Media Type Constants
    const TYPE_IMAGE = 'image';
    const TYPE_VIDEO = 'video';
    const TYPE_GIF = 'gif';
    const TYPE_LINK_PREVIEW = 'link_preview';

    // Relationships
    public function post(): BelongsTo
    {
        return $this->belongsTo(FeedPost::class, 'post_id');
    }

    // Helper Methods
    public function isImage(): bool
    {
        return $this->media_type === self::TYPE_IMAGE;
    }

    public function isVideo(): bool
    {
        return $this->media_type === self::TYPE_VIDEO;
    }

    public function getFullMediaUrlAttribute(): string
    {
        return asset('storage/' . $this->media_url);
    }

    public function getFullThumbnailUrlAttribute(): ?string
    {
        return $this->thumbnail_url ? asset('storage/' . $this->thumbnail_url) : null;
    }
}