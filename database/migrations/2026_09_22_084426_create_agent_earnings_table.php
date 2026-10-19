<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_earnings', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('agent_id')
                ->constrained('agent_profiles')
                ->cascadeOnDelete();

            $table->enum('earning_type', ['activation', 'first_transaction', 'bonus', 'adjustment']);

            // Creates source_type (varchar) and source_id (char(36))
            $table->nullableUuidMorphs('source');

            $table->decimal('amount', 12, 2);
            $table->enum('status', ['pending', 'approved', 'paid', 'reversed'])->default('pending');

            $table->string('description')->nullable();
            $table->timestamp('paid_at')->nullable();

            $table->timestamps();

            $table->index(['agent_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_earnings');
    }
};