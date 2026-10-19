<?php

declare(strict_types=1);

namespace Tests\Feature\Agent;

use App\Models\Agent\AgentProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgentAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_agent_can_register_and_sign_in_from_the_mobile_api(): void
    {
        $registration = $this->postJson('/api/marketAgent/auth/register', [
            'first_name' => 'Amina',
            'last_name' => 'Yusuf',
            'email' => 'amina@example.com',
            'phone' => '08012345670',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $registration->assertCreated()
            ->assertJsonPath('data.user.role', 'agent')
            ->assertJsonPath('data.agent_profile.agent_code', fn ($code) => str_starts_with($code, 'AG-'));

        $this->assertDatabaseHas('users', [
            'email' => 'amina@example.com',
            'role' => 'agent',
        ]);
        $this->assertDatabaseHas('agent_profiles', [
            'user_id' => User::where('email', 'amina@example.com')->value('id'),
        ]);

        $this->postJson('/api/marketAgent/auth/login', [
            'login' => 'amina@example.com',
            'password' => 'Password123!',
        ])->assertOk()
            ->assertJsonPath('data.user.email', 'amina@example.com');
    }

    public function test_admin_tokens_cannot_access_agent_mobile_routes(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/marketAgent/profile')
            ->assertForbidden();
    }

    public function test_suspended_agents_cannot_sign_in(): void
    {
        $agent = User::factory()->create(['role' => 'agent', 'status' => 'active']);
        AgentProfile::factory()->create([
            'user_id' => $agent->id,
            'status' => 'suspended',
        ]);

        $this->postJson('/api/marketAgent/auth/login', [
            'login' => $agent->email,
            'password' => 'password',
        ])->assertUnprocessable();
    }
}
