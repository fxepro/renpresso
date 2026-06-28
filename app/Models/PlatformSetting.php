<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PlatformSetting extends Model
{
    use HasUuids;

    public const DEFAULT_PAYMENT_METHODS = ['credit_card', 'debit_card', 'ach'];

    public const DEFAULT_RENT_PROCESSORS = ['stripe', 'razorpay', 'flutterwave', 'xendit', 'mercadopago'];

    /** Maps rent processor slug → billing slug when charging landlord subscriptions. */
    public const SUBSCRIPTION_PROCESSOR_MAP = [
        'stripe' => 'stripe_billing',
    ];

    protected $fillable = [
        'reporting_currency',
        'default_billing_currency',
        'first_property_free_months',
        'default_signup_fee_minor_per_unit',
        'default_monthly_fee_minor_per_unit',
        'default_maintenance_commission_bps',
        'subscription_provider_slug',
        'enabled_payment_method_slugs',
        'enabled_rent_processor_slugs',
        'payment_method_settings',
    ];

    protected function casts(): array
    {
        return [
            'first_property_free_months'         => 'integer',
            'default_signup_fee_minor_per_unit'  => 'integer',
            'default_monthly_fee_minor_per_unit' => 'integer',
            'default_maintenance_commission_bps' => 'integer',
            'enabled_payment_method_slugs'       => 'array',
            'enabled_rent_processor_slugs'       => 'array',
            'payment_method_settings'            => 'array',
        ];
    }

    public static function current(): self
    {
        $settings = static::query()->first();

        if ($settings) {
            return $settings;
        }

        return static::create([
            'subscription_provider_slug'     => 'stripe_billing',
            'enabled_payment_method_slugs'   => self::DEFAULT_PAYMENT_METHODS,
            'enabled_rent_processor_slugs'   => self::DEFAULT_RENT_PROCESSORS,
            'payment_method_settings'        => self::defaultPaymentMethodSettings(),
        ]);
    }

    /** @return array<string, array{enabled: bool, provider_slug: string}> */
    public static function defaultPaymentMethodSettings(): array
    {
        $out = [];
        foreach (config('payment_methods.methods', []) as $method) {
            $slug = $method['slug'];
            $out[$slug] = [
                'enabled'       => in_array($slug, self::DEFAULT_PAYMENT_METHODS, true),
                'provider_slug' => $method['default_provider_slug'] ?? $method['provider_slug'] ?? 'stripe',
            ];
        }

        return $out;
    }

    /** @return array<string, array{enabled: bool, provider_slug: string}> */
    public function paymentMethodSettingsMap(): array
    {
        $saved = $this->payment_method_settings;
        if (is_array($saved) && $saved !== []) {
            return $saved;
        }

        $enabledSlugs = is_array($this->enabled_payment_method_slugs) && $this->enabled_payment_method_slugs !== []
            ? $this->enabled_payment_method_slugs
            : self::DEFAULT_PAYMENT_METHODS;

        $legacy = [];
        foreach (config('payment_methods.methods', []) as $method) {
            $slug = $method['slug'];
            $legacy[$slug] = [
                'enabled'       => in_array($slug, $enabledSlugs, true),
                'provider_slug' => $method['default_provider_slug'] ?? 'stripe',
            ];
        }

        return $legacy;
    }

    /** @return list<array{slug: string, label: string, enabled: bool, provider_slug: string, processor_fee_label: string, fee_paid_by: string, used_for: string, notes: string}> */
    public function resolvedPaymentMethods(): array
    {
        $map = $this->paymentMethodSettingsMap();
        $rows = [];

        foreach (config('payment_methods.methods', []) as $method) {
            $slug = $method['slug'];
            $row = $map[$slug] ?? [
                'enabled'       => false,
                'provider_slug' => $method['default_provider_slug'] ?? 'stripe',
            ];
            $rows[] = array_merge($method, [
                'enabled'       => (bool) ($row['enabled'] ?? false),
                'provider_slug' => (string) ($row['provider_slug'] ?? 'stripe'),
            ]);
        }

        return $rows;
    }

    /** @return list<string> */
    public function enabledPaymentMethodSlugs(): array
    {
        return collect($this->resolvedPaymentMethods())
            ->filter(fn (array $m) => $m['enabled'])
            ->pluck('slug')
            ->values()
            ->all();
    }

    public function providerSlugForMethod(string $methodSlug): ?string
    {
        $map = $this->paymentMethodSettingsMap();

        return $map[$methodSlug]['provider_slug'] ?? null;
    }

    /** Landlord monthly dues use the credit-card processor (mapped to billing slug if needed). */
    public function subscriptionProviderSlug(): string
    {
        if (filled($this->subscription_provider_slug)) {
            return $this->subscription_provider_slug;
        }

        $cardProcessor = $this->providerSlugForMethod('credit_card') ?? 'stripe';

        return self::SUBSCRIPTION_PROCESSOR_MAP[$cardProcessor] ?? $cardProcessor;
    }

    /** @return list<string> */
    public function enabledRentProcessorSlugs(): array
    {
        $slugs = $this->enabled_rent_processor_slugs;

        return is_array($slugs) && $slugs !== []
            ? array_values($slugs)
            : self::DEFAULT_RENT_PROCESSORS;
    }

    public function isPaymentMethodEnabled(string $slug): bool
    {
        return in_array($slug, $this->enabledPaymentMethodSlugs(), true);
    }

    public function syncLegacyPaymentColumns(): void
    {
        $map = $this->paymentMethodSettingsMap();
        $this->forceFill([
            'enabled_payment_method_slugs' => collect($map)->filter(fn ($r) => $r['enabled'] ?? false)->keys()->values()->all(),
            'subscription_provider_slug'   => $this->subscriptionProviderSlug(),
            'payment_method_settings'      => $map,
        ])->save();
    }

    public function formatMinor(int $minor, string $currency): string
    {
        return $currency.' '.number_format($minor / 100, 2);
    }
}
