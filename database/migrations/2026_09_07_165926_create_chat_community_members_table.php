<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('chat_community_members', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('community_id');
            $table->uuid('user_id');
            $table->enum('role', ['member', 'moderator', 'admin', 'owner'])->default('member');
            $table->timestamp('joined_at')->nullable();
            $table->boolean('is_muted')->default(false);
            $table->timestamp('muted_until')->nullable();
            $table->boolean('is_banned')->default(false);
            $table->string('ban_reason')->nullable();
            $table->timestamps();
            
            $table->foreign('community_id')
                ->references('id')->on('chat_communities')->cascadeOnDelete();
            $table->foreign('user_id')
                ->references('id')->on('users')->cascadeOnDelete();
            
            $table->unique(['community_id', 'user_id']);
            $table->index('user_id');
            $table->index('role');
        });
    }
    public function down(): void { Schema::dropIfExists('chat_community_members'); }
};