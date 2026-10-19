<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('advertisers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('business_name');
            $table->string('industry')->nullable();
            $table->string('status')->default('pending'); // pending, approved, rejected, suspended
            $table->json('restricted_categories')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignUuid('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('rejection_reason')->nullable();
            $table->unsignedInteger('violation_count')->default(0);
            $table->timestamps();

            $table->unique('user_id');
            $table->index('status');
        });

        Schema::create('ad_configs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('key')->unique();
            $table->json('value')->nullable();
            $table->string('group')->nullable();
            $table->timestamps();
        });

        Schema::create('ad_placements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('key')->unique(); // home_page, marketplace, food_section...
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('requires_approval')->default(false);
            $table->boolean('is_active')->default(true);
            $table->decimal('price_per_day', 15, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('ad_cta_options', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('label')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('ad_packages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->decimal('price', 15, 2);
            $table->unsignedInteger('duration_hours');
            $table->text('description')->nullable();
            $table->json('placement_keys')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('ad_pricing_models', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('placement_id')->nullable()->constrained('ad_placements')->nullOnDelete();
            $table->string('category')->nullable();
            $table->string('pricing_model'); // cpm, cpc, sponsored_listing
            $table->decimal('rate', 15, 4);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['pricing_model', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ad_pricing_models');
        Schema::dropIfExists('ad_packages');
        Schema::dropIfExists('ad_cta_options');
        Schema::dropIfExists('ad_placements');
        Schema::dropIfExists('ad_configs');
        Schema::dropIfExists('advertisers');
    }
};