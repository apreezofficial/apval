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
        Schema::create('virtual_accounts', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('user_id')->index();
            $table->uuid('wallet_id')->index();

            $table->string('provider', 30)->default('monnify');
            $table->enum('currency', ['NGN'])->default('NGN');

            $table->string('account_reference', 80)->unique();
            $table->string('reservation_reference', 120)->nullable();

            $table->string('account_name', 140)->nullable();
            $table->json('accounts')->nullable();

            $table->enum('status', ['active', 'inactive'])->default('active')->index();
            $table->json('provider_response')->nullable();

            $table->timestamps();

            $table->unique(['user_id', 'provider', 'currency'], 'va_user_provider_currency_unique');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('wallet_id')->references('id')->on('wallets')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('virtual_accounts');
    }
};
