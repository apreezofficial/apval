<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory, HasUuids;
 
    protected $fillable = [
        'user_id',
        'total_amount',
        'status',
        'payment_status',
        'payment_method',
        // New Logistics Fields
        'is_perishable',
        'transport_type',
        'cold_chain_required',
        'estimated_weight_kg',
        'estimated_volume_m3',
        'areaconnect_reference_id',
        'logistics_status',
        'delivery_location_address',
        'delivery_time_window',
        'logistics_fee',
        'cold_room_fee',
        'platform_margin',       
        'areaconnect_payable', 
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'is_perishable' => 'boolean',
        'cold_chain_required' => 'boolean',
        'estimated_weight_kg' => 'decimal:2',
        'estimated_volume_m3' => 'decimal:2',
        'logistics_fee' => 'decimal:2',
        'cold_room_fee' => 'decimal:2',
    ];

    public function buyer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function logisticsRequest()
    {
        return $this->hasOne(LogisticsRequest::class);
    }
}