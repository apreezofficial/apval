<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['card', 'bank'])->default('card');
            // Card fields
            $table->string('last4', 4)->nullable();
            $table->string('expiry', 7)->nullable();  // MM/YYYY
            $table->string('card_brand')->nullable();  // visa, mastercard, etc.
            $table->string('card_token')->nullable();  // tokenized, never raw
            // Bank fields
            $table->string('bank_name')->nullable();
            $table->string('account_number', 20)->nullable();
            $table->string('account_name')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};

