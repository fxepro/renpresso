<?php

namespace App\Services;

use App\Models\CountryMarket;
use App\Models\PlatformSetting;
use App\Models\User;

class LandlordAccountActivation
{
    public function signupFeeMinorPerUnit(User $landlord): int
    {
        $settings = PlatformSetting::current();
        $code     = strtoupper((string) ($landlord->home_country ?? 'US'));
        $market   = CountryMarket::query()->where('country_code', $code)->where('is_active', true)->first();

        return (int) ($market?->signup_fee_minor_per_unit ?? $settings->default_signup_fee_minor_per_unit);
    }

    public function billingCurrency(User $landlord): string
    {
        $settings = PlatformSetting::current();
        $code     = strtoupper((string) ($landlord->home_country ?? 'US'));
        $market   = CountryMarket::query()->where('country_code', $code)->where('is_active', true)->first();

        return $market?->billing_currency ?? $settings->default_billing_currency ?? 'USD';
    }

    public function activationFeeMinor(User $landlord, int $units): int
    {
        $units = max(1, $units);

        return $units * $this->signupFeeMinorPerUnit($landlord);
    }

    public function formatFee(int $minor, string $currency): string
    {
        return $currency.' '.number_format($minor / 100, 2);
    }

    public function defaultPaymentMethod(User $landlord): ?\App\Models\LandlordPaymentMethod
    {
        return $landlord->landlordPaymentMethods()
            ->where('status', '!=', 'removed')
            ->where('is_default', true)
            ->first();
    }

    public function hasDefaultPaymentMethod(User $landlord): bool
    {
        return $this->defaultPaymentMethod($landlord) !== null;
    }

    /**
     * Record portfolio activation (platform charge integration TBD).
     */
    public function activate(User $landlord, int $units): void
    {
        $units = max(1, min(9999, $units));
        $fee   = $this->activationFeeMinor($landlord, $units);

        $landlord->forceFill([
            'landlord_account_status'        => 'active',
            'portfolio_committed_units'      => $units,
            'portfolio_activation_units'     => $units,
            'portfolio_activation_fee_minor' => $fee,
            'portfolio_activation_paid_at'   => now(),
        ])->save();
    }
}
