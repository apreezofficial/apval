<?php

declare(strict_types=1);

namespace App\Models\Ads;

use App\Enums\Ads\MediaType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class AdvertisementMedia extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'advertisement_id',
        'type',
        'path',
        'thumbnail_path',
        'mime_type',
        'size_bytes',
        'width',
        'height',
        'duration_seconds',
        'order',
    ];

    protected $casts = [
        'type' => MediaType::class,
        'size_bytes' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'duration_seconds' => 'integer',
        'order' => 'integer',
    ];

    protected $appends = ['url'];

    public function advertisement(): BelongsTo
    {
        return $this->belongsTo(Advertisement::class);
    }

    public function getUrlAttribute(): ?string
    {
        return $this->path ? Storage::disk('public')->url($this->path) : null;
    }
}