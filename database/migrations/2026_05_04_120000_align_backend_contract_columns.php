<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Current services use extensible business states. Keep these as strings in
     * MySQL so adding a new transaction/status/role does not require enum surgery.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        if (Schema::hasTable('transactions') && Schema::hasColumn('transactions', 'type')) {
            DB::statement("ALTER TABLE transactions MODIFY type VARCHAR(40) NOT NULL DEFAULT 'transfer'");
        }

        if (Schema::hasTable('withdrawal_requests') && Schema::hasColumn('withdrawal_requests', 'status')) {
            DB::statement("ALTER TABLE withdrawal_requests MODIFY status VARCHAR(40) NOT NULL");
        }

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'role')) {
            DB::statement("ALTER TABLE users MODIFY role VARCHAR(40) NOT NULL");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        if (Schema::hasTable('transactions') && Schema::hasColumn('transactions', 'type')) {
            DB::statement("ALTER TABLE transactions MODIFY type ENUM('transfer','funding','withdrawal','reversal') NOT NULL DEFAULT 'transfer'");
        }

        if (Schema::hasTable('withdrawal_requests') && Schema::hasColumn('withdrawal_requests', 'status')) {
            DB::statement("ALTER TABLE withdrawal_requests MODIFY status ENUM('pending_2fa','queued','processing','success','failed','reversed') NOT NULL");
        }

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'role')) {
            DB::statement("ALTER TABLE users MODIFY role ENUM('customer','farmer','vendor','admin') NOT NULL");
        }
    }
};
