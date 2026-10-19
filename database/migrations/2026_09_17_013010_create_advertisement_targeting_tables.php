<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('advertisement_targets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('campaign_id')->constrained('advertisement_campaigns')->cascadeOnDelete();
            $table->json('countries')->nullable();
            $table->json('states')->nullable();
            $table->json('cities')->nullable();
            $table->json('delivery_areas')->nullable();
            $table->unsignedTinyInteger('min_age')->nullable();
            $table->unsignedTinyInteger('max_age')->nullable();
            $table->string('gender')->default('all'); // all, male, female
            $table->json('interests')->nullable();
            $table->json('marketplace_categories')->nullable();
            $table->timestamps();

            $table->unique('campaign_id');
        });

        Schema::create('advertisement_placements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('campaign_id')->constrained('advertisement_campaigns')->cascadeOnDelete();
            $table->foreignUuid('placement_id')->constrained('ad_placements')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['campaign_id', 'placement_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('advertisement_placements');
        Schema::dropIfExists('advertisement_targets');
    }
};