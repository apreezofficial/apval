<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('chat_conversations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('type', ['direct', 'group', 'marketplace', 'order', 'support', 'system']);
            $table->string('title')->nullable(); // For groups/communities
            $table->string('avatar_url')->nullable();
            $table->text('description')->nullable();
            
            // Context references (SRS Section 8 - Order Chat)
            $table->uuid('related_order_id')->nullable();
            $table->uuid('related_product_id')->nullable();
            $table->uuid('related_user_id')->nullable(); // For marketplace chats
            
            // Metadata
            $table->json('metadata')->nullable();
            $table->boolean('is_locked')->default(false);
            $table->boolean('is_archived')->default(false);
            
            // Last message cache (for fast listing)
            $table->text('last_message_preview')->nullable();
            $table->timestamp('last_message_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['type', 'last_message_at']);
            $table->index('related_order_id');
            $table->index('related_product_id');
        });
    }
    public function down(): void { Schema::dropIfExists('chat_conversations'); }
};