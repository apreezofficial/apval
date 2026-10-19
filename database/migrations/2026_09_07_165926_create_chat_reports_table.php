<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasTable('chat_reports')) {
            Schema::create('chat_reports', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('reporter_id');
                $table->enum('target_type', ['user', 'message', 'community']);
                $table->uuid('target_id');
                $table->uuid('conversation_id')->nullable();
                $table->uuid('message_id')->nullable();
                
                $table->enum('reason', [
                    'spam', 'scam', 'fraud', 'harassment', 'fake_product',
                    'offensive_content', 'illegal_activity', 'other'
                ]);
                
                $table->text('description')->nullable();
                $table->enum('status', ['pending', 'reviewing', 'resolved', 'dismissed'])->default('pending');
                $table->uuid('reviewed_by')->nullable();
                $table->text('resolution_notes')->nullable();
                $table->timestamp('resolved_at')->nullable();
                
                $table->timestamps();
                
                $table->foreign('reporter_id')
                    ->references('id')->on('users')->cascadeOnDelete();
                
                $table->index(['target_type', 'target_id']);
                $table->index('status');
            });
        }
    }
    public function down(): void { Schema::dropIfExists('chat_reports'); }
};