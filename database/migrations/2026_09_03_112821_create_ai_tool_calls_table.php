<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('ai_tool_calls', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('conversation_id')->constrained('ai_conversations')->cascadeOnDelete();
            $table->foreignUuid('message_id')->nullable()->constrained('ai_messages')->nullOnDelete();
            $table->string('tool_name'); // e.g., search_marketplace_products
            $table->json('arguments');
            $table->json('result')->nullable();
            $table->integer('execution_time_ms')->default(0);
            $table->boolean('success')->default(true);
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('ai_tool_calls'); }
};