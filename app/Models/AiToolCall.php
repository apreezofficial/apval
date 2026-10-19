<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiToolCall extends Model {
    use HasUuids;
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'conversation_id', 'message_id', 'tool_name', 'arguments',
        'result', 'execution_time_ms', 'success', 'error_message'
    ];
    protected $casts = [
        'arguments' => 'array',
        'result' => 'array',
        'execution_time_ms' => 'integer',
        'success' => 'boolean',
    ];

    public function conversation(): BelongsTo { return $this->belongsTo(AiConversation::class); }
}