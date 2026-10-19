<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'order_id',
        'product_id',
        'vendor_id',
        'quantity',
        'unit_price',
        'total_price',
        'vendor_status',
        'accepted_at',
        'prepared_at',
        'dispatched_at',
        'delivered_at',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'accepted_at' => 'datetime',
        'prepared_at' => 'datetime',
        'dispatched_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    public function inspection()
    {
        return $this->hasOne(Inspection::class);
    }

    public function dispatchJob()
    {
        return $this->hasOne(DispatchJob::class);
    }
}
