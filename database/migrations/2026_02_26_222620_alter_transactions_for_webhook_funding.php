<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{ 
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get existing columns
        $columns = Schema::getColumnListing('transactions');
        
        // 1) idempotency_key: make nullable (keep existing unique index)
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('idempotency_key', 80)->nullable()->change();
        });

        // 2) Add provider and provider_reference ONLY if they don't exist
        // Since they already exist, these will be skipped
        if (!in_array('provider', $columns)) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->string('provider', 30)->nullable()->index();
            });
        }

        if (!in_array('provider_reference', $columns)) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->string('provider_reference', 100)->nullable()->unique();
            });
        }

        // 3) Handle the type column
        // Check if type column exists
        if (in_array('type', $columns)) {
            // If type exists, we need to expand it
            // Check if type_new doesn't exist
            if (!in_array('type_new', $columns)) {
                Schema::table('transactions', function (Blueprint $table) {
                    $table->enum('type_new', ['transfer', 'funding', 'withdrawal', 'reversal', 'purchase'])->default('transfer')->index();
                });
            }

            // Copy data from type to type_new
            DB::statement("UPDATE transactions SET type_new = type WHERE type IS NOT NULL");

            // Drop old index safely
            if (DB::getDriverName() === 'mysql') {
                Schema::table('transactions', function (Blueprint $table) {
                    $table->dropIndex(['type']);
                });
            } else {
                DB::statement("DROP INDEX IF EXISTS transactions_type_index");
            }

            // Drop old type column
            Schema::table('transactions', function (Blueprint $table) {
                $table->dropColumn('type');
            });

            // Rename type_new to type
            if (DB::getDriverName() === 'mysql') {
                DB::statement("ALTER TABLE transactions CHANGE type_new type ENUM('transfer', 'funding', 'withdrawal', 'reversal', 'purchase') DEFAULT 'transfer'");
            } else {
                Schema::table('transactions', function (Blueprint $table) {
                    $table->renameColumn('type_new', 'type');
                });
            }
        } elseif (in_array('type_new', $columns) && !in_array('type', $columns)) {
            // If type doesn't exist but type_new does (previous migration half-ran)
            // Rename type_new to type
            if (DB::getDriverName() === 'mysql') {
                DB::statement("ALTER TABLE transactions CHANGE type_new type ENUM('transfer', 'funding', 'withdrawal', 'reversal', 'purchase') DEFAULT 'transfer'");
            } else {
                Schema::table('transactions', function (Blueprint $table) {
                    $table->renameColumn('type_new', 'type');
                });
            }
        } else {
            // If neither type nor type_new exists, just add type
            Schema::table('transactions', function (Blueprint $table) {
                $table->enum('type', ['transfer', 'funding', 'withdrawal', 'reversal', 'purchase'])->default('transfer')->index();
            });
        }
    }

    public function down(): void
    {
        $columns = Schema::getColumnListing('transactions');
        
        // Reverse the enum change
        if (in_array('type', $columns)) {
            // Add old type column
            if (!in_array('type_old', $columns)) {
                Schema::table('transactions', function (Blueprint $table) {
                    $table->enum('type_old', ['transfer'])->default('transfer')->index();
                });
            }

            DB::statement("UPDATE transactions SET type_old = 'transfer' WHERE type IS NOT NULL");

            if (DB::getDriverName() === 'mysql') {
                Schema::table('transactions', function (Blueprint $table) {
                    $table->dropIndex(['type']);
                    $table->dropColumn('type');
                });
                DB::statement("ALTER TABLE transactions CHANGE type_old type ENUM('transfer') DEFAULT 'transfer'");
            } else {
                DB::statement("DROP INDEX IF EXISTS transactions_type_index");
                Schema::table('transactions', function (Blueprint $table) {
                    $table->dropColumn('type');
                    $table->renameColumn('type_old', 'type');
                });
            }
        }

        // Remove provider fields (only if they exist and you want to remove them)
        // Comment these out if you want to keep provider fields
        /*
        if (in_array('provider_reference', $columns)) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->dropUnique(['provider_reference']);
            });
        }

        if (in_array('provider', $columns) && in_array('provider_reference', $columns)) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->dropColumn(['provider', 'provider_reference']);
            });
        }
        */
    }
};