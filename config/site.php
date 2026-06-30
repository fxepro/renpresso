<?php

return [
    'name'        => env('APP_NAME', 'Renpresso'),
    'tagline'     => 'Collect rent reliably. Manage every unit.',
    'description' => 'Property management for independent landlords. Collect rent via ACH, track every unit, and export tax-ready reports.',
    'email'       => env('MAIL_FROM_ADDRESS', 'hello@renpresso.com'),
    'url'         => env('APP_URL', 'https://renpresso.com'),

    'logo' => [
        'prefix' => 'Ren',
        'accent' => 'presso',
    ],

    'social' => [
        ['label' => 'Twitter',  'href' => '#', 'icon' => '𝕏'],
        ['label' => 'LinkedIn', 'href' => '#', 'icon' => 'in'],
        ['label' => 'GitHub',   'href' => '#', 'icon' => '⌥'],
    ],

    'regions' => [
        '🇺🇸 United States',
        '🇨🇦 Canada',
        '🇬🇧 United Kingdom',
        '🇦🇺 Australia',
    ],
];
