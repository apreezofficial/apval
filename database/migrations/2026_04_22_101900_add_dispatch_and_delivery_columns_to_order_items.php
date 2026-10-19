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
            if (!Schema::hasColumn('order_items', 'dispatched_at')) {
                $table->timestamp('dispatched_at')->nullable()->after('prepared_at');
            }
            if (!Schema::hasColumn('order_items', 'delivered_at')) {
                $table->timestamp('delivered_at')->nullable()->after('dispatched_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['dispatched_at', 'delivered_at']);
        });
    }
};
