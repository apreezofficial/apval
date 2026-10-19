<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('ai_conversations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->string('title')->nullable(); // e.g., "Maize Fertilizer Plan"
            $table->string('intent_category')->nullable(); // agriculture, food, agreonpay
            $table->json('metadata')->nullable(); // farm_id, family_size, etc.
            $table->timestamps();
        });

        Schema::create('ai_messages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('conversation_id')->constrained('ai_conversations')->cascadeOnDelete();
            $table->string('role'); // 'user', 'assistant', 'system', 'tool'
            $table->text('content');
            $table->string('tool_call_id')->nullable();
            $table->json('tool_calls')->nullable(); // Array of tool calls made by AI
            $table->integer('prompt_tokens')->default(0);
            $table->integer('completion_tokens')->default(0);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('ai_messages');
        Schema::dropIfExists('ai_conversations');
    }
};