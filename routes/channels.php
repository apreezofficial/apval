<?php

use Illuminate\Support\Facades\Broadcast;


Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Private channel for conversation messages
Broadcast::channel('chat.conversation.{conversationId}', function ($user, $conversationId) {
    return \App\Models\ChatConversationParticipant::where('conversation_id', $conversationId)
        ->where('user_id', $user->id)
        ->whereNull('left_at')
        ->exists();
});

// Presence channel for online status
Broadcast::channel('chat.presence', function ($user) {
    return [
        'id' => $user->id,
        'name' => $user->first_name . ' ' . $user->last_name,
        'profile_picture' => $user->profile_picture,
    ];
});
