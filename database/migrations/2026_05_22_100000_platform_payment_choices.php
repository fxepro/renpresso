<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('platform_settings', function (Blueprint $table) {
            $table->string('subscription_provider_slug', 50)->default('stripe_billing')->after('default_maintenance_commission_bps');
            $table->json('enabled_payment_method_slugs')->nullable()->after('subscription_provider_slug');
            $table->json('enabled_rent_processor_slugs')->nullable()->after('enabled_payment_method_slugs');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->json('landlord_payment_preferences')->nullable()->after('billing_pm_last4');
        });
    }

    public function down(): void
    {
        Schema::table('platform_settings', function (Blueprint $table) {
            $table->dropColumn(['subscription_provider_slug', 'enabled_payment_method_slugs', 'enabled_rent_processor_slugs']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('landlord_payment_preferences');
        });
    }
};
