<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('ai_feedback', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('conversation_id')->constrained('ai_conversations')->cascadeOnDelete();
            $table->foreignUuid('message_id')->nullable()->constrained('ai_messages')->nullOnDelete();
            $table->enum('rating', ['helpful', 'not_helpful', 'incorrect', 'safety_concern', 'request_expert'])->nullable();
            $table->text('comment')->nullable();
            $table->string('intent_category')->nullable();
            $table->boolean('resolved')->default(false);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('ai_feedback'); }
};