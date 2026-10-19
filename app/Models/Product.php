<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'vendor_id',
        'name',
        'description',
        'category',
        'price',
        'unit',
        'stock_quantity',
        'min_order_quantity',
        'pickup_location',
        'is_perishable',
        'logistics_preference',
        'status',
        'weight_kg',
        'volume_m3',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock_quantity' => 'decimal:2',
        'min_order_quantity' => 'decimal:2',
        'weight_kg' => 'decimal:2',
        'volume_m3' => 'decimal:4',
        'is_perishable' => 'boolean',
    ];

    protected $appends = [
        'average_rating',
        'reviews_count',
    ];

    public function getAverageRatingAttribute(): float
    {
        try {
            return (float) round((float) ($this->reviews()->avg('rating') ?? 0), 1);
        } catch (\Throwable $e) {
            return 0.0;
        }
    }

    public function getReviewsCountAttribute(): int
    {
        try {
            return (int) ($this->reviews()->count());
        } catch (\Throwable $e) {
            return 0;
        }
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInStock($query)
    {
        return $query->where('stock_quantity', '>', 0);
    }

    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function inventoryMovements()
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    public function reviews()
    {
        return $this->hasMany(ProductReview::class);
    }
}
