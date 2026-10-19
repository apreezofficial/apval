<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('feed_posts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('category_id')->nullable()->constrained('feed_categories')->nullOnDelete();

            // Post type (SRS Section 7)
            $table->enum('post_type', [
                'normal',        // text + optional media
                'image',         // image-focused
                'video',         // video-focused
                'product',       // linked to marketplace product
                'event',         // linked to event
                'announcement',  // admin official
                'sponsored',     // paid promotional
            ])->default('normal');

            // Content
            $table->text('content')->nullable();
            $table->json('hashtags')->nullable();       // ["#TomatoFarming", "#Agriculture"]
            $table->json('mentions')->nullable();       // ["user_id_1", "user_id_2"]

            // Marketplace integration (SRS Section 27)
            $table->uuid('product_id')->nullable();
            $table->uuid('event_id')->nullable();

            // Location (optional)
            $table->json('location')->nullable(); // {lat, lng, name, address}

            // Status & moderation (SRS Section 22)
            $table->enum('status', [
                'draft',
                'pending',    // awaiting moderation
                'published',
                'hidden',     // admin hidden
                'rejected',   // admin rejected
                'removed',    // deleted by admin
            ])->default('published');

            // Admin features (SRS Section 24, 25)
            $table->boolean('is_pinned')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->timestamp('pinned_at')->nullable();
            $table->timestamp('featured_at')->nullable();

            // Scheduling (Phase 2 ready)
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('published_at')->nullable();

            // Denormalized counters (performance)
            $table->integer('likes_count')->default(0);
            $table->integer('comments_count')->default(0);
            $table->integer('shares_count')->default(0);
            $table->integer('saves_count')->default(0);
            $table->integer('views_count')->default(0);

            // Moderation metadata
            $table->boolean('is_flagged')->default(false);
            $table->string('flag_reason')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes for feed algorithm (SRS Section 17)
            $table->index(['status', 'published_at']);
            $table->index(['user_id', 'status']);
            $table->index(['category_id', 'status']);
            $table->index(['post_type', 'status']);
            $table->index('is_pinned');
            $table->index('is_featured');
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feed_posts');
    }
};