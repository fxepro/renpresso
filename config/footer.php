<?php

return [
    'columns' => [
        [
            'title' => 'Product',
            'links' => [
                ['label' => 'How it works',      'href' => '/how-it-works'],
                ['label' => 'Features',          'href' => '/features'],
                ['label' => 'Rental types',      'href' => '/rental-types'],
                ['label' => 'Pricing',           'href' => '/pricing'],
                ['label' => 'Countries',         'href' => '/countries'],
                ['label' => 'Long-term rentals', 'href' => '/listings/long-term'],
                ['label' => 'Short-term stays',  'href' => '/listings/short-term'],
                ['label' => 'Join waitlist',     'href' => '/waitlist', 'badge' => 'Free'],
            ],
        ],
        [
            'title' => 'Landlords',
            'links' => [
                ['label' => 'Rent collection', 'href' => '/features#collection'],
                ['label' => 'Dashboard',       'href' => '/features#dashboard'],
                ['label' => 'Documents',       'href' => '/features#documents'],
                ['label' => 'Tax export',      'href' => '/features#tax'],
                ['label' => 'Maintenance',     'href' => '/features#maintenance'],
            ],
        ],
        [
            'title' => 'Tenants',
            'links' => [
                ['label' => 'Tenant experience', 'href' => '/features#tenant'],
                ['label' => 'Paying rent',       'href' => '/features#payments'],
                ['label' => 'Receipts',          'href' => '/features#receipts'],
                ['label' => 'Raise an issue',    'href' => '/features#maintenance'],
            ],
        ],
        [
            'title' => 'Company',
            'links' => [
                ['label' => 'About',   'href' => '/about'],
                ['label' => 'Blog',    'href' => '/blog'],
                ['label' => 'Contact', 'href' => '/contact'],
                ['label' => 'Careers', 'href' => '/careers', 'badge' => 'Hiring'],
            ],
        ],
    ],

    'legal' => [
        ['label' => 'Privacy',  'href' => '/privacy',  'page' => 'privacy'],
        ['label' => 'Terms',    'href' => '/terms',    'page' => 'terms'],
        ['label' => 'Cookies',  'href' => '/cookies',  'page' => 'cookies'],
        ['label' => 'Security', 'href' => '/security', 'page' => 'security'],
    ],

    'compliance' => [
        'SOC 2 aligned practices',
        'Licensed payment processors',
        'Data encrypted in transit & at rest',
        '7-year document retention',
    ],
];
