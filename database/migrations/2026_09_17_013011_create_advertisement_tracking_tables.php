<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('advertisement_impressions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('campaign_id')->constrained('advertisement_campaigns')->cascadeOnDelete();
            $table->foreignUuid('advertisement_id')->constrained('advertisements')->cascadeOnDelete();
            $table->foreignUuid('placement_id')->nullable()->constrained('ad_placements')->nullOnDelete();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['campaign_id', 'created_at']);
            $table->index(['campaign_id', 'user_id']);
        });

        Schema::create('advertisement_clicks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('campaign_id')->constrained('advertisement_campaigns')->cascadeOnDelete();
            $table->foreignUuid('advertisement_id')->constrained('advertisements')->cascadeOnDelete();
            $table->foreignUuid('placement_id')->nullable()->constrained('ad_placements')->nullOnDelete();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['campaign_id', 'created_at']);
            $table->index(['ip_address', 'created_at']);
        });

        Schema::create('advertisement_conversions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('campaign_id')->constrained('advertisement_campaigns')->cascadeOnDelete();
            $table->foreignUuid('advertisement_id')->constrained('advertisements')->cascadeOnDelete();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('conversion_type'); // product_view, store_visit, order, purchase, offer_claim, registration
            $table->string('reference_id')->nullable();
            $table->decimal('value', 15, 2)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['campaign_id', 'conversion_type']);
        });

        Schema::create('advertisement_reports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('campaign_id')->constrained('advertisement_campaigns')->cascadeOnDelete();
            $table->date('date');
            $table->unsignedInteger('impressions')->default(0);
            $table->unsignedInteger('clicks')->default(0);
            $table->unsignedInteger('conversions')->default(0);
            $table->decimal('amount_spent', 15, 2)->default(0);
            $table->timestamps();

            $table->unique(['campaign_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('advertisement_reports');
        Schema::dropIfExists('advertisement_conversions');
        Schema::dropIfExists('advertisement_clicks');
        Schema::dropIfExists('advertisement_impressions');
    }
};