<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('advertisement_campaigns', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('reference')->unique(); // CMP-000001
            $table->foreignUuid('advertisement_id')->constrained('advertisements')->cascadeOnDelete();
            $table->foreignUuid('advertiser_id')->constrained('advertisers')->cascadeOnDelete();

            $table->string('objective')->nullable(); // reach, traffic, engagement, sales, awareness
            $table->string('pricing_model')->nullable(); // fixed_package, cpm, cpc, sponsored_listing
            $table->foreignUuid('package_id')->nullable()->constrained('ad_packages')->nullOnDelete();

            $table->decimal('daily_budget', 15, 2)->nullable();
            $table->decimal('total_budget', 15, 2)->default(0);
            $table->decimal('amount_spent', 15, 2)->default(0);

            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();

            $table->string('status')->default('draft');
            $table->timestamp('paused_at')->nullable();
            $table->timestamp('flagged_at')->nullable();
            $table->text('flag_reason')->nullable();

            $table->timestamps();

            $table->unique('advertisement_id'); // one campaign per advertisement
            $table->index(['status', 'start_date', 'end_date']);
            $table->index('advertiser_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('advertisement_campaigns');
    }
};