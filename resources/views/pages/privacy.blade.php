@extends('layouts.utility', ['page' => 'privacy'])

@section('title', 'Privacy Policy')
@section('meta_description', config('site.name') . ' privacy policy. How we collect, use, and protect your personal data across all markets.')
@section('heading', 'Privacy Policy')
@section('meta')
  Last updated: <span>1 May 2025</span> · Effective: <span>1 May 2025</span>
@endsection

@section('utility')

<div class="legal-highlight">
  <p>This policy explains how {{ config('app.name') }} collects, uses, stores, and protects personal data. It applies to landlords, tenants, and visitors to our platform across all supported markets. We are committed to plain-language privacy — if something is unclear, <a href="{{ url('/contact') }}">contact us</a>.</p>
</div>

<nav class="legal-toc" aria-label="Table of contents">
  <h3>Contents</h3>
  <ol>
    <li><a href="#who-we-are">Who we are</a></li>
    <li><a href="#data-we-collect">Data we collect</a></li>
    <li><a href="#how-we-use">How we use your data</a></li>
    <li><a href="#data-sharing">Data sharing</a></li>
    <li><a href="#data-storage">Data storage and retention</a></li>
    <li><a href="#your-rights">Your rights</a></li>
    <li><a href="#cookies">Cookies</a></li>
    <li><a href="#international">International transfers</a></li>
    <li><a href="#children">Children</a></li>
    <li><a href="#changes">Changes to this policy</a></li>
    <li><a href="#contact">Contact us</a></li>
  </ol>
</nav>

<section class="legal-section" id="who-we-are">
  <h2>1. Who we are</h2>
  <p>{{ config('app.name') }} is a property management platform that enables landlords to collect rent from tenants in local currencies across multiple countries. The data controller for personal data processed through the {{ config('app.name') }} platform is {{ config('app.name') }} Inc., a company incorporated in the United States.</p>
  <p>Where we process data of individuals in the European Economic Area or United Kingdom, we comply with the General Data Protection Regulation (GDPR) and UK GDPR respectively. We comply with applicable local privacy laws in every market we operate.</p>
</section>

<section class="legal-section" id="data-we-collect">
  <h2>2. Data we collect</h2>
  <h3>From landlords</h3>
  <ul>
    <li>Name, email address, phone number, and home country</li>
    <li>Identity verification documents (for payment provider KYC requirements)</li>
    <li>Bank account details (connected via your payment provider — we do not store full account numbers)</li>
    <li>Property details: address, country, rent amount, lease terms</li>
    <li>Documents you upload: lease agreements, inspection reports, insurance certificates</li>
    <li>Transaction records: payment dates, amounts, FX rates at time of collection</li>
    <li>Communication records: messages sent via our in-app messaging system</li>
  </ul>
  <h3>From tenants</h3>
  <ul>
    <li>Name, email address, and phone number (as provided by the landlord or entered during onboarding)</li>
    <li>Payment authorization details (bank account or card — held by your payment provider, not us)</li>
    <li>Maintenance request details: descriptions, photos, and status updates</li>
    <li>Communication records: messages sent via our in-app messaging system</li>
  </ul>
  <h3>From all users automatically</h3>
  <ul>
    <li>Device and browser information, IP address, and locale</li>
    <li>Usage data: pages visited, features used, time spent</li>
    <li>Cookies and similar tracking technologies (see Section 7)</li>
  </ul>
</section>

<section class="legal-section" id="how-we-use">
  <h2>3. How we use your data</h2>
  <p>We use personal data to:</p>
  <ul>
    <li>Provide, operate, and improve the {{ config('app.name') }} platform</li>
    <li>Process rent payments and maintain transaction records</li>
    <li>Send transactional notifications (payment confirmations, arrears alerts, maintenance updates)</li>
    <li>Generate tax-ready income reports for landlords</li>
    <li>Comply with applicable laws and regulations in each market we operate</li>
    <li>Respond to support requests and enquiries</li>
    <li>Detect and prevent fraud and unauthorised access</li>
  </ul>
  <p>We do not use your personal data for advertising, sell it to third parties, or share it for marketing purposes.</p>
</section>

<section class="legal-section" id="data-sharing">
  <h2>4. Data sharing</h2>
  <p>We share personal data only where necessary to deliver the service:</p>
  <ul>
    <li><strong>Payment providers:</strong> We share payment-relevant data with locally licensed payment providers in each market to process rent collections. These providers are bound by their own privacy policies and regulatory obligations.</li>
    <li><strong>Cloud infrastructure:</strong> We use cloud providers to host the platform. Data is stored in the appropriate region for each user (see Section 5).</li>
    <li><strong>Legal requirements:</strong> We may disclose data where required by law, court order, or regulatory authority in applicable jurisdictions.</li>
    <li><strong>Business transfers:</strong> In the event of a merger, acquisition, or sale of the company, personal data may be transferred as part of that transaction. We will notify users in advance.</li>
  </ul>
  <p>We do not sell, rent, or trade personal data to any third party for commercial purposes.</p>
</section>

<section class="legal-section" id="data-storage">
  <h2>5. Data storage and retention</h2>
  <h3>Where we store data</h3>
  <ul>
    <li>Data relating to EU and EEA users is stored in EU-based data centres</li>
    <li>Data is stored in region-appropriate data centres where required by applicable law</li>
    <li>Data relating to all other users is stored in US-based data centres</li>
  </ul>
  <h3>How long we keep data</h3>
  <ul>
    <li>Lease documents, payment records, and communications are retained for a minimum of 7 years from the end of the relevant tenancy — consistent with standard tax record requirements across our markets</li>
    <li>Account data is retained for as long as your account is active, plus 90 days after deletion to allow for recovery</li>
    <li>After the retention period, data is securely deleted or anonymised</li>
  </ul>
  <div class="legal-highlight">
    <p>FX rates at the time of each payment are stored permanently and are never recalculated retroactively. This is intentional — it ensures your tax records remain accurate to the original transaction date.</p>
  </div>
</section>

<section class="legal-section" id="your-rights">
  <h2>6. Your rights</h2>
  <p>Depending on your country of residence, you may have the following rights regarding your personal data:</p>
  <ul>
    <li><strong>Access:</strong> Request a copy of the personal data we hold about you</li>
    <li><strong>Correction:</strong> Request correction of inaccurate or incomplete data</li>
    <li><strong>Deletion:</strong> Request deletion of your personal data (subject to legal retention obligations)</li>
    <li><strong>Portability:</strong> Request your data in a structured, machine-readable format</li>
    <li><strong>Objection:</strong> Object to certain processing of your data</li>
    <li><strong>Restriction:</strong> Request that we restrict processing while a dispute is resolved</li>
    <li><strong>Withdrawal of consent:</strong> Where processing is based on consent, you may withdraw it at any time</li>
  </ul>
  <p>To exercise any of these rights, use our <a href="{{ url('/contact') }}">contact form</a>. We will respond within 30 days. If you are in the EU or UK and are unsatisfied with our response, you may lodge a complaint with your local data protection authority.</p>
</section>

<section class="legal-section" id="cookies">
  <h2>7. Cookies</h2>
  <p>We use cookies and similar technologies for the following purposes:</p>
  <ul>
    <li><strong>Essential cookies:</strong> Required for the platform to function — session management, authentication, security</li>
    <li><strong>Analytics cookies:</strong> Help us understand how users interact with the platform so we can improve it. We use privacy-respecting analytics tools.</li>
    <li><strong>Preference cookies:</strong> Remember your settings such as language and region</li>
  </ul>
  <p>We do not use advertising or tracking cookies. You can manage your cookie preferences at any time via our <a href="{{ url('/cookies') }}">Cookie Policy</a> page.</p>
</section>

<section class="legal-section" id="international">
  <h2>8. International data transfers</h2>
  <p>Where personal data is transferred outside your country of residence, we ensure appropriate safeguards are in place:</p>
  <ul>
    <li>For EU/EEA data transferred outside the EEA: Standard Contractual Clauses (SCCs) approved by the European Commission</li>
    <li>For UK data transferred outside the UK: International Data Transfer Agreements (IDTAs)</li>
    <li>Compliance with applicable cross-border transfer provisions where required</li>
  </ul>
</section>

<section class="legal-section" id="children">
  <h2>9. Children</h2>
  <p>{{ config('app.name') }} is not intended for use by individuals under the age of 18. We do not knowingly collect personal data from children. If you believe we have collected data from a child, please <a href="{{ url('/contact') }}">contact us</a> immediately.</p>
</section>

<section class="legal-section" id="changes">
  <h2>10. Changes to this policy</h2>
  <p>We may update this policy from time to time. When we make material changes, we will notify registered users by email at least 14 days before the changes take effect. The updated policy will be published on this page with a revised effective date.</p>
  <p>Continued use of the platform after the effective date constitutes acceptance of the updated policy.</p>
</section>

<section class="legal-section" id="contact">
  <h2>11. Contact us</h2>
  <p>For privacy-related questions, requests, or complaints, use our <a href="{{ url('/contact') }}">contact form</a>. We aim to respond to all privacy enquiries within 5 business days.</p>
</section>

@endsection
