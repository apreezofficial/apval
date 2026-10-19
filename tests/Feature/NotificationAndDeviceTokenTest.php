<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserDeviceToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Tests\TestCase;

class NotificationAndDeviceTokenTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_user_can_register_device_token()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/userManager/device-token', [
                'token'       => 'fcm_token_sample_12345',
                'device_type' => 'android',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status'  => 'success',
                'message' => 'Device push token registered successfully.',
                'data'    => [
                    'token'       => 'fcm_token_sample_12345',
                    'device_type' => 'android',
                ],
            ]);

        $this->assertDatabaseHas('user_device_tokens', [
            'user_id'     => $this->user->id,
            'token'       => 'fcm_token_sample_12345',
            'device_type' => 'android',
        ]);
    }

    public function test_user_can_remove_device_token()
    {
        UserDeviceToken::create([
            'user_id'     => $this->user->id,
            'token'       => 'fcm_token_to_remove',
            'device_type' => 'ios',
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson('/api/userManager/device-token', [
                'token' => 'fcm_token_to_remove',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status'  => 'success',
                'message' => 'Device push token removed successfully.',
            ]);

        $this->assertDatabaseMissing('user_device_tokens', [
            'token' => 'fcm_token_to_remove',
        ]);
    }

    public function test_user_can_view_in_app_notifications_and_unread_count()
    {
        // Seed a database notification for the user
        $this->user->notifications()->create([
            'id'        => \Illuminate\Support\Str::uuid()->toString(),
            'type'      => 'App\Notifications\OrderPlacedNotification',
            'data'      => ['message' => 'Your order #1001 has been placed.'],
            'read_at'   => null,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/notifications');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data'   => [
                    'unread_count' => 1,
                ],
            ]);
    }

    public function test_user_can_mark_single_notification_as_read()
    {
        $notification = $this->user->notifications()->create([
            'id'      => \Illuminate\Support\Str::uuid()->toString(),
            'type'    => 'App\Notifications\DepositReceivedNotification',
            'data'    => ['message' => 'Wallet deposit of NGN 5,000 confirmed.'],
            'read_at' => null,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/notifications/{$notification->id}/read");

        $response->assertStatus(200)
            ->assertJson([
                'status'  => 'success',
                'message' => 'Notification marked as read.',
            ]);

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_user_can_mark_all_notifications_as_read()
    {
        $this->user->notifications()->create([
            'id'      => \Illuminate\Support\Str::uuid()->toString(),
            'type'    => 'App\Notifications\TransferReceivedNotification',
            'data'    => ['message' => 'Transfer received.'],
            'read_at' => null,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/notifications/read-all');

        $response->assertStatus(200)
            ->assertJson([
                'status'  => 'success',
                'message' => 'All notifications marked as read.',
            ]);

        $this->assertEquals(0, $this->user->unreadNotifications()->count());
    }
}
