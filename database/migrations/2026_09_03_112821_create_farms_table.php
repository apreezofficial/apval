<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('farms', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('location');
            $table->string('state')->nullable();
            $table->decimal('size_hectares', 10, 2);
            $table->string('crop')->nullable();
            $table->string('soil_type')->nullable();
            $table->decimal('soil_ph', 4, 2)->nullable();
            $table->date('planting_date')->nullable();
            $table->date('expected_harvest')->nullable();
            $table->string('farming_method')->default('traditional'); // traditional, mechanized, organic
            $table->string('water_source')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down(): void { Schema::dropIfExists('farms'); }
};