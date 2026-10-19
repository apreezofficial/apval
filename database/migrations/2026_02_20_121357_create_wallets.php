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
        Schema::create('wallets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->index();

            $table->enum('currency', ['NGN', 'USDT'])->index();

            $table->decimal('balance', 20, 8)->default(0);
            $table->decimal('frozen_balance', 20, 8)->default(0);

            $table->enum('status', ['active', 'frozen'])->default('active')->index();

            $table->timestamps();

            $table->unique(['user_id', 'currency']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};
