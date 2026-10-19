<?php

// app/Models/LogisticsRequest.php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogisticsRequest extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'order_id',
        'pickup_location',
        'dropoff_location',
        'transport_type',
        'cold_chain_required',
        'delivery_time_window',
        'areaconnect_reference_id',
        'assigned_vehicle_type',
        'rider_driver_id',
        'estimated_pickup_time',
        'estimated_delivery_time',
        'status',
        'error_message',
        'request_payload',
        'response_payload',
    ];

    protected $casts = [
        'cold_chain_required' => 'boolean',
        'request_payload' => 'array',
        'response_payload' => 'array',
        'estimated_pickup_time' => 'datetime',
        'estimated_delivery_time' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}