<?php

namespace Database\Seeders;

use App\Models\CountryMarket;
use App\Models\PlatformPaymentProvider;
use App\Models\PlatformSetting;
use Illuminate\Database\Seeder;

class PlatformSettingsSeeder extends Seeder
{
    public function run(): void
    {
        PlatformSetting::query()->delete();
        $settings = PlatformSetting::create([
            'reporting_currency'                 => 'USD',
            'default_billing_currency'           => 'USD',
            'first_property_free_months'         => 1,
            'default_signup_fee_minor_per_unit'  => 1000,
            'default_monthly_fee_minor_per_unit' => 900,
            'default_maintenance_commission_bps' => 500,
            'subscription_provider_slug'         => 'stripe_billing',
            'enabled_payment_method_slugs'       => PlatformSetting::DEFAULT_PAYMENT_METHODS,
            'enabled_rent_processor_slugs'       => PlatformSetting::DEFAULT_RENT_PROCESSORS,
            'payment_method_settings'            => PlatformSetting::defaultPaymentMethodSettings(),
        ]);

        PlatformPaymentProvider::syncFromConfig();

        $countries = config('countries', []);
        $tierOverrides = [
            'IN' => ['tier' => 'emerging', 'signup' => 49900, 'monthly' => 39900],
            'NG' => ['tier' => 'emerging', 'signup' => 150000, 'monthly' => 120000],
            'KE' => ['tier' => 'emerging', 'signup' => 120000, 'monthly' => 100000],
            'ID' => ['tier' => 'emerging', 'signup' => 15000000, 'monthly' => 12000000],
            'PH' => ['tier' => 'emerging', 'signup' => 55000, 'monthly' => 45000],
            'BR' => ['tier' => 'emerging', 'signup' => 5500, 'monthly' => 4500],
            'MX' => ['tier' => 'emerging', 'signup' => 20000, 'monthly' => 15000],
        ];

        foreach ($countries as $code => $meta) {
            $override = $tierOverrides[$code] ?? null;
            $tier = $override['tier'] ?? 'standard';
            $signup = $override['signup'] ?? $settings->default_signup_fee_minor_per_unit;
            $monthly = $override['monthly'] ?? $settings->default_monthly_fee_minor_per_unit;

            if ($tier === 'standard' && ! in_array($code, ['US', 'CA', 'GB', 'FR', 'DE', 'AU', 'SG'], true)) {
                $signup = (int) round($signup * 0.85);
                $monthly = (int) round($monthly * 0.85);
            }

            CountryMarket::updateOrCreate(
                ['country_code' => $code],
                [
                    'billing_currency'           => $meta['currency'],
                    'rent_processor_slug'        => $meta['processor'],
                    'pricing_tier'               => $tier,
                    'signup_fee_minor_per_unit'  => $signup,
                    'monthly_fee_minor_per_unit' => $monthly,
                    'maintenance_commission_bps' => $tier === 'emerging' ? 280 : 300,
                    'is_active'                  => true,
                ]
            );
        }

        CountryMarket::updateOrCreate(
            ['country_code' => 'MA'],
            [
                'billing_currency'           => 'MAD',
                'rent_processor_slug'        => 'flutterwave',
                'pricing_tier'               => 'frontier',
                'signup_fee_minor_per_unit'  => 8000,
                'monthly_fee_minor_per_unit' => 6000,
                'maintenance_commission_bps' => 500,
                'is_active'                  => false,
                'admin_notes'                => 'Example frontier market (Morocco) — lower unit fees for PPP; enable when live.',
            ]
        );
    }
}
