<?php

declare(strict_types=1);

namespace App\Services\Feed;

use App\Models\FeedPost;
use App\Models\ChatConversation;
use App\Services\Chat\ConversationService;
use Illuminate\Support\Str;

class ChatroomIntegrationService
{
    public function __construct(
        private ConversationService $conversationService
    ) {}

    /**
     * Get or create a chatroom conversation for a feed post
     */
    public function getOrCreateDiscussion(string $postId, string $userId): ChatConversation
    {
        $post = FeedPost::findOrFail($postId);

        // Check if conversation already exists for this post
        $existingConversation = ChatConversation::where('type', 'marketplace')
            ->where('metadata->post_id', $postId)
            ->first();

        if ($existingConversation) {
            return $existingConversation;
        }

        // Create new conversation for post discussion
        return \DB::transaction(function () use ($post, $userId) {
            $conversation = ChatConversation::create([
                'id' => (string) Str::uuid(),
                'type' => 'group',
                'title' => 'Discussion: ' . Str::limit($post->content, 50),
                'description' => 'Chatroom discussion for feed post',
                'metadata' => [
                    'post_id' => $post->id,
                    'source' => 'feed_discussion',
                ],
            ]);

            // Add post author
            $conversation->participants()->create([
                'user_id' => $post->user_id,
                'role' => 'admin',
                'joined_at' => now(),
            ]);

            // Add current user if different
            if ($userId !== $post->user_id) {
                $conversation->participants()->create([
                    'user_id' => $userId,
                    'role' => 'member',
                    'joined_at' => now(),
                ]);
            }

            // Post a system message
            $conversation->messages()->create([
                'id' => (string) Str::uuid(),
                'sender_id' => $post->user_id,
                'type' => 'system',
                'content' => 'Discussion started for this post. Be respectful and stay on topic.',
            ]);

            return $conversation;
        });
    }
}