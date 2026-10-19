<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeedSavedPost extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'post_id',
        'folder_name',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

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

        static::creating(function ($saved) {
            // Increment post saves count
            if ($saved->post) {
                $saved->post->increment('saves_count');
            }
        });

        static::deleting(function ($saved) {
            // Decrement post saves count
            if ($saved->post) {
                $saved->post->decrement('saves_count');
            }
        });
    }
}