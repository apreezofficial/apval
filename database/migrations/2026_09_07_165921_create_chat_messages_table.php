<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('conversation_id');
            $table->uuid('sender_id');
            $table->uuid('reply_to_message_id')->nullable();
            
            // Message type (SRS Section 6)
            $table->enum('type', [
                'text', 'image', 'video', 'voice', 'document', 'location',
                'product_card', 'order_card', 'payment_request', 'profile_card',
                'system', 'ai_response'
            ])->default('text');
            
            $table->text('content')->nullable();
            $table->string('media_url')->nullable();
            $table->string('media_type')->nullable(); // image/jpeg, video/mp4, etc.
            $table->integer('media_duration')->nullable(); // For voice/video (seconds)
            $table->json('media_metadata')->nullable(); // width, height, size
            
            // Context references
            $table->uuid('product_id')->nullable();
            $table->uuid('order_id')->nullable();
            $table->uuid('payment_request_id')->nullable();
            $table->uuid('profile_user_id')->nullable();
            
            // Location data
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('location_name')->nullable();
            
            // Reactions (SRS Section 20)
            $table->json('reactions')->nullable(); // {user_id: emoji}
            
            // Status tracking (SRS Section 18)
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->boolean('is_edited')->default(false);
            $table->boolean('is_deleted')->default(false);
            $table->timestamp('deleted_at')->nullable();
            
            // Anti-scam flag (SRS Section 27)
            $table->boolean('flagged_suspicious')->default(false);
            $table->string('flag_reason')->nullable();
            
            $table->timestamps();
            
            $table->foreign('conversation_id')
                ->references('id')->on('chat_conversations')->cascadeOnDelete();
            $table->foreign('sender_id')
                ->references('id')->on('users')->cascadeOnDelete();
            
            $table->index(['conversation_id', 'created_at']);
            $table->index('sender_id');
            $table->index('type');
        });
    }
    public function down(): void { Schema::dropIfExists('chat_messages'); }
};