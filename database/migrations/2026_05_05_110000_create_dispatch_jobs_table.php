<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('dispatch_jobs')) {
            return;
        }

        Schema::create('dispatch_jobs', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('order_item_id')->unique()->constrained()->onDelete('cascade');

            // Identifiers from AreaConnect
            $table->string('area_connect_job_id')->unique()->nullable();
            $table->string('tracking_code')->unique()->nullable();

            // Internal status mirrors AreaConnect lifecycle
            // created | picked_up | in_transit | out_for_delivery | delivered | failed | returned
            $table->string('status', 40)->default('created');

            // Addresses captured at dispatch time
            $table->text('pickup_address');
            $table->text('delivery_address');

            // AreaConnect-supplied estimates and location updates
            $table->timestamp('estimated_delivery_at')->nullable();
            $table->string('last_location')->nullable();
            $table->string('last_status_message')->nullable();

            // Full raw payload from the last AreaConnect webhook
            $table->json('raw_webhook')->nullable();

            $table->timestamp('picked_up_at')->nullable();
            $table->timestamp('in_transit_at')->nullable();
            $table->timestamp('out_for_delivery_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('failed_at')->nullable();

            $table->timestamps();

            $table->index(['status']);
            $table->index(['tracking_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dispatch_jobs');
    }
};
