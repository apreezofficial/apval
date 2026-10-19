<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DispatchJob extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'order_item_id',
        'area_connect_job_id',
        'tracking_code',
        'status',
        'pickup_address',
        'delivery_address',
        'estimated_delivery_at',
        'last_location',
        'last_status_message',
        'raw_webhook',
        'picked_up_at',
        'in_transit_at',
        'out_for_delivery_at',
        'cold_room_entry_at',
        'cold_room_exit_at',
        'delivered_at',
        'failed_at',
    ];

    protected $casts = [
        'raw_webhook'           => 'array',
        'estimated_delivery_at' => 'datetime',
        'picked_up_at'          => 'datetime',
        'in_transit_at'         => 'datetime',
        'out_for_delivery_at'   => 'datetime',
        'cold_room_entry_at'    => 'datetime', 
        'cold_room_exit_at'     => 'datetime', 
        'delivered_at'          => 'datetime',
        'failed_at'             => 'datetime',
    ];

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }
}
