<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('advertisement_reviews', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('campaign_id')->nullable()->constrained('advertisement_campaigns')->nullOnDelete();
            $table->foreignUuid('advertisement_id')->constrained('advertisements')->cascadeOnDelete();
            $table->foreignUuid('admin_id')->constrained('users')->cascadeOnDelete();
            $table->string('action'); // approved, rejected, changes_requested, paused, resumed, suspended, cancelled
            $table->text('reason')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('advertisement_refunds', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('reference')->unique(); // REF-000001
            $table->foreignUuid('campaign_id')->constrained('advertisement_campaigns')->cascadeOnDelete();
            $table->foreignUuid('advertiser_id')->constrained('advertisers')->cascadeOnDelete();
            $table->foreignUuid('payment_id')->constrained('advertisement_payments')->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->string('type'); // full, partial, wallet_credit
            $table->string('reason');
            $table->foreignUuid('admin_id')->constrained('users')->cascadeOnDelete();
            $table->string('status')->default('pending'); // pending, processed, failed
            $table->timestamps();
        });

        Schema::create('advertisement_fraud_flags', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('campaign_id')->constrained('advertisement_campaigns')->cascadeOnDelete();
            $table->string('indicator'); // artificial_clicks, repeated_clicks, bot_traffic, fake_impressions, payment_fraud, suspicious_traffic, misleading_ad, malicious_link
            $table->json('details')->nullable();
            $table->string('status')->default('flagged'); // flagged, reviewed, cleared, confirmed
            $table->timestamps();

            $table->index(['campaign_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('advertisement_fraud_flags');
        Schema::dropIfExists('advertisement_refunds');
        Schema::dropIfExists('advertisement_reviews');
    }
};