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
        $this->addIndexIfMissing('transactions', function (Blueprint $table) {
            $table->index('reference');
            $table->index('status');
            $table->index('from_wallet_id');
            $table->index('to_wallet_id');
        });

        $this->addIndexIfMissing('order_items', function (Blueprint $table) {
            $table->index('vendor_id');
            $table->index('vendor_status');
            $table->index('order_id');
        });

        $this->addIndexIfMissing('orders', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('status');
        });

        $this->addIndexIfMissing('wallets', function (Blueprint $table) {
            $table->index(['user_id', 'currency']);
        });

        $this->addIndexIfMissing('withdrawal_requests', function (Blueprint $table) {
            $table->index('reference');
            $table->index('user_id');
            $table->index('status');
            $table->index('idempotency_key');
        });
    }

    private function addIndexIfMissing(string $table, \Closure $callback): void
    {
        try {
            Schema::table($table, $callback);
        } catch (\Illuminate\Database\QueryException $e) {
            $msg = $e->getMessage();
            if (!str_contains($msg, 'Duplicate key name') &&
                !str_contains($msg, "Key column") &&
                !str_contains($msg, 'already exists')) {
                throw $e;
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['reference']);
            $table->dropIndex(['status']);
            $table->dropIndex(['from_wallet_id']);
            $table->dropIndex(['to_wallet_id']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex(['vendor_id']);
            $table->dropIndex(['vendor_status']);
            $table->dropIndex(['order_id']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['status']);
        });

        Schema::table('wallets', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'currency']);
        });

        Schema::table('withdrawal_requests', function (Blueprint $table) {
            $table->dropIndex(['reference']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['idempotency_key']);
        });
    }
};
