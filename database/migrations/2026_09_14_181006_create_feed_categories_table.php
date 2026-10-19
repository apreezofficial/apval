<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('feed_categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('icon')->nullable(); // emoji or icon name
            $table->string('color', 7)->nullable(); // hex color
            $table->uuid('parent_id')->nullable(); // for subcategories
            $table->enum('status', ['active', 'inactive', 'archived'])->default('active');
            $table->integer('sort_order')->default(0);
            $table->integer('posts_count')->default(0); // denormalized counter
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('sort_order');
            $table->index('parent_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feed_categories');
    }
};