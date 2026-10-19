<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiUsage extends Model {
    use HasUuids;
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'user_id', 'conversation_id', 'model', 'prompt_tokens',
        'completion_tokens', 'total_tokens', 'estimated_cost_usd',
        'response_time_ms', 'success', 'intent_category'
    ];
    protected $casts = [
        'prompt_tokens' => 'integer',
        'completion_tokens' => 'integer',
        'total_tokens' => 'integer',
        'estimated_cost_usd' => 'decimal:6',
        'response_time_ms' => 'integer',
        'success' => 'boolean',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}