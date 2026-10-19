<?php

declare(strict_types=1);

namespace App\Services\Chat;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Services\AI\AgrinovaOrchestrator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AgrinovaChatService
{
    public function __construct(
        private AgrinovaOrchestrator $orchestrator
    ) {}

    /**
     * Handle an @Agrinova mention in a chat
     */
    public function handleMention(string $conversationId, string $mentionText, ?string $replyToMessageId = null): ChatMessage
    {
        $user = Auth::user();
        $conversation = ChatConversation::findOrFail($conversationId);
        
        // 1. Gather Context for the AI
        $context = [];
        if ($conversation->type === 'group' || $conversation->type === 'community') {
            $community = $conversation->community;
            if ($community) {
                $context['community_category'] = $community->category;
                $context['community_name'] = $community->name;
            }
        } elseif ($conversation->related_product_id) {
            $context['related_product_id'] = $conversation->related_product_id;
        }

        // 2. Call the AI Orchestrator (configured for 'chat' source)
        $response = $this->orchestrator->processMessage(
            $user->id,
            $mentionText,
            null, // Start a new internal AI thread for this query
            $context,
            'chat' // Tells orchestrator to use chat-optimized system prompt
        );

        // 3. Create the AI Response Message in the Chat
        $systemUserId = config('services.agrinova.system_user_id') ?? env('AGRINOVA_SYSTEM_USER_ID');

        if (!$systemUserId) {
            throw new \Exception('AGRINOVA_SYSTEM_USER_ID is not configured in .env or config/services.php');
        }

        $aiMessage = ChatMessage::create([
            'id' => (string) Str::uuid(),
            'conversation_id' => $conversationId,
            'sender_id' => $systemUserId, // <-- Now guaranteed to have a value
            'type' => ChatMessage::TYPE_AI_RESPONSE,
            'content' => $response['message'] ?? 'I am currently unable to process this request.',
            'reply_to_message_id' => $replyToMessageId,
        ]);

        // 4. Broadcast the real-time event so the UI updates instantly
        event(new \App\Events\Chat\MessageSent($aiMessage));

        return $aiMessage;
    }
}