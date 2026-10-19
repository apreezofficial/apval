<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'transaction_pin')) {
                $table->string('transaction_pin')->nullable()->after('password');
            }
            if (!Schema::hasColumn('users', 'transaction_pin_set_at')) {
                $table->timestamp('transaction_pin_set_at')->nullable()->after('transaction_pin');
            }
            if (!Schema::hasColumn('users', 'transaction_pin_attempts')) {
                $table->unsignedTinyInteger('transaction_pin_attempts')->default(0)->after('transaction_pin_set_at');
            }
            if (!Schema::hasColumn('users', 'transaction_pin_locked_until')) {
                $table->timestamp('transaction_pin_locked_until')->nullable()->after('transaction_pin_attempts');
            }
            if (!Schema::hasColumn('users', 'biometric_enabled')) {
                $table->boolean('biometric_enabled')->default(false)->after('transaction_pin_locked_until');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'transaction_pin',
                'transaction_pin_set_at',
                'transaction_pin_attempts',
                'transaction_pin_locked_until',
                'biometric_enabled',
            ]);
        });
    }
};
