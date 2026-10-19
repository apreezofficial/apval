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
        Schema::create('webhook_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('provider', 30)->index();         // e.g. monnify
            $table->string('event_type', 80)->nullable()->index();

            $table->string('signature_header', 255)->nullable();
            $table->boolean('signature_valid')->default(false)->index();

            $table->string('provider_reference', 120)->nullable()->index(); // paymentReference / transactionReference
            $table->boolean('processed')->default(false)->index();
            $table->timestamp('processed_at')->nullable();

            $table->json('payload')->nullable();             // full raw JSON (audit)
            $table->timestamps();

            // Optional but strong: prevent duplicates per provider_reference (only if you’re sure field is always present)
             $table->unique(['provider','provider_reference']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webhook_logs');
    }
};
