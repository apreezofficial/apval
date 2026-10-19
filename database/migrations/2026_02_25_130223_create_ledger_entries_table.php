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
        Schema::create('ledger_entries', function ($table) {
            $table->uuid('id')->primary();

            $table->uuid('wallet_id')->index();
            $table->uuid('transaction_id')->index();

            $table->enum('entry_type', ['debit', 'credit'])->index();
            $table->decimal('amount', 20, 8);

            $table->decimal('balance_before', 20, 8);
            $table->decimal('balance_after', 20, 8);

            $table->timestamps();

            $table->foreign('wallet_id')->references('id')->on('wallets');
            $table->foreign('transaction_id')->references('id')->on('transactions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ledger_entries');
    }
};
