<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{ 
    public function up(): void
    {
        // 1) Ensure type_new exists only once
        if (!Schema::hasColumn('transactions', 'type_new')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->enum('type_new', ['transfer', 'funding', 'withdrawal', 'reversal', 'purchase'])
                    ->default('transfer')
                    ->after('type');
            });

            DB::statement("UPDATE transactions SET type_new = type");
        }

        // 2) Drop old type index safely (if exists)
        if (DB::getDriverName() === 'mysql') {
            $indexes = collect(DB::select("SHOW INDEX FROM transactions"))
                ->pluck('Key_name')
                ->unique()
                ->values();

            if ($indexes->contains('transactions_type_index')) {
                DB::statement("ALTER TABLE transactions DROP INDEX transactions_type_index");
            }
        }

        // 3) Drop old column if still present
        if (Schema::hasColumn('transactions', 'type') && DB::getDriverName() === 'mysql') {
            Schema::table('transactions', function (Blueprint $table) {
                $table->dropColumn('type');
            });
        }

        // 4) Rename if needed
        if (Schema::hasColumn('transactions', 'type_new') && !Schema::hasColumn('transactions', 'type') && DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE transactions CHANGE type_new type ENUM('transfer','funding','withdrawal','reversal','purchase') NOT NULL DEFAULT 'transfer'");
        }

        // 5) Ensure index exists on final type
        if (DB::getDriverName() === 'mysql') {
            $indexes = collect(DB::select("SHOW INDEX FROM transactions"))
                ->pluck('Key_name')
                ->unique()
                ->values();

            if (!$indexes->contains('transactions_type_index')) {
                Schema::table('transactions', function (Blueprint $table) {
                    $table->index('type'); // becomes transactions_type_index
                });
            }
        } elseif (DB::getDriverName() === 'sqlite') {
             Schema::table('transactions', function (Blueprint $table) {
                $table->index('type');
            });
        }
    }

    public function down(): void
    {
        // Optional: implement reverse similarly
    }
};
