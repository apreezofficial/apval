<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('chat_blocked_users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('blocked_user_id');
            $table->text('reason')->nullable();
            $table->timestamps();
            
            $table->foreign('user_id')
                ->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('blocked_user_id')
                ->references('id')->on('users')->cascadeOnDelete();
            
            $table->unique(['user_id', 'blocked_user_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('chat_blocked_users'); }
};