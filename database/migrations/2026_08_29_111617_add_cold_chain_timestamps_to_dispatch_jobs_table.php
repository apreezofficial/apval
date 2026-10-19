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
        Schema::table('dispatch_jobs', function (Blueprint $table) {
            $table->timestamp('cold_room_entry_at')->nullable()->after('estimated_delivery_at');
            $table->timestamp('cold_room_exit_at')->nullable()->after('cold_room_entry_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dispatch_jobs', function (Blueprint $table) {
            $table->dropColumn(['cold_room_entry_at', 'cold_room_exit_at']);
        });
    }
};
