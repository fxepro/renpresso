<?php

namespace App\Support;

class CurrencyDisplay
{
    public const DECIMAL_PLACES = 2;

    /** Currencies shown without fractional units (tenant-facing residence amounts). */
    public const ZERO_DECIMAL_CURRENCIES = ['JPY', 'KRW', 'VND'];

    public static function decimalPlaces(string $currencyCode): int
    {
        return in_array(strtoupper($currencyCode), self::ZERO_DECIMAL_CURRENCIES, true)
            ? 0
            : self::DECIMAL_PLACES;
    }

    public static function amountStep(string $currencyCode): string
    {
        return self::decimalPlaces($currencyCode) === 0 ? '1' : '0.01';
    }

    public static function symbol(string $code): string
    {
        return match (strtoupper($code)) {
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'INR' => '₹',
            'IDR' => 'Rp',
            'NGN' => '₦',
            'JPY' => '¥',
            'CAD' => 'CA$',
            'AUD' => 'A$',
            'SGD' => 'S$',
            'CHF' => 'CHF ',
            default => strtoupper($code).' ',
        };
    }

    public static function formatMajor(?float $amount, string $currencyCode): string
    {
        if ($amount === null || $amount <= 0) {
            return '—';
        }

        $symbol = self::symbol($currencyCode);
        $decimals = self::decimalPlaces($currencyCode);

        return $symbol.number_format($amount, $decimals, '.', ',');
    }

    public static function formatMinor(?int $minorUnits, string $currencyCode): string
    {
        if (! $minorUnits || $minorUnits <= 0) {
            return '—';
        }

        return self::formatMajor($minorUnits / 100, $currencyCode);
    }

    public static function formatMajorWithCode(?float $amount, string $currencyCode): string
    {
        if ($amount === null || $amount <= 0) {
            return '—';
        }

        return number_format($amount, self::decimalPlaces($currencyCode), '.', ',')
            .' '.strtoupper($currencyCode);
    }

    public static function formatMinorWithCode(?int $minorUnits, string $currencyCode): string
    {
        if (! $minorUnits || $minorUnits <= 0) {
            return '—';
        }

        return self::formatMajorWithCode($minorUnits / 100, $currencyCode);
    }
}
