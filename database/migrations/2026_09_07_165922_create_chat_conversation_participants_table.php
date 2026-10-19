<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('chat_conversation_participants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('conversation_id');
            $table->uuid('user_id');
            $table->enum('role', ['member', 'admin', 'moderator', 'owner'])->default('member');
            $table->string('nickname')->nullable();
            $table->boolean('is_muted')->default(false);
            $table->timestamp('muted_until')->nullable();
            $table->timestamp('joined_at')->nullable();
            $table->timestamp('left_at')->nullable();
            $table->timestamp('last_read_at')->nullable();
            $table->timestamps();
            
            $table->foreign('conversation_id')
                ->references('id')->on('chat_conversations')->cascadeOnDelete();
            $table->foreign('user_id')
                ->references('id')->on('users')->cascadeOnDelete();
            
            $table->unique(['conversation_id', 'user_id']);
            $table->index('user_id');
        });
    }
    public function down(): void { Schema::dropIfExists('chat_conversation_participants'); }
};