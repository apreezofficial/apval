<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('feed_sponsored_posts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('post_id')->constrained('feed_posts')->cascadeOnDelete();
            $table->foreignUuid('advertiser_id')->constrained('users')->cascadeOnDelete();

            $table->string('campaign_name');
            $table->decimal('budget', 12, 2);
            $table->decimal('spent_amount', 12, 2)->default(0);
            $table->date('start_date');
            $table->date('end_date');

            // Targeting
            $table->json('target_location')->nullable();   // ["Lagos", "Oyo"]
            $table->json('target_category')->nullable();   // ["Agriculture", "Business"]
            $table->json('target_audience')->nullable();   // user segments

            // Performance tracking
            $table->integer('impressions')->default(0);
            $table->integer('clicks')->default(0);
            $table->integer('conversions')->default(0);

            // Billing model (Phase 2)
            $table->enum('billing_model', ['cpm', 'cpc', 'fixed'])->default('fixed');
            $table->decimal('cost_per_unit', 10, 2)->nullable();

            $table->enum('status', ['draft', 'pending_approval', 'active', 'paused', 'completed', 'rejected'])->default('draft');

            $table->timestamps();

            $table->index(['status', 'start_date', 'end_date']);
            $table->index('advertiser_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feed_sponsored_posts');
    }
};