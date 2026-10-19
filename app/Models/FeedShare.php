<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeedShare extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'post_id',
        'share_type',
        'caption',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    // Share Type Constants
    const TYPE_INTERNAL = 'internal';
    const TYPE_WHATSAPP = 'whatsapp';
    const TYPE_TWITTER = 'twitter';
    const TYPE_FACEBOOK = 'facebook';
    const TYPE_TELEGRAM = 'telegram';
    const TYPE_LINK_COPY = 'link_copy';
    const TYPE_OTHER = 'other';

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(FeedPost::class);
    }

    // Boot method
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($share) {
            // Increment post shares count
            if ($share->post) {
                $share->post->increment('shares_count');
            }
        });
    }
}