<?php

/**
 * Maintenance company compliance uploads (see docs/Document requirements).
 * Director identity uses the account owner's user KYC fields, not this table.
 */
return [
    'sections' => [
        'address' => [
            'title' => 'Registered address',
            'lead'  => 'Proof of your company’s registered business address (utility bill or bank statement, under 3 months).',
            'documents' => [
                'proof_of_registered_address' => [
                    'label'    => 'Proof of registered address',
                    'hint'     => 'JPEG, PNG, WebP, or PDF — max 10 MB.',
                    'required' => true,
                ],
            ],
        ],
        'business' => [
            'title' => 'Company (KYB)',
            'lead'  => 'Know Your Business documents for the maintenance entity.',
            'documents' => [
                'company_registration' => [
                    'label'    => 'Company registration certificate',
                    'hint'     => 'Official registry extract or certificate of registration.',
                    'required' => true,
                ],
                'certificate_of_incorporation' => [
                    'label'    => 'Certificate of incorporation',
                    'hint'     => 'If issued separately from registration in your jurisdiction.',
                    'required' => true,
                ],
                'ubo_declaration' => [
                    'label'    => 'Ultimate Beneficial Owner (UBO) declaration',
                    'hint'     => 'Signed declaration identifying owners with 25%+ control (EU/UK standard).',
                    'required' => true,
                ],
            ],
        ],
        'financial' => [
            'title' => 'Financial',
            'lead'  => 'Required to receive payouts and meet tax reporting obligations.',
            'documents' => [
                'bank_account_verification' => [
                    'label'    => 'Bank account verification',
                    'hint'     => 'Bank letter, void cheque, or statement showing account for payouts.',
                    'required' => true,
                ],
                'tax_identification' => [
                    'label'    => 'Tax identification (TIN / VAT / GST)',
                    'hint'     => 'Tax registration certificate or official TIN letter.',
                    'required' => true,
                    'fields'   => ['reference_number' => 'Tax ID / registration number'],
                ],
            ],
        ],
        'operations' => [
            'title' => 'Trade & insurance',
            'lead'  => 'Documents required before assigning staff to occupied properties.',
            'documents' => [
                'trade_licence' => [
                    'label'    => 'Trade / contractor licence',
                    'hint'     => 'Valid licence for your primary trade and jurisdiction.',
                    'required' => true,
                    'fields'   => ['reference_number' => 'Licence number', 'expires_on' => 'Expiry date'],
                ],
                'public_liability_insurance' => [
                    'label'    => 'Public liability insurance',
                    'hint'     => 'Minimum £1M / $1M coverage is typical.',
                    'required' => true,
                    'fields'   => ['expires_on' => 'Policy expiry date'],
                ],
                'professional_indemnity_insurance' => [
                    'label'    => 'Professional indemnity insurance',
                    'hint'     => 'Required for structural or electrical work where applicable.',
                    'required' => false,
                    'fields'   => ['expires_on' => 'Policy expiry date'],
                ],
                'dbs_background_check' => [
                    'label'    => 'DBS / criminal background check',
                    'hint'     => 'UK: DBS certificate. US: state background check for property access.',
                    'required' => true,
                    'fields'   => ['reference_number' => 'Certificate / reference number', 'expires_on' => 'Expiry date (if shown)'],
                ],
            ],
        ],
    ],
];
