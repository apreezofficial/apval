<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('loan_reference')->unique();
            $table->decimal('amount', 12, 2);
            $table->unsignedInteger('tenure_months')->default(3);
            $table->decimal('interest_rate', 5, 2)->default(5.00); // 5% monthly / flat
            $table->decimal('total_repayment', 12, 2);
            $table->decimal('amount_repaid', 12, 2)->default(0.00);
            $table->timestamp('due_date')->nullable();
            $table->string('status')->default('under_review'); // pending, under_review, approved, disbursed, rejected, repaid
            $table->string('reason_for_loan')->nullable();
            $table->json('personal_info')->nullable();
            $table->json('employment_info')->nullable();
            $table->json('emergency_contacts')->nullable();
            $table->string('id_card_url', 1000)->nullable();
            $table->string('selfie_url', 1000)->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('disbursed_at')->nullable();
            $table->timestamp('repaid_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
