<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('inspections')) {
            return;
        }

        Schema::create('inspections', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('order_item_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('inspector_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('status')->default('pending'); // pending, assigned, passed, failed
            $table->text('notes')->nullable();
            $table->json('evidence')->nullable(); // Arrays of image/video URLs
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inspections');
    }
};
