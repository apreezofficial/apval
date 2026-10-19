<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('advertisements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('reference')->unique(); // ADV-000001
            $table->foreignUuid('advertiser_id')->constrained('advertisers')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('vendor_id')->nullable()->constrained('vendor_profiles')->nullOnDelete();
            $table->foreignUuid('product_id')->nullable()->constrained('products')->nullOnDelete();

            $table->string('type'); // product, vendor, banner, sponsored_post, sponsored_product, video, promotional_offer, push_notification, other
            $table->string('title');
            $table->text('description');
            $table->string('cta');
            $table->string('destination_type')->nullable(); // product, vendor, external, post
            $table->string('destination_url')->nullable();

            $table->string('promo_code')->nullable();
            $table->decimal('discount_percentage', 5, 2)->nullable();
            $table->decimal('discount_amount', 15, 2)->nullable();
            $table->decimal('product_price', 15, 2)->nullable();
            $table->date('offer_start_date')->nullable();
            $table->date('offer_end_date')->nullable();
            $table->string('contact_information')->nullable();

            $table->string('status')->default('draft');
            $table->text('rejection_reason')->nullable();
            $table->text('admin_notes')->nullable();
            $table->foreignUuid('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['advertiser_id', 'status']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('advertisements');
    }
};