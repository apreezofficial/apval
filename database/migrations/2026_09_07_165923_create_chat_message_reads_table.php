<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('chat_message_reads', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('message_id');
            $table->uuid('user_id');
            $table->timestamp('read_at');
            
            $table->foreign('message_id')
                ->references('id')->on('chat_messages')->cascadeOnDelete();
            $table->foreign('user_id')
                ->references('id')->on('users')->cascadeOnDelete();
            
            $table->unique(['message_id', 'user_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('chat_message_reads'); }
};