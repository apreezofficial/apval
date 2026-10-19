<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kyc_records', function (Blueprint $table) {
            if (!Schema::hasColumn('kyc_records', 'daily_transaction_limit')) {
                $table->decimal('daily_transaction_limit', 20, 2)->default(50000.00)->after('level');
            }
            if (!Schema::hasColumn('kyc_records', 'max_wallet_balance')) {
                $table->decimal('max_wallet_balance', 20, 2)->default(300000.00)->after('daily_transaction_limit');
            }
            if (!Schema::hasColumn('kyc_records', 'id_card_url')) {
                $table->string('id_card_url')->nullable()->after('bvn');
            }
            if (!Schema::hasColumn('kyc_records', 'utility_bill_url')) {
                $table->string('utility_bill_url')->nullable()->after('id_card_url');
            }
        });
    }

    public function down(): void
    {
        Schema::table('kyc_records', function (Blueprint $table) {
            $table->dropColumn(['daily_transaction_limit', 'max_wallet_balance', 'id_card_url', 'utility_bill_url']);
        });
    }
};
