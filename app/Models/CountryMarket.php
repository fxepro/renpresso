<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class CountryMarket extends Model
{
    use HasUuids;

    public const TIERS = ['standard', 'emerging', 'frontier'];

    protected $fillable = [
        'country_code',
        'billing_currency',
        'rent_processor_slug',
        'pricing_tier',
        'signup_fee_minor_per_unit',
        'monthly_fee_minor_per_unit',
        'maintenance_commission_bps',
        'rent_transaction_commission_bps',
        'is_active',
        'admin_notes',
    ];

    protected function casts(): array
    {
        return [
            'signup_fee_minor_per_unit'  => 'integer',
            'monthly_fee_minor_per_unit' => 'integer',
            'maintenance_commission_bps' => 'integer',
            'rent_transaction_commission_bps' => 'integer',
            'is_active'                  => 'boolean',
        ];
    }

    public function countryLabel(): string
    {
        return $this->country_code;
    }

    public function formatSignupFee(): string
    {
        return $this->formatMinor($this->signup_fee_minor_per_unit);
    }

    public function formatMonthlyFee(): string
    {
        return $this->formatMinor($this->monthly_fee_minor_per_unit);
    }

    public function formatMinor(int $minor): string
    {
        return $this->billing_currency.' '.number_format($minor / 100, 2);
    }

    public function maintenanceCommissionPercent(): string
    {
        return number_format($this->maintenance_commission_bps / 100, 2).'%';
    }

    public static function defaultsFromPlatform(): array
    {
        $s = PlatformSetting::current();

        return [
            'signup_fee_minor_per_unit'  => $s->default_signup_fee_minor_per_unit,
            'monthly_fee_minor_per_unit' => $s->default_monthly_fee_minor_per_unit,
            'maintenance_commission_bps' => $s->default_maintenance_commission_bps,
        ];
    }
}
