<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PromoCode extends Model
{
    protected $fillable = [
        'code', 'description', 'discount_type', 'discount_value',
        'minimum_order_amount', 'maximum_discount_amount',
        'usage_limit', 'usage_count', 'per_user_limit',
        'is_active', 'starts_at', 'expires_at',
    ];

    protected $casts = [
        'discount_value'          => 'decimal:2',
        'minimum_order_amount'    => 'decimal:2',
        'maximum_discount_amount' => 'decimal:2',
        'is_active'               => 'boolean',
        'starts_at'               => 'datetime',
        'expires_at'              => 'datetime',
    ];

    public function usages(): HasMany
    {
        return $this->hasMany(PromoCodeUsage::class);
    }

    /** Check if this code is currently valid (active, within dates, under limit). */
    public function isValid(): bool
    {
        if (!$this->is_active) return false;
        if ($this->starts_at && now()->lt($this->starts_at)) return false;
        if ($this->expires_at && now()->gt($this->expires_at)) return false;
        if ($this->usage_limit !== null && $this->usage_count >= $this->usage_limit) return false;
        return true;
    }

    /** Calculate the discount amount for a given order subtotal. */
    public function calculateDiscount(float $orderTotal): float
    {
        if ($this->discount_type === 'percentage') {
            $discount = $orderTotal * ($this->discount_value / 100);
            if ($this->maximum_discount_amount) {
                $discount = min($discount, $this->maximum_discount_amount);
            }
        } else {
            $discount = $this->discount_value;
        }
        return min($discount, $orderTotal);
    }
}
