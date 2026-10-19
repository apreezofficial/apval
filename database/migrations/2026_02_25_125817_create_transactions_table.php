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
        Schema::create('transactions', function ($table) {
            $table->uuid('id')->primary();

            $table->string('reference', 64)->unique(); // e.g. AGP_TRX_xxx
            $table->string('idempotency_key', 80)->unique();

            $table->enum('type', ['transfer'])->index();
            $table->enum('status', ['pending', 'success', 'failed'])->index();

            $table->uuid('initiated_by')->index(); // user id
            $table->uuid('from_wallet_id')->nullable()->index();
            $table->uuid('to_wallet_id')->nullable()->index();

            $table->enum('currency', ['NGN', 'USDT'])->index();
            $table->decimal('amount', 20, 8);

            $table->json('meta')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->foreign('initiated_by')->references('id')->on('users');
            $table->foreign('from_wallet_id')->references('id')->on('wallets');
            $table->foreign('to_wallet_id')->references('id')->on('wallets');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
