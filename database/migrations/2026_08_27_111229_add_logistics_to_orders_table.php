<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Logistics Classification
            $table->boolean('is_perishable')->default(false);
            $table->string('transport_type')->nullable(); // bike, van, truck
            $table->boolean('cold_chain_required')->default(false);
            
            // Estimated metrics for auto-assignment
            $table->decimal('estimated_weight_kg', 8, 2)->nullable();
            $table->decimal('estimated_volume_m3', 8, 2)->nullable();

            // AreaConnect Integration
            $table->string('areaconnect_reference_id')->nullable()->unique();
            $table->string('logistics_status')->default('pending'); // pending, assigned, picked_up, in_transit, delivered, failed
            
            // Delivery Details
            $table->string('delivery_location_address')->nullable();
            $table->string('delivery_time_window')->nullable();
            
            // Settlement
            $table->decimal('logistics_fee', 10, 2)->default(0);
            $table->decimal('cold_room_fee', 10, 2)->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'is_perishable', 'transport_type', 'cold_chain_required',
                'estimated_weight_kg', 'estimated_volume_m3',
                'areaconnect_reference_id', 'logistics_status',
                'delivery_location_address', 'delivery_time_window',
                'logistics_fee', 'cold_room_fee'
            ]);
        });
    }
};