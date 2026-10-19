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
        Schema::create('kyc_records', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->unique();
            $table->unsignedTinyInteger('level')->default(1); // level 1 required at registration

            $table->string('bvn')->nullable(); // store encrypted later (for day 2/3 hardening)
            $table->boolean('bvn_verified')->default(false);

            $table->enum('status', ['pending', 'verified', 'rejected'])->default('pending');

            $table->json('provider_response')->nullable();
            $table->timestamp('verified_at')->nullable();

            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kyc_records');
    }
};
