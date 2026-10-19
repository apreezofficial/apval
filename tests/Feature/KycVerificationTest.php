<?php

namespace Tests\Feature;

use App\Models\KycRecord;
use App\Models\User;
use App\Services\Kyc\BvnVerificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KycVerificationTest extends TestCase
{
    use RefreshDatabase;

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    private function makeUser(array $attrs = []): User
    {
        return User::factory()->create(array_merge(['role' => 'customer'], $attrs));
    }

    private function kycPayload(): array
    {
        return ['bvn' => '12345678901', 'dob' => '1993-10-03'];
    }

    private function mockBvnSuccess(): void
    {
        $this->mock(BvnVerificationService::class, function ($mock) {
            $mock->shouldReceive('verify')->once()->andReturn([
                'ok'     => true,
                'reason' => null,
                'raw'    => ['requestSuccessful' => true, 'responseBody' => ['name' => ['matchStatus' => 'FULL_MATCH']]],
            ]);
        });
    }

    private function mockBvnFailed(): void
    {
        $this->mock(BvnVerificationService::class, function ($mock) {
            $mock->shouldReceive('verify')->once()->andReturn([
                'ok'     => false,
                'reason' => 'KYC_VERIFICATION_FAILED',
                'raw'    => ['requestSuccessful' => false],
            ]);
        });
    }

    private function mockBvnProviderDown(): void
    {
        $this->mock(BvnVerificationService::class, function ($mock) {
            $mock->shouldReceive('verify')->once()->andReturn([
                'ok'     => false,
                'reason' => 'PROVIDER_UNAVAILABLE',
                'raw'    => [],
            ]);
        });
    }

    // -----------------------------------------------------------------------
    // GET /api/userManager/kyc
    // -----------------------------------------------------------------------

    public function test_unauthenticated_user_cannot_access_kyc_status(): void
    {
        $this->getJson('/api/userManager/kyc')->assertStatus(401);
    }

    public function test_user_with_no_kyc_record_gets_pending_response(): void
    {
        $user = $this->makeUser();
        $this->actingAs($user, 'sanctum');

        $this->getJson('/api/userManager/kyc')
            ->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data'   => [
                    'kyc_status'   => 'pending',
                    'level'        => 0,
                    'bvn_verified' => false,
                    'verified_at'  => null,
                ],
            ]);
    }

    public function test_user_can_check_pending_kyc_status(): void
    {
        $user = $this->makeUser();
        KycRecord::create(['user_id' => $user->id, 'level' => 0, 'status' => 'pending']);
        $this->actingAs($user, 'sanctum');

        $this->getJson('/api/userManager/kyc')
            ->assertStatus(200)
            ->assertJsonPath('data.kyc_status', 'pending')
            ->assertJsonPath('data.bvn_verified', false);
    }

    public function test_user_can_check_verified_kyc_status(): void
    {
        $user = $this->makeUser();
        KycRecord::create([
            'user_id'      => $user->id,
            'level'        => 1,
            'status'       => 'verified',
            'bvn_verified' => true,
            'verified_at'  => now(),
        ]);
        $this->actingAs($user, 'sanctum');

        $this->getJson('/api/userManager/kyc')
            ->assertStatus(200)
            ->assertJsonPath('data.kyc_status', 'verified')
            ->assertJsonPath('data.level', 1)
            ->assertJsonPath('data.bvn_verified', true);
    }

    // -----------------------------------------------------------------------
    // POST /api/userManager/kyc — validation
    // -----------------------------------------------------------------------

    public function test_unauthenticated_user_cannot_submit_kyc(): void
    {
        $this->postJson('/api/userManager/kyc', $this->kycPayload())->assertStatus(401);
    }

    public function test_kyc_submission_requires_bvn_and_dob(): void
    {
        $user = $this->makeUser();
        $this->actingAs($user, 'sanctum');

        $this->postJson('/api/userManager/kyc', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['bvn', 'dob']);
    }

    public function test_bvn_must_be_11_digits(): void
    {
        $user = $this->makeUser();
        $this->actingAs($user, 'sanctum');

        $this->postJson('/api/userManager/kyc', ['bvn' => '1234', 'dob' => '1993-10-03'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['bvn']);
    }

    public function test_dob_must_be_in_correct_format(): void
    {
        $user = $this->makeUser();
        $this->actingAs($user, 'sanctum');

        $this->postJson('/api/userManager/kyc', ['bvn' => '12345678901', 'dob' => '03/10/1993'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['dob']);
    }

    // -----------------------------------------------------------------------
    // POST /api/userManager/kyc — happy path
    // -----------------------------------------------------------------------

    public function test_pending_user_can_submit_bvn_and_get_verified(): void
    {
        $user = $this->makeUser();
        KycRecord::create(['user_id' => $user->id, 'level' => 0, 'status' => 'pending']);
        $this->actingAs($user, 'sanctum');
        $this->mockBvnSuccess();

        $this->postJson('/api/userManager/kyc', $this->kycPayload())
            ->assertStatus(200)
            ->assertJson([
                'status'  => 'success',
                'message' => 'KYC verified successfully.',
                'data'    => [
                    'kyc_status'   => 'verified',
                    'level'        => 1,
                    'bvn_verified' => true,
                ],
            ]);

        $this->assertDatabaseHas('kyc_records', [
            'user_id'      => $user->id,
            'status'       => 'verified',
            'bvn_verified' => true,
            'level'        => 1,
        ]);
    }

    public function test_rejected_user_can_resubmit_and_get_verified(): void
    {
        $user = $this->makeUser();
        KycRecord::create(['user_id' => $user->id, 'level' => 0, 'status' => 'rejected', 'bvn_verified' => false]);
        $this->actingAs($user, 'sanctum');
        $this->mockBvnSuccess();

        $this->postJson('/api/userManager/kyc', $this->kycPayload())
            ->assertStatus(200)
            ->assertJsonPath('data.kyc_status', 'verified');

        $this->assertDatabaseHas('kyc_records', [
            'user_id' => $user->id,
            'status'  => 'verified',
        ]);
    }

    public function test_user_without_kyc_record_gets_one_created_on_submission(): void
    {
        $user = $this->makeUser();
        $this->actingAs($user, 'sanctum');
        $this->mockBvnSuccess();

        $this->assertDatabaseMissing('kyc_records', ['user_id' => $user->id]);

        $this->postJson('/api/userManager/kyc', $this->kycPayload())
            ->assertStatus(200);

        $this->assertDatabaseHas('kyc_records', ['user_id' => $user->id, 'status' => 'verified']);
    }

    // -----------------------------------------------------------------------
    // POST /api/userManager/kyc — guard: already verified
    // -----------------------------------------------------------------------

    public function test_already_verified_user_cannot_resubmit_kyc(): void
    {
        $user = $this->makeUser();
        KycRecord::create([
            'user_id'      => $user->id,
            'level'        => 1,
            'status'       => 'verified',
            'bvn_verified' => true,
            'verified_at'  => now(),
        ]);
        $this->actingAs($user, 'sanctum');

        $this->postJson('/api/userManager/kyc', $this->kycPayload())
            ->assertStatus(422)
            ->assertJsonPath('code', 'KYC_ALREADY_VERIFIED');
    }

    // -----------------------------------------------------------------------
    // POST /api/userManager/kyc — BVN mismatch
    // -----------------------------------------------------------------------

    public function test_bvn_mismatch_sets_status_to_rejected(): void
    {
        $user = $this->makeUser();
        KycRecord::create(['user_id' => $user->id, 'level' => 0, 'status' => 'pending']);
        $this->actingAs($user, 'sanctum');
        $this->mockBvnFailed();

        $this->postJson('/api/userManager/kyc', $this->kycPayload())
            ->assertStatus(422)
            ->assertJsonPath('code', 'KYC_VERIFICATION_FAILED');

        $this->assertDatabaseHas('kyc_records', [
            'user_id' => $user->id,
            'status'  => 'rejected',
        ]);
    }

    // -----------------------------------------------------------------------
    // POST /api/userManager/kyc — provider unavailable
    // -----------------------------------------------------------------------

    public function test_provider_unavailable_returns_503_and_does_not_change_status(): void
    {
        $user = $this->makeUser();
        KycRecord::create(['user_id' => $user->id, 'level' => 0, 'status' => 'pending']);
        $this->actingAs($user, 'sanctum');
        $this->mockBvnProviderDown();

        $this->postJson('/api/userManager/kyc', $this->kycPayload())
            ->assertStatus(503)
            ->assertJsonPath('code', 'PROVIDER_UNAVAILABLE');

        // Status must remain 'pending' — not changed to 'rejected'
        $this->assertDatabaseHas('kyc_records', [
            'user_id' => $user->id,
            'status'  => 'pending',
        ]);
    }

    public function test_provider_unavailable_on_rejected_user_does_not_change_status(): void
    {
        $user = $this->makeUser();
        KycRecord::create(['user_id' => $user->id, 'level' => 0, 'status' => 'rejected']);
        $this->actingAs($user, 'sanctum');
        $this->mockBvnProviderDown();

        $this->postJson('/api/userManager/kyc', $this->kycPayload())
            ->assertStatus(503);

        $this->assertDatabaseHas('kyc_records', [
            'user_id' => $user->id,
            'status'  => 'rejected',
        ]);
    }
}
