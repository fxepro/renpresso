<?php

/**
 * Admin portal navigation.
 * Finance = money in/out and platform revenue (read-only reporting).
 * Operations = users, portfolio, workflow.
 * Settings = processors, per-country pricing, commissions, platform defaults.
 */
return [
    'reporting_currency' => env('PLATFORM_REPORTING_CURRENCY', 'USD'),

    'nav' => [
        [
            'label' => 'Overview',
            'items' => [
                ['route' => 'admin.dashboard', 'icon' => '📊', 'label' => 'Platform dashboard'],
            ],
        ],
        [
            'label' => 'Finance',
            'collapsible' => true,
            'items' => [
                ['route' => 'admin.revenue', 'icon' => '💰', 'label' => 'Platform revenue'],
                ['route' => 'admin.page', 'page' => 'revenue-ledger', 'icon' => '📒', 'label' => 'Revenue ledger', 'soon' => true],
                ['route' => 'admin.rent-collections', 'icon' => '🏠', 'label' => 'Rent collections'],
                ['route' => 'admin.page', 'page' => 'maintenance-payments', 'icon' => '🔧', 'label' => 'Maintenance payments', 'soon' => true],
                ['route' => 'admin.landlord-billing', 'icon' => '🧾', 'label' => 'Landlord subscriptions'],
                ['route' => 'admin.page', 'page' => 'repatriation', 'icon' => '💱', 'label' => 'FX & repatriation', 'soon' => true],
                ['route' => 'admin.page', 'page' => 'tax-export', 'icon' => '📤', 'label' => 'Tax export', 'soon' => true],
            ],
        ],
        [
            'label' => 'Operations',
            'collapsible' => true,
            'items' => [
                ['route' => 'admin.landlords', 'icon' => '🏢', 'label' => 'Landlords'],
                ['route' => 'admin.tenants', 'icon' => '👥', 'label' => 'Tenants'],
                ['route' => 'admin.properties', 'icon' => '🏘️', 'label' => 'Properties'],
                ['route' => 'admin.leases', 'icon' => '📋', 'label' => 'Leases'],
                ['route' => 'admin.maintenance-teams', 'icon' => '🧰', 'label' => 'Maintenance teams'],
                ['route' => 'admin.maintenance-requests', 'icon' => '🛠️', 'label' => 'Maintenance requests'],
                ['route' => 'admin.maintenance-invoices', 'icon' => '📄', 'label' => 'Maintenance invoices'],
                ['route' => 'admin.messages', 'icon' => '💬', 'label' => 'Email templates'],
                ['route' => 'admin.page', 'page' => 'applications', 'icon' => '📝', 'label' => 'Applications', 'soon' => true],
                ['route' => 'admin.page', 'page' => 'waitlist', 'icon' => '✉️', 'label' => 'Waitlist', 'soon' => true],
                ['route' => 'admin.page', 'page' => 'documents', 'icon' => '📁', 'label' => 'Documents', 'soon' => true],
                ['route' => 'admin.page', 'page' => 'deals', 'icon' => '🎁', 'label' => 'Deals', 'soon' => true],
                ['route' => 'admin.page', 'page' => 'kyc', 'icon' => '🪪', 'label' => 'KYC queue', 'soon' => true],
            ],
        ],
        [
            'label' => 'Settings',
            'collapsible' => true,
            'header_route' => 'admin.settings.index',
            'items' => [
                ['route' => 'admin.settings.general', 'icon' => '🌐', 'label' => 'General'],
                ['route' => 'admin.settings.payments', 'icon' => '💳', 'label' => 'Payments'],
                ['route' => 'admin.settings.markets', 'icon' => '🌍', 'label' => 'Markets & pricing'],
            ],
        ],
        [
            'label' => 'Help',
            'items' => [
                ['route' => 'help.collateral', 'icon' => '📄', 'label' => 'Collateral & runbooks'],
                ['route' => 'help.videos', 'icon' => '▶', 'label' => 'Videos'],
                ['route' => 'admin.page', 'page' => 'helpline', 'icon' => '📞', 'label' => 'Helpline log', 'soon' => true],
            ],
        ],
    ],

    'pages' => 'revenue|revenue-ledger|rent-collections|maintenance-payments|landlord-billing|repatriation|tax-export|landlords|tenants|properties|leases|maintenance-teams|maintenance-requests|maintenance-invoices|messages|applications|waitlist|documents|deals|kyc|helpline',
];
