<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\KycRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KycUpgradeTest extends TestCase
{
    use RefreshDatabase;

    // ---------------------------------------------------------------
    // TIER 1 → 2 (User submits ID + utility bill)
    // ---------------------------------------------------------------

    public function test_tier1_user_can_submit_tier2_upgrade_documents(): void
    {
        $user = $this->makeVerifiedUser(level: 1);
        $this->actingAs($user, 'sanctum');

        $response = $this->postJson('/api/userManager/kyc/upgrade', [
            'id_card_url'      => 'https://cdn.agreonpay.test/id-card.jpg',
            'utility_bill_url' => 'https://cdn.agreonpay.test/utility-bill.jpg',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status'  => 'success',
                'data'    => ['kyc_status' => 'pending_review', 'level' => 1],
            ]);

        $this->assertDatabaseHas('kyc_records', [
            'user_id'          => $user->id,
            'status'           => 'pending_review',
            'id_card_url'      => 'https://cdn.agreonpay.test/id-card.jpg',
            'utility_bill_url' => 'https://cdn.agreonpay.test/utility-bill.jpg',
        ]);
    }

    public function test_tier2_upgrade_requires_both_documents(): void
    {
        $user = $this->makeVerifiedUser(level: 1);
        $this->actingAs($user, 'sanctum');

        $response = $this->postJson('/api/userManager/kyc/upgrade', [
            'id_card_url' => 'https://cdn.agreonpay.test/id-card.jpg',
            // missing utility_bill_url
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['utility_bill_url']);
    }

    public function test_unverified_user_cannot_submit_upgrade(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $response = $this->postJson('/api/userManager/kyc/upgrade', [
            'id_card_url'      => 'https://cdn.agreonpay.test/id-card.jpg',
            'utility_bill_url' => 'https://cdn.agreonpay.test/utility-bill.jpg',
        ]);

        $response->assertStatus(422)
            ->assertJson(['code' => 'KYC_NOT_VERIFIED']);
    }

    // ---------------------------------------------------------------
    // TIER 2 → 3 (User submits CAC corporate docs)
    // ---------------------------------------------------------------

    public function test_tier2_user_can_submit_tier3_corporate_upgrade(): void
    {
        $user = $this->makeVerifiedUser(level: 2);
        $this->actingAs($user, 'sanctum');

        $response = $this->postJson('/api/userManager/kyc/upgrade', [
            'business_name'    => 'FarmFresh Agro Ltd',
            'cac_number'       => 'RC-123456',
            'cac_document_url' => 'https://cdn.agreonpay.test/cac-cert.pdf',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data'   => ['kyc_status' => 'pending_review', 'level' => 2],
            ]);

        $this->assertDatabaseHas('kyc_records', [
            'user_id'          => $user->id,
            'status'           => 'pending_review',
            'business_name'    => 'FarmFresh Agro Ltd',
            'cac_number'       => 'RC-123456',
        ]);
    }

    public function test_tier3_user_gets_max_tier_message(): void
    {
        $user = $this->makeVerifiedUser(level: 3);
        $this->actingAs($user, 'sanctum');

        $response = $this->postJson('/api/userManager/kyc/upgrade', [
            'business_name'    => 'Top Agro Corp',
            'cac_number'       => 'RC-999999',
            'cac_document_url' => 'https://cdn.agreonpay.test/cac2.pdf',
        ]);

        $response->assertStatus(422)
            ->assertJson(['code' => 'KYC_MAX_TIER']);
    }

    // ---------------------------------------------------------------
    // ADMIN — Approve
    // ---------------------------------------------------------------

    public function test_admin_can_approve_pending_kyc_and_bumps_tier(): void
    {
        $user  = $this->makeVerifiedUser(level: 1);
        $admin = User::factory()->create(['role' => 'admin']);

        // User submits Tier 2 documents
        $user->kycRecord->update([
            'status'           => 'pending_review',
            'id_card_url'      => 'https://cdn.agreonpay.test/id.jpg',
            'utility_bill_url' => 'https://cdn.agreonpay.test/bill.jpg',
        ]);

        $this->actingAs($admin, 'sanctum');

        $response = $this->postJson("/api/admin/kyc/{$user->kycRecord->id}/approve");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data'   => ['new_level' => 2, 'status' => 'verified'],
            ]);

        $this->assertDatabaseHas('kyc_records', [
            'id'          => $user->kycRecord->id,
            'level'       => 2,
            'status'      => 'verified',
            'reviewed_by' => $admin->id,
        ]);
    }

    public function test_admin_can_reject_pending_kyc_with_reason(): void
    {
        $user  = $this->makeVerifiedUser(level: 1);
        $admin = User::factory()->create(['role' => 'admin']);

        $user->kycRecord->update(['status' => 'pending_review']);

        $this->actingAs($admin, 'sanctum');

        $response = $this->postJson("/api/admin/kyc/{$user->kycRecord->id}/reject", [
            'reason' => 'ID card photo is blurry and unreadable.',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data'   => ['status' => 'upgrade_rejected'],
            ]);

        $this->assertDatabaseHas('kyc_records', [
            'id'               => $user->kycRecord->id,
            'status'           => 'upgrade_rejected',
            'rejection_reason' => 'ID card photo is blurry and unreadable.',
        ]);
    }

    public function test_admin_cannot_approve_non_pending_kyc(): void
    {
        $user  = $this->makeVerifiedUser(level: 1);
        $admin = User::factory()->create(['role' => 'admin']);

        // Still in 'verified' status (not pending_review)
        $this->actingAs($admin, 'sanctum');

        $response = $this->postJson("/api/admin/kyc/{$user->kycRecord->id}/approve");

        $response->assertStatus(422)
            ->assertJson(['code' => 'KYC_NOT_PENDING']);
    }

    public function test_non_admin_cannot_access_admin_kyc_endpoints(): void
    {
        $user = $this->makeVerifiedUser(level: 1);
        $user->kycRecord->update(['status' => 'pending_review']);

        $this->actingAs($user, 'sanctum');

        $this->getJson('/api/admin/kyc/pending')->assertStatus(403);
        $this->postJson("/api/admin/kyc/{$user->kycRecord->id}/approve")->assertStatus(403);
    }

    public function test_admin_can_list_pending_kyc_records(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // Create 2 pending records
        $u1 = $this->makeVerifiedUser(level: 1);
        $u2 = $this->makeVerifiedUser(level: 2);
        $u1->kycRecord->update(['status' => 'pending_review']);
        $u2->kycRecord->update(['status' => 'pending_review']);

        // Create 1 already verified record (should not appear)
        $this->makeVerifiedUser(level: 1);

        $this->actingAs($admin, 'sanctum');

        $response = $this->getJson('/api/admin/kyc/pending');

        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);

        $this->assertCount(2, $response->json('data.data'));
    }

    // ---------------------------------------------------------------
    // HELPER
    // ---------------------------------------------------------------

    private function makeVerifiedUser(int $level): User
    {
        $user = User::factory()->create();

        KycRecord::create([
            'user_id'      => $user->id,
            'status'       => 'verified',
            'level'        => $level,
            'bvn_verified' => true,
            'verified_at'  => now(),
        ]);

        return $user->load('kycRecord');
    }
}
