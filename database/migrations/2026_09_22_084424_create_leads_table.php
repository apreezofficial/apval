<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('agent_id')
                ->constrained('agent_profiles')
                ->cascadeOnDelete();

            $table->string('full_name');
            $table->string('phone')->nullable();
            $table->string('business_name')->nullable();

            $table->enum('category', ['farmer', 'vendor', 'agro_business'])->nullable();
            $table->string('sub_category')->nullable();

            $table->string('location')->nullable();
            $table->string('state')->nullable();

            $table->enum('classification', ['new', 'hot', 'warm', 'cold', 'activated'])->default('new');
            $table->enum('source', ['market_visit', 'call', 'referral', 'walk_in', 'other'])->nullable();

            $table->json('needs_discovery')->nullable();
            $table->text('notes')->nullable();

            $table->foreignUuid('linked_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('next_follow_up_at')->nullable();
            $table->timestamp('registered_at')->nullable();
            $table->timestamp('activated_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['agent_id', 'classification']);
            $table->index(['next_follow_up_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};