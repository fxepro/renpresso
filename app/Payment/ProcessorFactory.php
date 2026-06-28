<?php

namespace App\Payment;

use App\Models\PaymentMandate;
use App\Payment\Contracts\PaymentProcessor;
use App\Payment\Processors\FlutterwaveProcessor;
use App\Payment\Processors\MercadoPagoProcessor;
use App\Payment\Processors\PayPalProcessor;
use App\Payment\Processors\RazorpayProcessor;
use App\Payment\Processors\SquareProcessor;
use App\Payment\Processors\StripeProcessor;
use App\Payment\Processors\XenditProcessor;
use InvalidArgumentException;

class ProcessorFactory
{
    /**
     * Resolve a PaymentProcessor for a country, with an optional processor override.
     *
     * @param  string       $countryCode   ISO 3166-1 alpha-2 (e.g. 'US')
     * @param  string|null  $processorSlug Override (e.g. 'paypal', 'square'). Validated against
     *                                     the country's available_processors list.
     */
    public static function for(string $countryCode, ?string $processorSlug = null): PaymentProcessor
    {
        $countryCode = strtoupper($countryCode);
        $config      = config('countries.' . $countryCode);

        if (! $config) {
            throw new InvalidArgumentException(
                "Country [{$countryCode}] is not supported. Add it to config/countries.php."
            );
        }

        // If a specific processor was requested, validate it's allowed for this country
        if ($processorSlug) {
            $available = $config['available_processors'] ?? [$config['processor']];
            if (! in_array($processorSlug, $available, true)) {
                throw new InvalidArgumentException(
                    "Processor [{$processorSlug}] is not available for country [{$countryCode}]. "
                    . "Available: " . implode(', ', $available)
                );
            }
        } else {
            $processorSlug = $config['processor'];
        }

        return self::make($processorSlug);
    }

    /**
     * Resolve the correct processor for an existing mandate.
     * Reads the mandate's processor_slug — no country lookup needed.
     */
    public static function forMandate(PaymentMandate $mandate): PaymentProcessor
    {
        if (empty($mandate->processor_slug)) {
            // Legacy mandates without a stored slug — fall back to country default
            $countryCode = $mandate->lease?->property?->country_code ?? 'US';
            return self::for($countryCode);
        }

        return self::make($mandate->processor_slug);
    }

    /**
     * Return all processor slugs available for a given country.
     *
     * @return string[]  e.g. ['stripe', 'paypal', 'square']
     */
    public static function availableFor(string $countryCode): array
    {
        $config = config('countries.' . strtoupper($countryCode));

        if (! $config) {
            return [];
        }

        return $config['available_processors'] ?? [$config['processor']];
    }

    /**
     * Return all supported country codes.
     */
    public static function supportedCountries(): array
    {
        return array_keys(config('countries', []));
    }

    /**
     * Check if a country is supported.
     */
    public static function supports(string $countryCode): bool
    {
        return (bool) config('countries.' . strtoupper($countryCode));
    }

    // ── Private ──────────────────────────────────────────────────────────────

    private static function make(string $slug): PaymentProcessor
    {
        return match ($slug) {
            'stripe'      => app(StripeProcessor::class),
            'square'      => app(SquareProcessor::class),
            'paypal'      => app(PayPalProcessor::class),
            'razorpay'    => app(RazorpayProcessor::class),
            'flutterwave' => app(FlutterwaveProcessor::class),
            'xendit'      => app(XenditProcessor::class),
            'mercadopago' => app(MercadoPagoProcessor::class),
            default       => throw new InvalidArgumentException(
                "Processor [{$slug}] has no registered implementation."
            ),
        };
    }
}
