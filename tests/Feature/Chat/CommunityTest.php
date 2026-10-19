<?php

declare(strict_types=1);

namespace Tests\Feature\Chat;

use App\Models\Community;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $member;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'first_name' => 'Tunde',
            'last_name' => 'Farming',
            'role' => 'vendor',
        ]);

        $this->member = User::factory()->create([
            'first_name' => 'Chidi',
            'last_name' => 'Buyer',
            'role' => 'buyer',
        ]);
    }

    public function test_user_can_create_community_and_list_with_category_filter(): void
    {
        $createResponse = $this->actingAs($this->admin)->postJson('/api/communities', [
            'name' => 'Poultry Farmers Hub',
            'category' => 'Livestock',
            'description' => 'Discussions on broilers, layers, and poultry management.',
        ]);

        $createResponse->assertStatus(201)
            ->assertJson(['status' => 'success', 'data' => ['name' => 'Poultry Farmers Hub']]);

        $communityId = $createResponse->json('data.id');

        // List with category filter
        $listResponse = $this->actingAs($this->member)->getJson('/api/communities?category=Livestock');
        $listResponse->assertStatus(200)
            ->assertJson(['status' => 'success']);

        $this->assertCount(1, $listResponse->json('data.data'));
        $this->assertEquals($communityId, $listResponse->json('data.data.0.id'));
    }

    public function test_user_can_join_and_leave_community(): void
    {
        $community = Community::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'name' => 'Fish Farmers Nigeria',
            'slug' => 'fish-farmers-ng',
            'category' => 'Aquaculture',
            'owner_id' => $this->admin->id,
            'members_count' => 1,
        ]);

        // Join
        $joinResponse = $this->actingAs($this->member)->postJson("/api/communities/{$community->id}/join");
        $joinResponse->assertStatus(200)
            ->assertJson(['status' => 'success', 'message' => 'Successfully joined community.']);

        $this->assertDatabaseHas('community_members', [
            'community_id' => $community->id,
            'user_id' => $this->member->id,
        ]);

        // Leave
        $leaveResponse = $this->actingAs($this->member)->postJson("/api/communities/{$community->id}/leave");
        $leaveResponse->assertStatus(200)
            ->assertJson(['status' => 'success', 'message' => 'Successfully left community.']);

        $this->assertDatabaseMissing('community_members', [
            'community_id' => $community->id,
            'user_id' => $this->member->id,
        ]);
    }

    public function test_community_poll_creation_and_voting(): void
    {
        $createResponse = $this->actingAs($this->admin)->postJson('/api/communities', [
            'name' => 'Grain Farmers',
            'category' => 'Cereals',
            'description' => 'Rice and maize cultivation.',
        ]);
        $communityId = $createResponse->json('data.id');

        // Join
        $this->actingAs($this->member)->postJson("/api/communities/{$communityId}/join");

        // Create poll
        $pollResponse = $this->actingAs($this->admin)->postJson("/api/communities/{$communityId}/polls", [
            'question' => 'Which crop are you currently farming?',
            'options' => ['Maize', 'Rice', 'Cassava'],
        ]);

        $pollResponse->assertStatus(201)
            ->assertJson(['status' => 'success', 'data' => ['question' => 'Which crop are you currently farming?']]);

        $pollId = $pollResponse->json('data.id');
        $optionId = $pollResponse->json('data.options.0.id');

        // Vote
        $voteResponse = $this->actingAs($this->member)->postJson("/api/communities/polls/{$pollId}/vote", [
            'option_id' => $optionId,
        ]);

        $voteResponse->assertStatus(200)
            ->assertJson(['status' => 'success', 'message' => 'Vote recorded successfully.']);

        $this->assertDatabaseHas('poll_votes', [
            'poll_id' => $pollId,
            'poll_option_id' => $optionId,
            'user_id' => $this->member->id,
        ]);
    }

    public function test_community_admin_can_update_role_and_mute_member(): void
    {
        $createResponse = $this->actingAs($this->admin)->postJson('/api/communities', [
            'name' => 'Vegetable Hub',
            'category' => 'Horticulture',
        ]);
        $communityId = $createResponse->json('data.id');

        // Member joins
        $this->actingAs($this->member)->postJson("/api/communities/{$communityId}/join");

        // Admin updates role to moderator
        $roleResponse = $this->actingAs($this->admin)->putJson("/api/communities/{$communityId}/members/{$this->member->id}/role", [
            'role' => 'moderator',
        ]);
        $roleResponse->assertStatus(200)
            ->assertJson(['status' => 'success', 'data' => ['role' => 'moderator']]);

        // Admin mutes member for 60 minutes
        $muteResponse = $this->actingAs($this->admin)->postJson("/api/communities/{$communityId}/members/{$this->member->id}/mute", [
            'minutes' => 60,
        ]);
        $muteResponse->assertStatus(200)
            ->assertJson(['status' => 'success', 'data' => ['is_muted' => true]]);
    }
}
