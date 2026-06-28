<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('business_legal_name', 255)->nullable()->after('kyc_rejection_reason');
            $table->string('business_entity_type', 64)->nullable()->after('business_legal_name');
            $table->string('business_ein', 32)->nullable()->after('business_entity_type');
            $table->string('business_address_line1', 255)->nullable()->after('business_ein');
            $table->string('business_address_line2', 255)->nullable()->after('business_address_line1');
            $table->string('business_city', 120)->nullable()->after('business_address_line2');
            $table->string('business_region', 120)->nullable()->after('business_city');
            $table->string('business_postal_code', 32)->nullable()->after('business_region');
            $table->char('business_address_country', 2)->nullable()->after('business_postal_code');
            $table->string('business_state_of_formation', 120)->nullable()->after('business_address_country');
            $table->boolean('use_business_entity_in_lease')->default(false)->after('business_state_of_formation');
        });

        Schema::table('leases', function (Blueprint $table) {
            $table->boolean('use_business_entity')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('leases', function (Blueprint $table) {
            $table->dropColumn('use_business_entity');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'business_legal_name',
                'business_entity_type',
                'business_ein',
                'business_address_line1',
                'business_address_line2',
                'business_city',
                'business_region',
                'business_postal_code',
                'business_address_country',
                'business_state_of_formation',
                'use_business_entity_in_lease',
            ]);
        });
    }
};
