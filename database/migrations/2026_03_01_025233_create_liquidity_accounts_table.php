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
        if (Schema::hasTable('liquidity_accounts')) {
            return;
        }

        Schema::create('liquidity_accounts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('currency', ['NGN','USDT'])->unique();
            $table->decimal('available_balance', 20, 8)->default(0);
            $table->decimal('reserved_balance', 20, 8)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('liquidity_accounts');
    }
};
