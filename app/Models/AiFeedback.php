<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiFeedback extends Model {
    use HasUuids;
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'user_id', 'conversation_id', 'message_id', 'rating',
        'comment', 'intent_category', 'resolved'
    ];
    protected $casts = ['resolved' => 'boolean'];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function conversation(): BelongsTo { return $this->belongsTo(AiConversation::class); }
    public function message(): BelongsTo { return $this->belongsTo(AiMessage::class); }
}