<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_follow_ups', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('lead_id')
                ->constrained('leads')
                ->cascadeOnDelete();

            $table->foreignUuid('agent_id')
                ->constrained('agent_profiles')
                ->cascadeOnDelete();

            $table->enum('classification_before', ['new', 'hot', 'warm', 'cold', 'activated'])->nullable();
            $table->enum('classification_after', ['new', 'hot', 'warm', 'cold', 'activated'])->nullable();

            $table->enum('channel', ['call', 'visit', 'sms', 'whatsapp', 'in_app', 'other'])->nullable();

            $table->enum('outcome', [
                'no_answer', 'interested', 'not_interested',
                'registered', 'activated', 'other',
            ])->nullable();

            $table->text('note')->nullable();
            $table->timestamp('followed_up_at');

            $table->timestamps();

            $table->index(['agent_id', 'followed_up_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_follow_ups');
    }
};