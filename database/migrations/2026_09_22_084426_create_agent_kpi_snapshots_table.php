<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_kpi_snapshots', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('agent_id')
                ->constrained('agent_profiles')
                ->cascadeOnDelete();

            $table->enum('period_type', ['daily', 'weekly', 'monthly']);
            $table->date('period_start');
            $table->date('period_end');

            $table->unsignedInteger('prospects_contacted')->default(0);
            $table->unsignedInteger('qualified_prospects')->default(0);
            $table->unsignedInteger('registrations')->default(0);
            $table->unsignedInteger('completed_onboardings')->default(0);
            $table->unsignedInteger('activated_users')->default(0);
            $table->unsignedInteger('products_listed')->default(0);
            $table->unsignedInteger('first_transactions')->default(0);
            $table->decimal('transaction_value', 14, 2)->default(0);
            $table->unsignedInteger('follow_ups_completed')->default(0);

            $table->enum('performance_status', ['excellent', 'good', 'average', 'poor'])->nullable();

            $table->timestamps();

            $table->unique(['agent_id', 'period_type', 'period_start']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_kpi_snapshots');
    }
};