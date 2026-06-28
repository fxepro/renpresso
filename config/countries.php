<?php

/**
 * ═══════════════════════════════════════════════════════════════
 * Renpresso — Country → Processor Lookup Table
 *
 * This is the single source of truth for payment routing.
 * Adding a new country = adding one row here. No code changes.
 *
 * Keys: ISO 3166-1 alpha-2 country codes
 * ═══════════════════════════════════════════════════════════════
 */

return [

    // ── North America ───────────────────────────────────────────
    // Stripe = default (required for ACH/EFT bank debit)
    // Square = card-first option (lowest card fee: 2.6%)
    // PayPal = wallet option (200+ countries, no card/bank details needed)
    'US' => [
        'processor'            => 'stripe',
        'currency'             => 'USD',
        'method'               => 'ACH',
        'available_processors' => ['stripe', 'square', 'paypal'],
    ],
    'CA' => [
        'processor'            => 'stripe',
        'currency'             => 'CAD',
        'method'               => 'EFT',
        'available_processors' => ['stripe', 'square', 'paypal'],
    ],

    // ── Western Europe (Stripe) ─────────────────────────────────
    'GB' => ['processor' => 'stripe',      'currency' => 'GBP', 'method' => 'BACS'],
    'FR' => ['processor' => 'stripe',      'currency' => 'EUR', 'method' => 'SEPA'],
    'DE' => ['processor' => 'stripe',      'currency' => 'EUR', 'method' => 'SEPA'],
    'ES' => ['processor' => 'stripe',      'currency' => 'EUR', 'method' => 'SEPA'],
    'IT' => ['processor' => 'stripe',      'currency' => 'EUR', 'method' => 'SEPA'],
    'NL' => ['processor' => 'stripe',      'currency' => 'EUR', 'method' => 'SEPA'],
    'PT' => ['processor' => 'stripe',      'currency' => 'EUR', 'method' => 'SEPA'],
    'BE' => ['processor' => 'stripe',      'currency' => 'EUR', 'method' => 'SEPA'],
    'IE' => ['processor' => 'stripe',      'currency' => 'EUR', 'method' => 'SEPA'],
    'AT' => ['processor' => 'stripe',      'currency' => 'EUR', 'method' => 'SEPA'],
    'CH' => ['processor' => 'stripe',      'currency' => 'CHF', 'method' => 'SEPA'],
    'SE' => ['processor' => 'stripe',      'currency' => 'SEK', 'method' => 'SEPA'],
    'NO' => ['processor' => 'stripe',      'currency' => 'NOK', 'method' => 'SEPA'],
    'DK' => ['processor' => 'stripe',      'currency' => 'DKK', 'method' => 'SEPA'],
    'FI' => ['processor' => 'stripe',      'currency' => 'EUR', 'method' => 'SEPA'],
    'PL' => ['processor' => 'stripe',      'currency' => 'PLN', 'method' => 'SEPA'],
    'CZ' => ['processor' => 'stripe',      'currency' => 'CZK', 'method' => 'SEPA'],
    'RO' => ['processor' => 'stripe',      'currency' => 'RON', 'method' => 'SEPA'],
    'LU' => ['processor' => 'stripe',      'currency' => 'EUR', 'method' => 'SEPA'],
    'GR' => ['processor' => 'stripe',      'currency' => 'EUR', 'method' => 'SEPA'],

    // ── Pacific & Asia Pacific (Stripe) ────────────────────────
    'AU' => ['processor' => 'stripe',      'currency' => 'AUD', 'method' => 'BECS'],
    'NZ' => ['processor' => 'stripe',      'currency' => 'NZD', 'method' => 'Bank Debit'],
    'SG' => ['processor' => 'stripe',      'currency' => 'SGD', 'method' => 'PayNow'],
    'HK' => ['processor' => 'stripe',      'currency' => 'HKD', 'method' => 'FPS'],
    'JP' => ['processor' => 'stripe',      'currency' => 'JPY', 'method' => 'Konbini'],

    // ── India (Razorpay) ────────────────────────────────────────
    'IN' => ['processor' => 'razorpay',    'currency' => 'INR', 'method' => 'UPI'],

    // ── Africa (Flutterwave) ────────────────────────────────────
    'NG' => ['processor' => 'flutterwave', 'currency' => 'NGN', 'method' => 'Bank Transfer'],
    'KE' => ['processor' => 'flutterwave', 'currency' => 'KES', 'method' => 'M-Pesa'],
    'GH' => ['processor' => 'flutterwave', 'currency' => 'GHS', 'method' => 'Mobile Money'],
    'ZA' => ['processor' => 'flutterwave', 'currency' => 'ZAR', 'method' => 'EFT'],
    'EG' => ['processor' => 'flutterwave', 'currency' => 'EGP', 'method' => 'Bank Transfer'],
    'TZ' => ['processor' => 'flutterwave', 'currency' => 'TZS', 'method' => 'M-Pesa'],
    'UG' => ['processor' => 'flutterwave', 'currency' => 'UGX', 'method' => 'Mobile Money'],
    'RW' => ['processor' => 'flutterwave', 'currency' => 'RWF', 'method' => 'Mobile Money'],
    'ZM' => ['processor' => 'flutterwave', 'currency' => 'ZMW', 'method' => 'Mobile Money'],
    'SN' => ['processor' => 'flutterwave', 'currency' => 'XOF', 'method' => 'Wave'],
    'CI' => ['processor' => 'flutterwave', 'currency' => 'XOF', 'method' => 'Mobile Money'],
    'CM' => ['processor' => 'flutterwave', 'currency' => 'XAF', 'method' => 'Mobile Money'],

    // ── Southeast Asia (Xendit) ─────────────────────────────────
    'ID' => ['processor' => 'xendit',      'currency' => 'IDR', 'method' => 'Virtual Account'],
    'PH' => ['processor' => 'xendit',      'currency' => 'PHP', 'method' => 'GCash'],
    'MY' => ['processor' => 'xendit',      'currency' => 'MYR', 'method' => 'FPX'],
    'VN' => ['processor' => 'xendit',      'currency' => 'VND', 'method' => 'Bank Transfer'],
    'TH' => ['processor' => 'xendit',      'currency' => 'THB', 'method' => 'PromptPay'],

    // ── Latin America (Mercado Pago) ────────────────────────────
    'BR' => ['processor' => 'mercadopago', 'currency' => 'BRL', 'method' => 'Pix'],
    'MX' => ['processor' => 'mercadopago', 'currency' => 'MXN', 'method' => 'SPEI'],
    'AR' => ['processor' => 'mercadopago', 'currency' => 'ARS', 'method' => 'Bank Transfer'],
    'CO' => ['processor' => 'mercadopago', 'currency' => 'COP', 'method' => 'PSE'],
    'CL' => ['processor' => 'mercadopago', 'currency' => 'CLP', 'method' => 'WebPay'],
    'PE' => ['processor' => 'mercadopago', 'currency' => 'PEN', 'method' => 'Yape'],

];
