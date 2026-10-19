<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('advertising_wallets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('advertiser_id')->constrained('advertisers')->cascadeOnDelete();
            $table->decimal('balance', 15, 2)->default(0);
            $table->timestamps();

            $table->unique('advertiser_id');
        });

        Schema::create('advertising_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('reference')->unique();
            $table->foreignUuid('wallet_id')->constrained('advertising_wallets')->cascadeOnDelete();
            $table->string('type'); // wallet_funding, campaign_payment, campaign_deduction, refund, promotional_credit, bonus_credit, adjustment
            $table->string('direction'); // credit, debit
            $table->decimal('amount', 15, 2);
            $table->decimal('balance_after', 15, 2);
            $table->foreignUuid('campaign_id')->nullable()->constrained('advertisement_campaigns')->nullOnDelete();
            $table->uuid('payment_id')->nullable();
            $table->text('description')->nullable();
            $table->json('meta')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['wallet_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('advertising_transactions');
        Schema::dropIfExists('advertising_wallets');
    }
};