<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Farm extends Model {
    use HasUuids, SoftDeletes;
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'user_id', 'name', 'location', 'state', 'size_hectares', 'crop',
        'soil_type', 'soil_ph', 'planting_date', 'expected_harvest',
        'farming_method', 'water_source', 'metadata'
    ];
    protected $casts = [
        'size_hectares' => 'decimal:2',
        'soil_ph' => 'decimal:2',
        'planting_date' => 'date',
        'expected_harvest' => 'date',
        'metadata' => 'array',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}