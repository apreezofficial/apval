<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Communities table
        if (!Schema::hasTable('communities')) {
            Schema::create('communities', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('category')->default('General');
                $table->text('description')->nullable();
                $table->string('image_url')->nullable();
                $table->uuid('owner_id')->nullable()->index();
                $table->unsignedInteger('members_count')->default(1);
                $table->boolean('is_locked')->default(false);
                $table->text('rules')->nullable();
                $table->timestamps();

                $table->foreign('owner_id')->references('id')->on('users')->onDelete('set null');
            });
        }

        // 2. Community Members table
        if (!Schema::hasTable('community_members')) {
            Schema::create('community_members', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('community_id')->index();
                $table->uuid('user_id')->index();
                $table->string('role')->default('member'); // admin, moderator, member
                $table->boolean('is_muted')->default(false);
                $table->timestamp('muted_until')->nullable();
                $table->timestamps();

                $table->unique(['community_id', 'user_id']);
                $table->foreign('community_id')->references('id')->on('communities')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }

        // 3. Alter Conversations table
        Schema::table('conversations', function (Blueprint $table) {
            if (!Schema::hasColumn('conversations', 'category')) {
                $table->string('category')->default('direct')->index(); // direct, community, marketplace, order, support
            }
            if (!Schema::hasColumn('conversations', 'community_id')) {
                $table->uuid('community_id')->nullable()->index();
            }
            if (!Schema::hasColumn('conversations', 'order_id')) {
                $table->uuid('order_id')->nullable()->index();
            }
            if (!Schema::hasColumn('conversations', 'product_id')) {
                $table->uuid('product_id')->nullable()->index();
            }
            if (!Schema::hasColumn('conversations', 'is_announcement_only')) {
                $table->boolean('is_announcement_only')->default(false);
            }
            if (!Schema::hasColumn('conversations', 'last_message_at')) {
                $table->timestamp('last_message_at')->nullable()->index();
            }
        });

        // 4. Alter Messages table
        Schema::table('messages', function (Blueprint $table) {
            if (!Schema::hasColumn('messages', 'media_duration')) {
                $table->unsignedInteger('media_duration')->nullable(); // duration in seconds
            }
            if (!Schema::hasColumn('messages', 'file_name')) {
                $table->string('file_name')->nullable();
            }
            if (!Schema::hasColumn('messages', 'file_size')) {
                $table->unsignedBigInteger('file_size')->nullable();
            }
            if (!Schema::hasColumn('messages', 'product_id')) {
                $table->uuid('product_id')->nullable()->index();
            }
            if (!Schema::hasColumn('messages', 'order_id')) {
                $table->uuid('order_id')->nullable()->index();
            }
            if (!Schema::hasColumn('messages', 'location_data')) {
                $table->json('location_data')->nullable();
            }
            if (!Schema::hasColumn('messages', 'reply_to_id')) {
                $table->uuid('reply_to_id')->nullable()->index();
            }
            if (!Schema::hasColumn('messages', 'is_pinned')) {
                $table->boolean('is_pinned')->default(false)->index();
            }
            if (!Schema::hasColumn('messages', 'pinned_at')) {
                $table->timestamp('pinned_at')->nullable();
            }
            if (!Schema::hasColumn('messages', 'pinned_by')) {
                $table->uuid('pinned_by')->nullable();
            }
            if (!Schema::hasColumn('messages', 'metadata')) {
                $table->json('metadata')->nullable();
            }
            if (!Schema::hasColumn('messages', 'delivered_at')) {
                $table->timestamp('delivered_at')->nullable();
            }
            if (!Schema::hasColumn('messages', 'read_at')) {
                $table->timestamp('read_at')->nullable();
            }
        });

        // 5. Message Reactions
        if (!Schema::hasTable('message_reactions')) {
            Schema::create('message_reactions', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('message_id')->index();
                $table->uuid('user_id')->index();
                $table->string('emoji', 16);
                $table->timestamps();

                $table->unique(['message_id', 'user_id', 'emoji']);
                $table->foreign('message_id')->references('id')->on('messages')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }

        // 6. Polls
        if (!Schema::hasTable('polls')) {
            Schema::create('polls', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('conversation_id')->index();
                $table->uuid('creator_id')->index();
                $table->string('question');
                $table->boolean('is_closed')->default(false);
                $table->timestamps();

                $table->foreign('conversation_id')->references('id')->on('conversations')->onDelete('cascade');
                $table->foreign('creator_id')->references('id')->on('users')->onDelete('cascade');
            });
        }

        // 7. Poll Options
        if (!Schema::hasTable('poll_options')) {
            Schema::create('poll_options', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('poll_id')->index();
                $table->string('option_text');
                $table->unsignedInteger('votes_count')->default(0);
                $table->timestamps();

                $table->foreign('poll_id')->references('id')->on('polls')->onDelete('cascade');
            });
        }

        // 8. Poll Votes
        if (!Schema::hasTable('poll_votes')) {
            Schema::create('poll_votes', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('poll_id')->index();
                $table->uuid('poll_option_id')->index();
                $table->uuid('user_id')->index();
                $table->timestamps();

                $table->unique(['poll_id', 'user_id']);
                $table->foreign('poll_id')->references('id')->on('polls')->onDelete('cascade');
                $table->foreign('poll_option_id')->references('id')->on('poll_options')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }

        // 9. Chat Reports
        if (!Schema::hasTable('chat_reports')) {
            Schema::create('chat_reports', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('reporter_id')->index();
                $table->string('target_type')->default('user');
                $table->uuid('target_id')->nullable();
                $table->uuid('reported_user_id')->nullable()->index();
                $table->uuid('conversation_id')->nullable()->index();
                $table->uuid('message_id')->nullable()->index();
                $table->string('reason');
                $table->text('details')->nullable();
                $table->text('description')->nullable();
                $table->string('status')->default('pending'); // pending, reviewing, resolved, dismissed
                $table->uuid('reviewed_by')->nullable();
                $table->text('resolution_notes')->nullable();
                $table->timestamp('resolved_at')->nullable();
                $table->timestamps();

                $table->foreign('reporter_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('reported_user_id')->references('id')->on('users')->onDelete('set null');
                $table->foreign('conversation_id')->references('id')->on('conversations')->onDelete('set null');
                $table->foreign('message_id')->references('id')->on('messages')->onDelete('set null');
            });
        }

        // 10. User Blocks
        if (!Schema::hasTable('user_blocks')) {
            Schema::create('user_blocks', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('blocker_id')->index();
                $table->uuid('blocked_id')->index();
                $table->timestamps();

                $table->unique(['blocker_id', 'blocked_id']);
                $table->foreign('blocker_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('blocked_id')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_blocks');
        Schema::dropIfExists('chat_reports');
        Schema::dropIfExists('poll_votes');
        Schema::dropIfExists('poll_options');
        Schema::dropIfExists('polls');
        Schema::dropIfExists('message_reactions');

        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn([
                'media_duration', 'file_name', 'file_size', 'product_id', 'order_id',
                'location_data', 'reply_to_id', 'is_pinned', 'pinned_at', 'pinned_by',
                'metadata', 'delivered_at', 'read_at'
            ]);
        });

        Schema::table('conversations', function (Blueprint $table) {
            $table->dropColumn([
                'category', 'community_id', 'order_id', 'product_id', 'is_announcement_only', 'last_message_at'
            ]);
        });

        Schema::dropIfExists('community_members');
        Schema::dropIfExists('communities');
    }
};
