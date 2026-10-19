<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('feed_notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete(); // recipient
            $table->foreignUuid('sender_id')->nullable()->constrained('users')->nullOnDelete(); // actor

            $table->enum('type', [
                'post_liked',
                'post_commented',
                'comment_replied',
                'post_shared',
                'new_follower',
                'mention',
                'admin_announcement',
                'post_featured',
                'comment_liked',
            ]);

            // Polymorphic reference
            $table->string('reference_type')->nullable(); // post, comment, user
            $table->uuid('reference_id')->nullable();

            $table->text('message');
            $table->json('metadata')->nullable(); // extra data

            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'is_read', 'created_at']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feed_notifications');
    }
};