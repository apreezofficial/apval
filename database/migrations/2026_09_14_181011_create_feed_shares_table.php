<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('feed_shares', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('post_id')->constrained('feed_posts')->cascadeOnDelete();

            $table->enum('share_type', [
                'internal',   // shared within AgreonPay
                'whatsapp',
                'twitter',
                'facebook',
                'telegram',
                'link_copy',  // copied link
                'other',
            ])->default('internal');

            $table->text('caption')->nullable(); // optional caption when sharing
            $table->timestamp('created_at')->nullable();

            $table->index(['post_id', 'share_type']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feed_shares');
    }
};