<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_daily_reports', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('agent_id')
                ->constrained('agent_profiles')
                ->cascadeOnDelete();

            $table->date('report_date');
            $table->string('territory')->nullable();

            $table->unsignedInteger('calls_count')->default(0);
            $table->unsignedInteger('market_visits_count')->default(0);
            $table->unsignedInteger('new_prospects')->default(0);
            $table->unsignedInteger('qualified_prospects')->default(0);

            $table->unsignedInteger('registrations')->default(0);
            $table->unsignedInteger('completed_registrations')->default(0);
            $table->unsignedInteger('pending_registrations')->default(0);
            $table->unsignedInteger('activated_users')->default(0);

            $table->unsignedInteger('vendors_with_products')->default(0);
            $table->unsignedInteger('products_uploaded_count')->default(0);

            $table->unsignedInteger('first_time_transactions')->default(0);
            $table->unsignedInteger('repeat_transactions')->default(0);

            $table->unsignedInteger('customers_followed_up')->default(0);
            $table->unsignedInteger('customers_requiring_followup')->default(0);

            $table->text('challenges')->nullable();
            $table->text('remarks')->nullable();

            $table->timestamps();

            $table->unique(['agent_id', 'report_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_daily_reports');
    }
};