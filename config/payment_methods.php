<?php

/**
 * Payment methods (how money moves). US/CA/EU only — other regions use local rails via country defaults.
 * Processor per method is chosen in admin (payment_method_settings), not hard-coded here.
 */
return [
    'us_ca_eu_country_codes' => ['US', 'CA', 'GB', 'FR', 'DE', 'IE', 'NL', 'BE', 'ES', 'IT', 'PT', 'AT', 'CH', 'SE', 'NO', 'DK', 'FI', 'PL', 'AU', 'SG'],

    'fee_paid_by' => [
        'landlord' => 'Landlord',
        'tenant'   => 'Tenant',
        'platform' => 'Renpresso (absorbed)',
    ],

    'methods' => [
        [
            'slug'                  => 'ach',
            'label'                 => 'ACH / bank debit',
            'scope'                 => 'us_ca_eu',
            'default_provider_slug' => 'stripe',
            'processor_fee_label'   => '~0.8% (capped)',
            'fee_paid_by'           => 'landlord',
        ],
        [
            'slug'                  => 'credit_card',
            'label'                 => 'Credit card',
            'scope'                 => 'us_ca_eu',
            'default_provider_slug' => 'stripe',
            'processor_fee_label'   => '2.9% + $0.30',
            'fee_paid_by'           => 'landlord',
        ],
        [
            'slug'                  => 'debit_card',
            'label'                 => 'Debit card',
            'scope'                 => 'us_ca_eu',
            'default_provider_slug' => 'stripe',
            'processor_fee_label'   => '2.9% + $0.30',
            'fee_paid_by'           => 'landlord',
        ],
    ],
];
