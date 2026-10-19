<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('shopping_lists', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->json('items'); // [{category, name, quantity, unit, estimated_price}]
            $table->decimal('total_estimated_cost', 12, 2)->default(0);
            $table->string('currency', 3)->default('NGN');
            $table->boolean('purchased')->default(false);
            $table->uuid('linked_order_id')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('shopping_lists'); }
};