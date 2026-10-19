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
        if (Schema::hasTable('withdrawal_requests')) {
            return;
        }

        Schema::create('withdrawal_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('reference', 64)->unique(); // AGP_WD_xxx
            $table->string('idempotency_key', 80)->unique(); // client-supplied

            $table->uuid('user_id')->index();
            $table->uuid('wallet_id')->index();

            $table->enum('currency', ['NGN','USDT'])->index();
            $table->decimal('amount', 20, 8);
            $table->decimal('fee', 20, 8)->default(0);
            $table->decimal('net_amount', 20, 8);

            $table->enum('status', [
                'pending_2fa',   // created, needs OTP
                'pending_pin',   // created, needs transaction PIN
                'queued',        // liquidity short
                'processing',    // debit done, payout executing
                'success',
                'failed',
                'reversed',
                'expired',
            ])->index();

            $table->enum('payout_mode', ['monnify','manual'])->default('monnify')->index();

            // bank destination
            $table->string('bank_code', 20)->nullable();
            $table->string('account_number', 20)->nullable();
            $table->string('account_name', 120)->nullable();

            // provider audit
            $table->string('provider', 30)->nullable()->index();
            $table->string('provider_reference', 120)->nullable()->unique();
            $table->json('provider_response')->nullable(); // SANITIZED ONLY

            $table->json('meta')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('wallet_id')->references('id')->on('wallets');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('withdrawal_requests');
    }
};
