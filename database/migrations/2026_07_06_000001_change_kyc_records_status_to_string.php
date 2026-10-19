<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kyc_records', function (Blueprint $table) {
            $table->string('status', 64)->default('pending')->change();
        });
    }

    public function down(): void
    {
        Schema::table('kyc_records', function (Blueprint $table) {
            $table->string('status', 64)->default('pending')->change();
        });
    }
};
