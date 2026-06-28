<?php

return [

    // ── Stripe ──────────────────────────────────────────────────
    'stripe' => [
        'key'            => env('STRIPE_KEY'),
        'secret'         => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],

    // ── Razorpay (India) ────────────────────────────────────────
    'razorpay' => [
        'key'            => env('RAZORPAY_KEY'),
        'secret'         => env('RAZORPAY_SECRET'),
        'webhook_secret' => env('RAZORPAY_WEBHOOK_SECRET'),
    ],

    // ── Flutterwave (Africa) ────────────────────────────────────
    'flutterwave' => [
        'public_key'      => env('FLUTTERWAVE_PUBLIC_KEY'),
        'secret_key'      => env('FLUTTERWAVE_SECRET_KEY'),
        'encryption_key'  => env('FLUTTERWAVE_ENCRYPTION_KEY'),
        'webhook_secret'  => env('FLUTTERWAVE_WEBHOOK_SECRET'),
    ],

    // ── Xendit (SE Asia) ────────────────────────────────────────
    'xendit' => [
        'secret_key'     => env('XENDIT_SECRET_KEY'),
        'webhook_token'  => env('XENDIT_WEBHOOK_TOKEN'),
    ],

    // ── Mercado Pago (LatAm) ────────────────────────────────────
    'mercadopago' => [
        'access_token'   => env('MERCADOPAGO_ACCESS_TOKEN'),
        'webhook_secret' => env('MERCADOPAGO_WEBHOOK_SECRET'),
    ],

    // ── Square (US/CA card collection) ──────────────────────────
    'square' => [
        'application_id' => env('SQUARE_APPLICATION_ID'),
        'access_token'   => env('SQUARE_ACCESS_TOKEN'),
        'location_id'    => env('SQUARE_LOCATION_ID'),
        'webhook_key'    => env('SQUARE_WEBHOOK_SIGNATURE_KEY'),
        'environment'    => env('SQUARE_ENVIRONMENT', 'sandbox'),
    ],

    // ── PayPal (wallet + global card collection) ─────────────────
    'paypal' => [
        'client_id'     => env('PAYPAL_CLIENT_ID'),
        'client_secret' => env('PAYPAL_CLIENT_SECRET'),
        'webhook_id'    => env('PAYPAL_WEBHOOK_ID'),
        'mode'          => env('PAYPAL_MODE', 'sandbox'),
    ],

    // ── Wise (outbound landlord payouts — NOT a payment processor) ─
    'wise' => [
        'api_key'     => env('WISE_API_KEY'),
        'profile_id'  => env('WISE_PROFILE_ID'),
        'environment' => env('WISE_ENVIRONMENT', 'sandbox'),
    ],

    'paddle' => [
        'api_key'        => env('PADDLE_API_KEY'),
        'webhook_secret' => env('PADDLE_WEBHOOK_SECRET'),
    ],

    'chargebee' => [
        'api_key' => env('CHARGEBEE_API_KEY'),
        'site'    => env('CHARGEBEE_SITE'),
    ],

    'braintree' => [
        'merchant_id' => env('BRAINTREE_MERCHANT_ID'),
        'public_key'  => env('BRAINTREE_PUBLIC_KEY'),
        'private_key' => env('BRAINTREE_PRIVATE_KEY'),
    ],

    'recurly' => [
        'api_key'    => env('RECURLY_API_KEY'),
        'subdomain'  => env('RECURLY_SUBDOMAIN'),
    ],

    'adyen' => [
        'api_key'           => env('ADYEN_API_KEY'),
        'merchant_account'  => env('ADYEN_MERCHANT_ACCOUNT'),
        'hmac_key'          => env('ADYEN_HMAC_KEY'),
    ],

    // ── FX / Exchange Rate API ──────────────────────────────────
    'fx' => [
        'api_key' => env('FX_API_KEY'),
        'api_url' => env('FX_API_URL', 'https://v6.exchangerate-api.com/v6'),
    ],

    // ── Mail ────────────────────────────────────────────────────
    'mailgun' => [
        'domain'    => env('MAILGUN_DOMAIN'),
        'secret'    => env('MAILGUN_SECRET'),
        'endpoint'  => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme'    => 'https',
    ],

    'ses' => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

];
