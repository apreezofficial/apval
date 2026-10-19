<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('feed_comments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('post_id')->constrained('feed_posts')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->uuid('parent_id')->nullable(); // for replies (SRS Section 10)

            $table->text('content');
            $table->enum('status', ['published', 'hidden', 'removed'])->default('published');

            // Denormalized counters
            $table->integer('likes_count')->default(0);
            $table->integer('replies_count')->default(0);

            // Moderation
            $table->boolean('is_flagged')->default(false);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['post_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->index('parent_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feed_comments');
    }
};