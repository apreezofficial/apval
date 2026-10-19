<?php

declare(strict_types=1);

namespace App\Events\Chat;

use App\Models\ChatMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public ChatMessage $message
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat.conversation.' . $this->message->conversation_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->message->id,
            'conversation_id' => $this->message->conversation_id,
            'sender_id' => $this->message->sender_id,
            'sender_name' => $this->message->sender->first_name . ' ' . $this->message->sender->last_name,
            'sender_profile_picture' => $this->message->sender->profile_picture,
            'type' => $this->message->type,
            'content' => $this->message->content,
            'media_url' => $this->message->media_url ? asset('storage/' . $this->message->media_url) : null,
            'product_id' => $this->message->product_id,
            'order_id' => $this->message->order_id,
            'reactions' => $this->message->reactions ?? [],
            'reply_to_message_id' => $this->message->reply_to_message_id,
            'created_at' => $this->message->created_at->toISOString(),
            'flagged_suspicious' => $this->message->flagged_suspicious,
        ];
    }
}