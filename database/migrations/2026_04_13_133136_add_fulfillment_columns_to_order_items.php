<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('order_items', 'vendor_status')) {
                $table->string('vendor_status')->default('pending')->after('total_price');
            }
            if (!Schema::hasColumn('order_items', 'accepted_at')) {
                $table->timestamp('accepted_at')->nullable()->after('vendor_status');
            }
            if (!Schema::hasColumn('order_items', 'prepared_at')) {
                $table->timestamp('prepared_at')->nullable()->after('accepted_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['vendor_status', 'accepted_at', 'prepared_at']);
        });
    }
};
