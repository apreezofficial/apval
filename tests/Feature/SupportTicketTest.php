<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupportTicketTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_support_ticket(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $response = $this->postJson('/api/support/tickets', [
            'subject' => 'Payment issue on order',
            'category' => 'transaction',
            'priority' => 'high',
            'message' => 'My wallet was debited twice for order 123.',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'message' => 'Support ticket created successfully.',
                'data' => [
                    'subject' => 'Payment issue on order',
                    'category' => 'transaction',
                    'priority' => 'high',
                    'status' => 'open',
                ],
            ]);

        $this->assertDatabaseHas('support_tickets', [
            'user_id' => $user->id,
            'subject' => 'Payment issue on order',
        ]);
    }

    public function test_user_can_view_their_tickets_and_thread(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $createResponse = $this->postJson('/api/support/tickets', [
            'subject' => 'Account inquiry',
            'category' => 'account',
            'message' => 'How do I change my phone number?',
        ]);

        $ticketId = $createResponse->json('data.id');

        $listResponse = $this->getJson('/api/support/tickets');
        $listResponse->assertStatus(200)
            ->assertJsonPath('data.data.0.subject', 'Account inquiry');

        $showResponse = $this->getJson("/api/support/tickets/{$ticketId}");
        $showResponse->assertStatus(200)
            ->assertJsonPath('data.messages.0.message', 'How do I change my phone number?');
    }

    public function test_user_and_admin_can_exchange_replies_on_ticket(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);

        // User creates ticket
        $this->actingAs($user, 'sanctum');
        $createResponse = $this->postJson('/api/support/tickets', [
            'subject' => 'Delivery delay',
            'category' => 'order',
            'message' => 'Item has not arrived.',
        ]);
        $ticketId = $createResponse->json('data.id');

        // Admin replies to ticket
        $this->actingAs($admin, 'sanctum');
        $adminReplyResponse = $this->postJson("/api/admin/support/tickets/{$ticketId}/reply", [
            'message' => 'We are contacting the dispatch rider now.',
        ]);
        $adminReplyResponse->assertStatus(201);

        $this->assertDatabaseHas('support_tickets', [
            'id' => $ticketId,
            'status' => 'in_progress',
        ]);

        // User replies back
        $this->actingAs($user, 'sanctum');
        $userReplyResponse = $this->postJson("/api/support/tickets/{$ticketId}/reply", [
            'message' => 'Thank you for following up.',
        ]);
        $userReplyResponse->assertStatus(201);

        // Admin resolves ticket
        $this->actingAs($admin, 'sanctum');
        $statusResponse = $this->putJson("/api/admin/support/tickets/{$ticketId}/status", [
            'status' => 'resolved',
        ]);
        $statusResponse->assertStatus(200)
            ->assertJsonPath('data.status', 'resolved');
    }
}
