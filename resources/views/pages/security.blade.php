@extends('layouts.utility', ['page' => 'security'])

@section('title', 'Security')
@section('heading', 'Security at Renpresso')
@section('meta', 'Last updated: June 2026')

@section('utility')

<section class="legal-section" id="overview">
  <h2>Our approach</h2>
  <p>{{ config('app.name') }} is built for landlords who manage properties across borders. That means handling financial and personal data with care — encryption in transit and at rest, least-privilege access, and region-aware data handling where regulations require it.</p>
</section>

<section class="legal-section" id="payments">
  <h2>Payments &amp; money movement</h2>
  <p>We do not hold landlord funds. Rent is collected locally through licensed payment processors in each market. Processor credentials and webhook secrets are stored encrypted and rotated through our admin configuration layer.</p>
  <p>Cross-border repatriation is always your responsibility through your own bank — {{ config('app.name') }} logs repatriation events for your records but does not move money across borders.</p>
</section>

<section class="legal-section" id="data">
  <h2>Data protection</h2>
  <ul>
    <li>TLS for all web traffic and API calls</li>
    <li>Encrypted storage for sensitive configuration and documents</li>
    <li>Role-based access in the dashboard — landlords, tenants, and staff see only what they need</li>
    <li>7-year document retention minimum for lease and payment records</li>
    <li>Data stored in region-appropriate data centres where required by law</li>
  </ul>
</section>

<section class="legal-section" id="report">
  <h2>Report a vulnerability</h2>
  <p>If you believe you have found a security issue, please contact us at <a href="mailto:security@renpresso.com">security@renpresso.com</a> with enough detail to reproduce the problem. We respond to good-faith reports promptly.</p>
  <p>For general account or payment issues, use <a href="{{ url('/contact') }}">contact support</a> instead.</p>
</section>

@endsection
