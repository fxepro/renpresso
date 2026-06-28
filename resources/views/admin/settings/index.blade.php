@extends('admin.layout')
@section('title', 'Settings')
@section('page-title', 'Settings')
@section('breadcrumb', 'Platform configuration')
@section('content')
<p class="admin-portal-note">Configure how {{ config('app.name') }} gets paid: <strong>payment processors</strong> (credentials stay in <code>.env</code>), <strong>per-country unit pricing</strong> ($10 signup + $9/mo per unit — adjust for PPP), and <strong>5% maintenance commission</strong> on invoices paid through the platform (rent % later). Billing logic will read these tables at go-live.</p>

<div class="db-grid-3">
  <a href="{{ route('admin.settings.general') }}" class="prop-card">
    <div class="prop-card-top"><span class="prop-card-flag">🌐</span></div>
    <div class="prop-card-name">General</div>
    <div class="prop-card-addr">Reporting currency {{ $settings->reporting_currency }}, defaults for new markets, free month on first property.</div>
  </a>
  <a href="{{ route('admin.settings.payments') }}" class="prop-card">
    <div class="prop-card-top"><span class="prop-card-flag">💳</span></div>
    <div class="prop-card-name">Payments</div>
    <div class="prop-card-addr">ACH, cards, PayPal, crypto → processor &amp; who pays fees. {{ $providerCount }} processors in .env registry.</div>
  </a>
  <a href="{{ route('admin.settings.markets') }}" class="prop-card">
    <div class="prop-card-top"><span class="prop-card-flag">🌍</span></div>
    <div class="prop-card-name">Markets &amp; pricing</div>
    <div class="prop-card-addr">Per-country signup fee, monthly fee per unit, maintenance commission. {{ $marketCount }} active markets.</div>
  </a>
</div>
@endsection
