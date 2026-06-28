<?php

return [
    'articles' => [
        [
            'id' => 'getting-started',
            'title' => 'Getting started',
            'tags' => ['onboarding', 'dashboard', 'overview'],
            'body' => 'After sign-in, landlords manage properties, leases, and tenants from the Portfolio section. Tenants see lease details, payments, and maintenance under Home. Use Messages for lease-related communication.',
        ],
        [
            'id' => 'rent-collection',
            'title' => 'Rent collection',
            'tags' => ['payments', 'rent', 'mandate', 'autopay'],
            'body' => 'Rent is stored in minor currency units and collected on the due day you set on each lease. Payment processors (Stripe, Razorpay, and others) depend on the property country. Tenants can add card, ACH, PayPal, or crypto methods in Account → Payment.',
        ],
        [
            'id' => 'leases',
            'title' => 'Leases & tenants',
            'tags' => ['lease', 'tenant', 'invite'],
            'body' => 'Create a lease from a property, assign a tenant email, and set rent amount, currency, and due day. Active leases appear on the tenant dashboard. Multi-unit properties support unit labels and per-unit leases.',
        ],
        [
            'id' => 'maintenance',
            'title' => 'Maintenance requests',
            'tags' => ['maintenance', 'repair', 'photos'],
            'body' => 'Tenants submit requests with a title, description, and optional photos. Landlords and assigned maintenance staff can update status and assign work. Tenants may edit or delete requests only while status is submitted.',
        ],
        [
            'id' => 'messages',
            'title' => 'Messages',
            'tags' => ['communication', 'chat', 'inbox'],
            'body' => 'Messages are organized by property or lease thread. All parties on a lease can participate. Use Messages under Communications in the sidebar.',
        ],
        [
            'id' => 'documents',
            'title' => 'Documents',
            'tags' => ['files', 'upload', 'lease documents'],
            'body' => 'Upload and store lease-related documents from the Documents area. Files can be linked to properties, leases, and maintenance requests where supported.',
        ],
        [
            'id' => 'tenant-account',
            'title' => 'Tenant account & ID',
            'tags' => ['profile', 'kyc', 'government id'],
            'body' => 'Tenants update phone in Profile; legal name comes from the lease and government ID upload. ID verification may be pending, verified, or rejected with a reason shown on the profile page.',
        ],
        [
            'id' => 'landlord-account',
            'title' => 'Landlord account',
            'tags' => ['billing', 'portfolio', 'settings'],
            'body' => 'Landlords configure portfolio defaults and billing from Account in the sidebar footer. Property creation can prefill multi-unit capacity from account settings.',
        ],
        [
            'id' => 'fx-ledger',
            'title' => 'FX & repatriation',
            'tags' => ['currency', 'fx', 'cross-border'],
            'body' => 'FX Ledger tracks conversion and repatriation for cross-border rent. Availability depends on property country and processor. Tax export is planned for reporting periods.',
        ],
        [
            'id' => 'processors',
            'title' => 'Payment processors',
            'tags' => ['stripe', 'razorpay', 'webhooks'],
            'body' => 'Each property locks to a processor slug based on country. Webhooks update payment status asynchronously. Failed payments respect the lease grace period before late status.',
        ],
        [
            'id' => 'deals',
            'title' => 'Deals — insurance & coupons',
            'tags' => ['insurance', 'coupons', 'offers'],
            'body' => 'The Deals section under Communications lists Insurance and Coupons. Partner offers and enrollment will be added over time.',
        ],
        [
            'id' => 'help-videos',
            'title' => 'Videos & collateral',
            'tags' => ['help', 'training', 'pdf'],
            'body' => 'Help → Videos hosts walkthroughs; Collateral hosts PDFs and one-pagers. This helpline assistant searches the same product knowledge base.',
        ],
        [
            'id' => 'security',
            'title' => 'Security',
            'tags' => ['password', '2fa', 'login'],
            'body' => 'Change your password from Account → Security. Two-factor authentication is rolling out; the toggle may show as coming soon until enabled for your account.',
        ],
        [
            'id' => 'maintenance-team',
            'title' => 'Maintenance team',
            'tags' => ['staff', 'assign', 'vendor'],
            'body' => 'Landlords add maintenance staff from Maintenance team. Staff register with a dedicated link, then landlords assign them to properties and requests.',
        ],
    ],
];
