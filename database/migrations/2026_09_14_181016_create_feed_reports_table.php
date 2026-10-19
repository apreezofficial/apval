<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('feed_reports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('reporter_id')->constrained('users')->cascadeOnDelete();

            // Polymorphic target (SRS Section 21)
            $table->enum('target_type', ['post', 'comment', 'user']);
            $table->uuid('target_id');

            $table->enum('reason', [
                'spam',
                'scam_fraud',
                'harassment',
                'hate_abuse',
                'sexual_inappropriate',
                'false_information',
                'illegal_content',
                'other',
            ]);

            $table->text('description')->nullable();
            $table->enum('status', ['pending', 'reviewing', 'resolved', 'dismissed'])->default('pending');
            $table->uuid('reviewed_by')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->timestamp('resolved_at')->nullable();

            $table->timestamps();

            $table->index(['target_type', 'target_id']);
            $table->index('status');
            $table->index('reporter_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feed_reports');
    }
};