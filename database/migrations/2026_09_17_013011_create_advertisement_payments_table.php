<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('advertisement_payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('reference')->unique(); // PAY-000001
            $table->foreignUuid('advertiser_id')->constrained('advertisers')->cascadeOnDelete();
            $table->foreignUuid('campaign_id')->constrained('advertisement_campaigns')->cascadeOnDelete();
            $table->foreignUuid('advertisement_id')->constrained('advertisements')->cascadeOnDelete();
            $table->string('transaction_id')->nullable();

            $table->decimal('amount', 15, 2);
            $table->decimal('gateway_fee', 15, 2)->default(0);
            $table->decimal('net_revenue', 15, 2)->default(0);

            $table->string('payment_method'); // advertising_wallet, agreonpay_wallet, bank_transfer, card, gateway
            $table->string('status')->default('pending'); // pending, successful, failed, cancelled, refunded, partially_refunded
            $table->string('payment_reference')->nullable();

            $table->timestamps();

            $table->index(['advertiser_id', 'status']);
            $table->index('campaign_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('advertisement_payments');
    }
};