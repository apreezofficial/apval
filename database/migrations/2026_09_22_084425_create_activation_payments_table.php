<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activation_payments', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignUuid('agent_id')
                ->nullable()
                ->constrained('agent_profiles')
                ->nullOnDelete();

            $table->foreignUuid('lead_id')
                ->nullable()
                ->constrained('leads')
                ->nullOnDelete();

            $table->decimal('amount', 12, 2)->default(2000.00);
            $table->string('currency', 3)->default('NGN');

            $table->string('reference')->unique();

            $table->string('provider')->default('monnify');
            $table->string('provider_reference')->nullable()->index();

            $table->enum('status', ['pending', 'paid', 'failed', 'cancelled'])->default('pending');

            $table->string('receipt_number')->nullable()->unique();
            $table->timestamp('paid_at')->nullable();

            $table->json('meta')->nullable();

            $table->timestamps();

            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activation_payments');
    }
};