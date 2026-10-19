<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Check if column exists before adding to prevent Duplicate Column error
            if (!Schema::hasColumn('orders', 'logistics_fee')) {
                $table->decimal('logistics_fee', 10, 2)->default(0)->after('total_amount');
            }
            
            if (!Schema::hasColumn('orders', 'cold_room_fee')) {
                $table->decimal('cold_room_fee', 10, 2)->default(0)->after('logistics_fee');
            }

            if (!Schema::hasColumn('orders', 'platform_margin')) {
                $table->decimal('platform_margin', 10, 2)->default(0)->after('cold_room_fee');
            }

            if (!Schema::hasColumn('orders', 'areaconnect_payable')) {
                $table->decimal('areaconnect_payable', 10, 2)->default(0)->after('platform_margin');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['logistics_fee', 'cold_room_fee', 'platform_margin', 'areaconnect_payable']);
        });
    }
};