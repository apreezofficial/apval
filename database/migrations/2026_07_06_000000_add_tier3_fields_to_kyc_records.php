<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kyc_records', function (Blueprint $table) {
            if (!Schema::hasColumn('kyc_records', 'business_name')) {
                $table->string('business_name')->nullable()->after('utility_bill_url');
            }
            if (!Schema::hasColumn('kyc_records', 'cac_number')) {
                $table->string('cac_number')->nullable()->after('business_name');
            }
            if (!Schema::hasColumn('kyc_records', 'cac_document_url')) {
                $table->string('cac_document_url')->nullable()->after('cac_number');
            }
            if (!Schema::hasColumn('kyc_records', 'reviewed_by')) {
                $table->uuid('reviewed_by')->nullable()->after('cac_document_url');
            }
            if (!Schema::hasColumn('kyc_records', 'reviewed_at')) {
                $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            }
            if (!Schema::hasColumn('kyc_records', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('reviewed_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('kyc_records', function (Blueprint $table) {
            $table->dropColumn([
                'business_name', 'cac_number', 'cac_document_url',
                'reviewed_by', 'reviewed_at', 'rejection_reason',
            ]);
        });
    }
};
