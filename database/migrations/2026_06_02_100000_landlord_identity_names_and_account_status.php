<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('kyc_first_name', 120)->nullable()->after('kyc_legal_name');
            $table->string('kyc_middle_name', 120)->nullable()->after('kyc_first_name');
            $table->string('kyc_last_name', 120)->nullable()->after('kyc_middle_name');
            $table->string('kyc_name_suffix', 16)->nullable()->after('kyc_last_name');

            $table->string('landlord_account_status', 32)->default('pending_activation')->after('use_business_entity_in_lease');
            $table->unsignedSmallInteger('portfolio_committed_units')->nullable()->after('landlord_account_status');
            $table->unsignedInteger('portfolio_activation_fee_minor')->nullable()->after('portfolio_committed_units');
            $table->unsignedSmallInteger('portfolio_activation_units')->nullable()->after('portfolio_activation_fee_minor');
            $table->timestamp('portfolio_activation_paid_at')->nullable()->after('portfolio_activation_units');
        });

        $landlords = DB::table('users')->where('role', 'landlord')->whereNotNull('kyc_legal_name')->get(['id', 'kyc_legal_name']);
        foreach ($landlords as $row) {
            $parts = \App\Support\KycLegalName::parseFullName((string) $row->kyc_legal_name);
            DB::table('users')->where('id', $row->id)->update([
                'kyc_first_name'   => $parts['first'],
                'kyc_middle_name'  => $parts['middle'],
                'kyc_last_name'    => $parts['last'],
                'kyc_name_suffix'  => $parts['suffix'],
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'kyc_first_name',
                'kyc_middle_name',
                'kyc_last_name',
                'kyc_name_suffix',
                'landlord_account_status',
                'portfolio_committed_units',
                'portfolio_activation_fee_minor',
                'portfolio_activation_units',
                'portfolio_activation_paid_at',
            ]);
        });
    }
};
