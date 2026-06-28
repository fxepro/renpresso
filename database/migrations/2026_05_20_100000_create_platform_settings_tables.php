<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('reporting_currency', 3)->default('USD');
            $table->string('default_billing_currency', 3)->default('USD');
            $table->unsignedTinyInteger('first_property_free_months')->default(1);
            $table->unsignedBigInteger('default_signup_fee_minor_per_unit')->default(1000);
            $table->unsignedBigInteger('default_monthly_fee_minor_per_unit')->default(900);
            $table->unsignedSmallInteger('default_maintenance_commission_bps')->default(500);
            $table->timestamps();
        });

        Schema::create('platform_payment_providers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('slug', 50)->unique();
            $table->string('name');
            $table->string('category', 40); // rent_collection | landlord_billing | crypto
            $table->boolean('is_enabled')->default(false);
            $table->boolean('is_configured')->default(false);
            $table->json('env_keys')->nullable();
            $table->text('setup_notes')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('country_markets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('country_code', 2)->unique();
            $table->string('billing_currency', 3);
            $table->string('rent_processor_slug', 50)->nullable();
            $table->string('pricing_tier', 20)->default('standard'); // standard | emerging | frontier
            $table->unsignedBigInteger('signup_fee_minor_per_unit');
            $table->unsignedBigInteger('monthly_fee_minor_per_unit');
            $table->unsignedSmallInteger('maintenance_commission_bps')->default(500);
            $table->unsignedSmallInteger('rent_transaction_commission_bps')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('admin_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('country_markets');
        Schema::dropIfExists('platform_payment_providers');
        Schema::dropIfExists('platform_settings');
    }
};
