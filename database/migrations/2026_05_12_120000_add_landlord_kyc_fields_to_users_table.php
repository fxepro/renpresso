<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('kyc_status', 32)->default('none')->after('kyc_verified_at');
            $table->string('kyc_legal_name')->nullable()->after('kyc_status');
            $table->date('kyc_date_of_birth')->nullable()->after('kyc_legal_name');
            $table->string('kyc_address_line1')->nullable()->after('kyc_date_of_birth');
            $table->string('kyc_address_line2')->nullable()->after('kyc_address_line1');
            $table->string('kyc_city', 120)->nullable()->after('kyc_address_line2');
            $table->string('kyc_region', 120)->nullable()->after('kyc_city');
            $table->string('kyc_postal_code', 32)->nullable()->after('kyc_region');
            $table->string('kyc_address_country', 2)->nullable()->after('kyc_postal_code');
            $table->string('kyc_id_document_path', 512)->nullable()->after('kyc_address_country');
            $table->timestamp('kyc_submitted_at')->nullable()->after('kyc_id_document_path');
            $table->text('kyc_rejection_reason')->nullable()->after('kyc_submitted_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'kyc_status',
                'kyc_legal_name',
                'kyc_date_of_birth',
                'kyc_address_line1',
                'kyc_address_line2',
                'kyc_city',
                'kyc_region',
                'kyc_postal_code',
                'kyc_address_country',
                'kyc_id_document_path',
                'kyc_submitted_at',
                'kyc_rejection_reason',
            ]);
        });
    }
};
