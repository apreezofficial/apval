<?php

declare(strict_types=1);

namespace Tests\Feature\AI;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgrinovaFarmTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_and_list_farms(): void
    {
        $user = User::factory()->create();

        $createResponse = $this->actingAs($user)->postJson('/api/v1/agrinova/farm', [
            'name' => 'Green Valley Farm',
            'location' => 'Oyo, Nigeria',
            'size_hectares' => 2.5,
            'crop' => 'Cassava',
            'farming_method' => 'organic',
        ]);

        $createResponse->assertStatus(201)
            ->assertJson(['success' => true]);

        $listResponse = $this->actingAs($user)->getJson('/api/v1/agrinova/farm');

        $listResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertCount(1, $listResponse->json('data'));
    }

    public function test_unauthenticated_user_cannot_access_agrinova(): void
    {
        $response = $this->getJson('/api/v1/agrinova/farm');
        $response->assertStatus(401);
    }
}
