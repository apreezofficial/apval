<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('split_buy_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('creator_id')->index();
            $table->uuid('product_id')->index();

            $table->decimal('quantity', 20, 4);
            $table->decimal('total_amount', 20, 2);
            $table->decimal('current_amount', 20, 2)->default(0);

            $table->enum('status', ['pending', 'completed', 'expired', 'cancelled'])->default('pending')->index();
            $table->timestamp('expires_at')->index();

            $table->timestamps();

            $table->foreign('creator_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });

        Schema::create('split_buy_contributions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('split_buy_session_id')->index();
            $table->uuid('user_id')->index();

            $table->decimal('amount', 20, 2);
            $table->enum('status', ['paid', 'refunded'])->default('paid');
            $table->uuid('transaction_id')->nullable();

            $table->timestamps();

            $table->foreign('split_buy_session_id')->references('id')->on('split_buy_sessions')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('split_buy_contributions');
        Schema::dropIfExists('split_buy_sessions');
    }
};
