<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('logistics_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('order_id')->constrained('orders')->onDelete('cascade');
            
            // Request Details (Per Section 3 of PDF)
            $table->string('pickup_location')->comment('Seller/farm/warehouse address or coordinates');
            $table->string('dropoff_location')->comment('Buyer delivery address or coordinates');
            $table->string('transport_type')->comment('bike, van, or truck');
            $table->boolean('cold_chain_required')->default(false);
            $table->string('delivery_time_window')->nullable();

            // AreaConnect API Response Details
            $table->string('areaconnect_reference_id')->nullable()->unique();
            $table->string('assigned_vehicle_type')->nullable();
            $table->string('rider_driver_id')->nullable();
            
            // Timestamps from AreaConnect
            $table->timestamp('estimated_pickup_time')->nullable();
            $table->timestamp('estimated_delivery_time')->nullable();
            
            // Status Tracking (Per Section 4, 5, 6 of PDF)
            $table->string('status')->default('initiated')->comment('initiated, assigned, picked_up, in_transit, delivered, failed');
            $table->text('error_message')->nullable();
            
            // Raw Payloads for debugging and audit
            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('logistics_requests');
    }
};