<?php

/**
 * Payment companies/brands only (one row per account you have).
 * Keys in .env → config/services.php. Per-country defaults: country_markets table.
 */
return [
    'providers' => [
        ['slug' => 'stripe',      'name' => 'Stripe',       'sort_order' => 10, 'role' => 'processor', 'service' => 'stripe',      'markets' => 'global', 'env_keys' => ['key', 'secret', 'webhook_secret'], 'auto_enable_when_configured' => true, 'dashboard_url' => 'https://dashboard.stripe.com/apikeys'],
        ['slug' => 'razorpay',    'name' => 'Razorpay',     'sort_order' => 20, 'role' => 'processor', 'service' => 'razorpay',    'markets' => ['IN'],    'env_keys' => ['key', 'secret'],               'auto_enable_when_configured' => true, 'dashboard_url' => 'https://dashboard.razorpay.com/app/keys'],
        ['slug' => 'flutterwave', 'name' => 'Flutterwave',  'sort_order' => 30, 'role' => 'processor', 'service' => 'flutterwave', 'markets' => ['NG','KE','GH','ZA','EG','TZ','UG','RW','ZM','SN','CI','CM'], 'env_keys' => ['secret_key'], 'auto_enable_when_configured' => true, 'dashboard_url' => 'https://app.flutterwave.com/dashboard/settings/apis/live'],
        ['slug' => 'xendit',      'name' => 'Xendit',       'sort_order' => 40, 'role' => 'processor', 'service' => 'xendit',      'markets' => ['ID','PH','MY','VN','TH'], 'env_keys' => ['secret_key'], 'auto_enable_when_configured' => true, 'dashboard_url' => 'https://dashboard.xendit.co/settings/developers'],
        ['slug' => 'mercadopago', 'name' => 'Mercado Pago', 'sort_order' => 50, 'role' => 'processor', 'service' => 'mercadopago', 'markets' => ['BR','MX','AR','CO','CL','PE'], 'env_keys' => ['access_token'], 'auto_enable_when_configured' => true, 'dashboard_url' => 'https://www.mercadopago.com.br/settings/account/credentials'],
        [
            'slug'       => 'square',
            'name'       => 'Square',
            'sort_order' => 60,
            'service'    => 'square',
            'role'       => 'processor',
            'markets'    => ['US', 'CA'],
            'env_keys'   => ['access_token', 'application_id', 'location_id', 'webhook_key'],
            'auto_enable_when_configured' => true,
            'dashboard_url' => 'https://developer.squareup.com/apps',
        ],
        [
            'slug'       => 'paypal',
            'name'       => 'PayPal',
            'sort_order' => 70,
            'service'    => 'paypal',
            'role'       => 'processor',
            'markets'    => 'global',
            'env_keys'   => ['client_id', 'client_secret', 'webhook_id'],
            'auto_enable_when_configured' => true,
            'dashboard_url' => 'https://developer.paypal.com/dashboard',
        ],
        [
            'slug'       => 'wise',
            'name'       => 'Wise',
            'sort_order' => 80,
            'service'    => 'wise',
            'role'       => 'payout',
            'markets'    => 'global',
            'env_keys'   => ['api_key', 'profile_id'],
            'auto_enable_when_configured' => true,
            'dashboard_url' => 'https://wise.com/gb/platform/developer',
        ],
        ['slug' => 'paddle', 'name' => 'Paddle', 'sort_order' => 90, 'service' => 'paddle', 'role' => 'subscription', 'env_keys' => ['api_key']],
        ['slug' => 'chargebee', 'name' => 'Chargebee', 'sort_order' => 100, 'service' => 'chargebee', 'role' => 'subscription', 'env_keys' => ['api_key', 'site']],
        ['slug' => 'braintree', 'name' => 'Braintree', 'sort_order' => 110, 'service' => 'braintree', 'role' => 'processor', 'env_keys' => ['merchant_id', 'public_key', 'private_key']],
        ['slug' => 'recurly', 'name' => 'Recurly', 'sort_order' => 120, 'service' => 'recurly', 'role' => 'subscription', 'env_keys' => ['api_key', 'subdomain']],
        ['slug' => 'adyen', 'name' => 'Adyen', 'sort_order' => 130, 'service' => 'adyen', 'role' => 'processor', 'env_keys' => ['api_key', 'merchant_account']],
        ['slug' => 'coinbase', 'name' => 'Coinbase Commerce', 'sort_order' => 140, 'role' => 'processor', 'env_vars' => ['COINBASE_COMMERCE_API_KEY']],
    ],
];
