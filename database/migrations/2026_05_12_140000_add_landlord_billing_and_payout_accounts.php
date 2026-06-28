<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('billing_company_name')->nullable()->after('kyc_rejection_reason');
            $table->string('billing_line1')->nullable()->after('billing_company_name');
            $table->string('billing_line2')->nullable()->after('billing_line1');
            $table->string('billing_city', 120)->nullable()->after('billing_line2');
            $table->string('billing_region', 120)->nullable()->after('billing_city');
            $table->string('billing_postal_code', 32)->nullable()->after('billing_region');
            $table->string('billing_country', 2)->nullable()->after('billing_postal_code');
            $table->string('stripe_customer_id')->nullable()->after('billing_country');
            $table->string('billing_pm_brand', 32)->nullable()->after('stripe_customer_id');
            $table->string('billing_pm_last4', 4)->nullable()->after('billing_pm_brand');
        });

        Schema::create('landlord_payout_accounts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('country_code', 2);
            $table->string('currency_code', 3);
            $table->string('label', 120);
            $table->string('holder_name', 255);
            $table->string('bank_name')->nullable();
            $table->text('iban')->nullable();
            $table->text('local_account_ref')->nullable();
            $table->text('local_routing_ref')->nullable();
            $table->string('display_hint', 32)->nullable();
            $table->string('status', 24)->default('saved');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('landlord_payout_accounts');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'billing_company_name', 'billing_line1', 'billing_line2', 'billing_city',
                'billing_region', 'billing_postal_code', 'billing_country',
                'stripe_customer_id', 'billing_pm_brand', 'billing_pm_last4',
            ]);
        });
    }
};
