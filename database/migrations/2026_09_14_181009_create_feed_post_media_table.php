<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('feed_post_media', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('post_id')->constrained('feed_posts')->cascadeOnDelete();

            $table->enum('media_type', ['image', 'video', 'gif', 'link_preview']);
            $table->string('media_url');
            $table->string('thumbnail_url')->nullable();
            $table->string('alt_text')->nullable(); // accessibility
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('file_size')->nullable(); // in bytes
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->integer('duration')->nullable(); // for video (seconds)
            $table->integer('sort_order')->default(0);
            $table->json('metadata')->nullable(); // additional data

            $table->timestamps();

            $table->index(['post_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feed_post_media');
    }
};