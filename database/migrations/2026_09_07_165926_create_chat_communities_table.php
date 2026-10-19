<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('chat_communities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('owner_id');
            $table->uuid('conversation_id'); // Links to chat_conversations
            
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('banner_url')->nullable();
            $table->string('avatar_url')->nullable();
            $table->string('category')->nullable(); // farming, market, region, etc.
            $table->json('tags')->nullable();
            
            $table->enum('privacy', ['public', 'private', 'invite_only'])->default('public');
            $table->boolean('approval_required')->default(false);
            $table->boolean('members_can_post')->default(true);
            $table->boolean('is_locked')->default(false);
            
            $table->integer('members_count')->default(0);
            $table->integer('posts_count')->default(0);
            
            $table->json('rules')->nullable();
            $table->json('settings')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('owner_id')
                ->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('conversation_id')
                ->references('id')->on('chat_conversations')->cascadeOnDelete();
            
            $table->index('category');
            $table->index('members_count');
        });
    }
    public function down(): void { Schema::dropIfExists('chat_communities'); }
};