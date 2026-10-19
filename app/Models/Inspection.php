<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Inspection extends Model
{
    use HasUuids;

    protected $fillable = ['order_item_id', 'inspector_id', 'status', 'notes', 'evidence'];

    protected $casts = [
        'evidence' => 'array',
    ];

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function inspector()
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }
}
