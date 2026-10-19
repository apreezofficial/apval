<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('feed_follows', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('follower_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('following_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('created_at')->nullable();

            // Prevent duplicate follows & self-follows
            $table->unique(['follower_id', 'following_id']);
            $table->index('follower_id');
            $table->index('following_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feed_follows');
    }
};