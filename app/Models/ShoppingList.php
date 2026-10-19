<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShoppingList extends Model {
    use HasUuids;
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'user_id', 'title', 'items', 'total_estimated_cost',
        'currency', 'purchased', 'linked_order_id'
    ];
    protected $casts = [
        'items' => 'array',
        'total_estimated_cost' => 'decimal:2',
        'purchased' => 'boolean',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}