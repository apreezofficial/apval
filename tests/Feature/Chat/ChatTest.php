<?php

declare(strict_types=1);

namespace Tests\Feature\Chat;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatTest extends TestCase
{
    use RefreshDatabase;

    public function test_buyer_and_seller_can_start_chat_and_exchange_messages(): void
    {
        $buyer = User::factory()->create();
        $seller = User::factory()->create();

        // 1. Buyer starts direct chat with Seller
        $this->actingAs($buyer, 'sanctum');

        $startResponse = $this->postJson('/api/chat/conversations', [
            'recipient_id' => $seller->id,
        ]);

        $startResponse->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'type' => 'direct',
                ],
            ]);

        $conversationId = $startResponse->json('data.id');

        // 2. Buyer sends a message
        $sendResponse = $this->postJson("/api/chat/conversations/{$conversationId}/messages", [
            'body' => 'Hello seller, is this pepper fresh?',
        ]);

        $sendResponse->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'message' => 'Message sent.',
                'data' => [
                    'body' => 'Hello seller, is this pepper fresh?',
                ],
            ]);

        // 3. Seller views messages and replies
        $this->actingAs($seller, 'sanctum');

        $messagesResponse = $this->getJson("/api/chat/conversations/{$conversationId}/messages");
        $messagesResponse->assertStatus(200)
            ->assertJsonPath('data.data.0.body', 'Hello seller, is this pepper fresh?');

        $replyResponse = $this->postJson("/api/chat/conversations/{$conversationId}/messages", [
            'body' => 'Yes, harvested this morning!',
        ]);

        $replyResponse->assertStatus(201);

        // 4. Mark conversation as read
        $readResponse = $this->postJson("/api/chat/conversations/{$conversationId}/read");
        $readResponse->assertStatus(200);
    }
}
