@extends('layouts.utility', ['page' => 'terms'])

@section('title', 'Terms of Service')
@section('meta_description', config('site.name') . ' terms of service. The rules and responsibilities that govern your use of the platform.')
@section('heading', 'Terms of Service')
@section('meta')
  Last updated: <span>1 May 2025</span> · Effective: <span>1 May 2025</span>
@endsection

@section('utility')

<div class="legal-highlight">
  <p>By creating an account or using the {{ config('app.name') }} platform, you agree to these terms. Please read them carefully. If you have questions, <a href="{{ url('/contact') }}">contact us</a> before proceeding.</p>
</div>

<nav class="legal-toc" aria-label="Table of contents">
  <h3>Contents</h3>
  <ol>
    <li><a href="#definitions">Definitions</a></li>
    <li><a href="#account">Account registration</a></li>
    <li><a href="#platform-use">Platform use</a></li>
    <li><a href="#landlord-obligations">Landlord obligations</a></li>
    <li><a href="#tenant-obligations">Tenant obligations</a></li>
    <li><a href="#payments">Payments and fees</a></li>
    <li><a href="#data-ownership">Data ownership</a></li>
    <li><a href="#liability">Limitation of liability</a></li>
    <li><a href="#prohibited">Prohibited conduct</a></li>
    <li><a href="#termination">Termination</a></li>
    <li><a href="#disputes">Disputes and governing law</a></li>
    <li><a href="#changes">Changes to these terms</a></li>
  </ol>
</nav>

<section class="legal-section" id="definitions">
  <h2>1. Definitions</h2>
  <p>In these terms:</p>
  <ul>
    <li><strong>"Platform"</strong> means the {{ config('app.name') }} web application, mobile application, and associated services</li>
    <li><strong>"Landlord"</strong> means a user who registers to manage properties and collect rent via the Platform</li>
    <li><strong>"Tenant"</strong> means a user who is invited by a Landlord to pay rent and access tenancy-related features via the Platform</li>
    <li><strong>"We", "us", "our"</strong> means {{ config('app.name') }} Inc.</li>
    <li><strong>"Payment Provider"</strong> means the locally licensed payment processor used in each supported country</li>
  </ul>
</section>

<section class="legal-section" id="account">
  <h2>2. Account registration</h2>
  <p>To use the Platform, you must create an account with accurate and complete information. You are responsible for maintaining the security of your account credentials and for all activity that occurs under your account.</p>
  <p>Landlords must complete identity verification as required by applicable Payment Providers. Failure to complete verification may result in limited access to payment features.</p>
  <p>You must be at least 18 years of age to create an account. Accounts may not be created on behalf of another person without their explicit consent.</p>
</section>

<section class="legal-section" id="platform-use">
  <h2>3. Platform use</h2>
  <p>{{ config('app.name') }} grants you a limited, non-exclusive, non-transferable licence to use the Platform for its intended purpose: managing rental properties, collecting rent, and facilitating landlord-tenant communication.</p>
  <p>The Platform is a management and collection tool. We are not a party to any tenancy agreement between a Landlord and a Tenant. We are not a remittance service, financial advisor, legal advisor, or tax advisor.</p>
  <div class="legal-highlight">
    <p>{{ config('app.name') }} collects rent locally in each country. We do not execute or facilitate cross-border transfers of funds. Moving money from an in-country balance to a foreign bank account is entirely the Landlord's responsibility.</p>
  </div>
</section>

<section class="legal-section" id="landlord-obligations">
  <h2>4. Landlord obligations</h2>
  <p>As a Landlord, you agree to:</p>
  <ul>
    <li>Ensure you have legal authority to rent the properties you add to the Platform</li>
    <li>Comply with all applicable landlord-tenant laws in the jurisdictions where your properties are located</li>
    <li>Ensure tenants are made aware of and consent to payment collection via the Platform</li>
    <li>Comply with all applicable tax obligations in each market, including non-resident landlord filing requirements</li>
    <li>Handle fund transfers in compliance with applicable regulations</li>
    <li>Not use the Platform to collect payments for properties you do not own or have authority to rent</li>
  </ul>
</section>

<section class="legal-section" id="tenant-obligations">
  <h2>5. Tenant obligations</h2>
  <p>As a Tenant, you agree to:</p>
  <ul>
    <li>Maintain a valid payment mandate for the duration of your tenancy</li>
    <li>Ensure sufficient funds are available on each collection date</li>
    <li>Not attempt to cancel or reverse payments improperly</li>
    <li>Notify your Landlord and update your payment details promptly if your bank account changes</li>
  </ul>
</section>

<section class="legal-section" id="payments">
  <h2>6. Payments and fees</h2>
  <h3>Platform fees</h3>
  <p>The first month of your first property is free. After that, platform fees are charged per unit per month as set out on our <a href="{{ url('/pricing') }}">pricing page</a>. Fees are billed monthly and are non-refundable except where required by law.</p>
  <h3>Payment processing fees</h3>
  <p>Local payment processing fees are charged by the applicable Payment Provider and passed through to the Landlord at cost. These fees are displayed before each collection and may vary by country and payment method.</p>
  <h3>Failed payments</h3>
  <p>If a rent payment fails, we will retry once after 3 days. We do not guarantee collection and are not liable for failed or disputed payments between Landlords and Tenants.</p>
  <h3>Refunds</h3>
  <p>We do not process refunds of rent payments. Any disputes about rent amounts between Landlords and Tenants are resolved directly between those parties.</p>
</section>

<section class="legal-section" id="data-ownership">
  <h2>7. Data ownership</h2>
  <p>You retain ownership of all data you upload to the Platform, including lease documents, photos, and messages. By uploading data, you grant us a limited licence to store, process, and display it for the purpose of delivering the Platform's features.</p>
  <p>You may export your data at any time. Upon account deletion, we will provide a data export within 30 days. Certain data may be retained beyond this period to comply with legal obligations (see our <a href="{{ url('/privacy') }}">Privacy Policy</a>).</p>
</section>

<section class="legal-section" id="liability">
  <h2>8. Limitation of liability</h2>
  <p>To the maximum extent permitted by applicable law, {{ config('app.name') }} is not liable for:</p>
  <ul>
    <li>Failed, delayed, or disputed rent payments between Landlords and Tenants</li>
    <li>Tax obligations, penalties, or filing requirements in any jurisdiction</li>
    <li>Loss arising from non-compliance with local landlord-tenant laws</li>
    <li>Currency fluctuations or FX losses</li>
    <li>Loss of data due to circumstances beyond our reasonable control</li>
    <li>Indirect, consequential, or incidental damages of any kind</li>
  </ul>
  <p>Our total liability to you in connection with the Platform shall not exceed the fees you paid to us in the 12 months preceding the claim.</p>
</section>

<section class="legal-section" id="prohibited">
  <h2>9. Prohibited conduct</h2>
  <p>You may not use the Platform to:</p>
  <ul>
    <li>Collect payments for properties you do not own or have authority over</li>
    <li>Engage in money laundering, fraud, or any illegal financial activity</li>
    <li>Impersonate another person or entity</li>
    <li>Attempt to reverse-engineer, scrape, or exploit the Platform's systems</li>
    <li>Transmit malicious code or interfere with other users' access</li>
    <li>Violate any applicable law or regulation in any jurisdiction</li>
  </ul>
  <p>Violation of these provisions may result in immediate account termination and may be reported to relevant authorities.</p>
</section>

<section class="legal-section" id="termination">
  <h2>10. Termination</h2>
  <p>You may close your account at any time from your account settings. Active lease payment mandates should be cancelled before closing your account.</p>
  <p>We may suspend or terminate your account if you breach these terms, engage in prohibited conduct, or if required by applicable law or a Payment Provider.</p>
  <p>Upon termination, you will retain access to your data for 90 days, after which it will be deleted subject to legal retention requirements.</p>
</section>

<section class="legal-section" id="disputes">
  <h2>11. Disputes and governing law</h2>
  <p>These terms are governed by the laws of the State of Delaware, United States, without regard to conflict of law principles.</p>
  <p>Any disputes arising from these terms or your use of the Platform shall first be attempted to be resolved through good-faith negotiation. If unresolved, disputes shall be submitted to binding arbitration in Delaware, except where prohibited by applicable law.</p>
  <p>Nothing in these terms limits your rights under applicable consumer protection laws in your country of residence.</p>
</section>

<section class="legal-section" id="changes">
  <h2>12. Changes to these terms</h2>
  <p>We may update these terms from time to time. We will notify registered users by email at least 14 days before material changes take effect. Continued use of the Platform after the effective date constitutes acceptance of the updated terms.</p>
  <p>If you do not agree to updated terms, you may close your account before the effective date.</p>
</section>

@endsection
