<?php

declare(strict_types=1);

namespace Tests\Feature\Chat;

use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Message;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatroomTest extends TestCase
{
    use RefreshDatabase;

    private User $user1;
    private User $user2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user1 = User::factory()->create([
            'first_name' => 'Adewale',
            'last_name' => 'Ayuba',
            'role' => 'buyer',
        ]);

        $this->user2 = User::factory()->create([
            'first_name' => 'Bisi',
            'last_name' => 'Farms',
            'role' => 'vendor',
        ]);
    }

    public function test_user_can_start_direct_conversation_and_list_by_tab(): void
    {
        $response = $this->actingAs($this->user1)->postJson('/api/chat/conversations/direct', [
            'recipient_id' => $this->user2->id,
        ]);

        $response->assertStatus(201)
            ->assertJson(['status' => 'success']);

        $convId = $response->json('data.id');

        // Test tabs
        $listResponse = $this->actingAs($this->user1)->getJson('/api/chat/conversations?tab=direct');
        $listResponse->assertStatus(200)
            ->assertJson(['status' => 'success']);

        $this->assertCount(1, $listResponse->json('data.data'));
        $this->assertEquals($convId, $listResponse->json('data.data.0.id'));
    }

    public function test_user_can_start_marketplace_chat_linked_to_product(): void
    {
        $product = Product::factory()->create([
            'vendor_id' => $this->user2->id,
            'name' => 'Fresh Catfish',
            'price' => 5500,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->user1)->postJson('/api/chat/conversations/marketplace', [
            'product_id' => $product->id,
        ]);

        $response->assertStatus(201)
            ->assertJson(['status' => 'success']);

        $convId = $response->json('data.id');

        // List under marketplace tab
        $listResponse = $this->actingAs($this->user1)->getJson('/api/chat/conversations?tab=marketplace');
        $listResponse->assertStatus(200);
        $this->assertCount(1, $listResponse->json('data.data'));
        $this->assertEquals($convId, $listResponse->json('data.data.0.id'));
    }

    public function test_user_can_send_rich_message_with_scam_warning_detection(): void
    {
        $conv = Conversation::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'type' => 'direct',
            'category' => 'direct',
            'created_by' => $this->user1->id,
        ]);

        ConversationParticipant::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'conversation_id' => $conv->id,
            'user_id' => $this->user1->id,
            'role' => 'owner',
        ]);

        ConversationParticipant::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'conversation_id' => $conv->id,
            'user_id' => $this->user2->id,
            'role' => 'member',
        ]);

        // Suspicious message that attempts to transact off-platform
        $response = $this->actingAs($this->user2)->postJson("/api/chat/conversations/{$conv->id}/messages", [
            'body' => 'Hello, please send money outside AgreonPay to my personal bank account.',
            'type' => 'text',
        ]);

        $response->assertStatus(201);
        $this->assertNotNull($response->json('data.metadata.safety_alert'));
    }

    public function test_user_can_react_pin_and_delete_message(): void
    {
        $conv = Conversation::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'type' => 'direct',
            'category' => 'direct',
            'created_by' => $this->user1->id,
        ]);

        ConversationParticipant::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'conversation_id' => $conv->id,
            'user_id' => $this->user1->id,
            'role' => 'admin',
        ]);

        $message = Message::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'conversation_id' => $conv->id,
            'sender_id' => $this->user1->id,
            'body' => 'Welcome announcement!',
            'type' => 'text',
        ]);

        // React
        $reactResponse = $this->actingAs($this->user1)->postJson("/api/chat/messages/{$message->id}/react", [
            'emoji' => '👍',
        ]);
        $reactResponse->assertStatus(200)
            ->assertJson(['status' => 'success', 'data' => ['action' => 'added']]);

        // Pin
        $pinResponse = $this->actingAs($this->user1)->postJson("/api/chat/messages/{$message->id}/pin", [
            'pin' => true,
        ]);
        $pinResponse->assertStatus(200)
            ->assertJson(['status' => 'success', 'data' => ['is_pinned' => true]]);

        // Delete
        $deleteResponse = $this->actingAs($this->user1)->deleteJson("/api/chat/messages/{$message->id}");
        $deleteResponse->assertStatus(200)
            ->assertJson(['status' => 'success', 'message' => 'Message deleted.']);

        $this->assertSoftDeleted('messages', ['id' => $message->id]);
    }

    public function test_user_can_block_and_unblock(): void
    {
        $blockResponse = $this->actingAs($this->user1)->postJson('/api/chat/block', [
            'user_id' => $this->user2->id,
        ]);
        $blockResponse->assertStatus(200);

        $this->assertDatabaseHas('user_blocks', [
            'blocker_id' => $this->user1->id,
            'blocked_id' => $this->user2->id,
        ]);

        $unblockResponse = $this->actingAs($this->user1)->postJson('/api/chat/unblock', [
            'user_id' => $this->user2->id,
        ]);
        $unblockResponse->assertStatus(200);

        $this->assertDatabaseMissing('user_blocks', [
            'blocker_id' => $this->user1->id,
            'blocked_id' => $this->user2->id,
        ]);
    }

    public function test_user_can_report_conversation(): void
    {
        $conv = Conversation::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'type' => 'direct',
            'created_by' => $this->user1->id,
        ]);

        $reportResponse = $this->actingAs($this->user1)->postJson('/api/chat/report', [
            'reported_user_id' => $this->user2->id,
            'conversation_id' => $conv->id,
            'reason' => 'scam',
            'details' => 'Requested direct payment to outside account.',
        ]);

        $reportResponse->assertStatus(201)
            ->assertJson(['status' => 'success']);

        $this->assertDatabaseHas('chat_reports', [
            'reporter_id' => $this->user1->id,
            'reason' => 'scam',
        ]);
    }
}
