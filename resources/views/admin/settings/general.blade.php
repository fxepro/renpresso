@extends('admin.layout')
@section('title', 'General settings')
@section('page-title', 'General settings')
@section('breadcrumb', 'Settings')
@section('content')
@php
  $bill = $settings->default_billing_currency;
@endphp
<p class="admin-portal-note">Platform-wide defaults. New countries inherit these until you override them under <a href="{{ route('admin.settings.markets') }}" class="db-table-link">Markets &amp; pricing</a>.</p>

@include('admin.partials.amount-note')

<div class="db-card">
  <div class="db-card-header"><span class="db-card-title">Defaults</span></div>
  <div class="db-card-body">
    <form method="POST" action="{{ route('admin.settings.general.update') }}" class="db-form">
      @csrf
      @method('PUT')
      <div class="db-form-row">
        <div class="db-form-group">
          <label>Reporting currency (admin revenue)</label>
          <input type="text" name="reporting_currency" class="db-input" maxlength="3" required value="{{ old('reporting_currency', $settings->reporting_currency) }}">
        </div>
        <div class="db-form-group">
          <label>Default billing currency</label>
          <input type="text" name="default_billing_currency" class="db-input" maxlength="3" required value="{{ old('default_billing_currency', $settings->default_billing_currency) }}">
          <span class="db-form-hint">Used for default fee amounts below.</span>
        </div>
      </div>
      <div class="db-form-group">
        <label>First property free (months)</label>
        <input type="number" name="first_property_free_months" class="db-input" min="0" max="12" required value="{{ old('first_property_free_months', $settings->first_property_free_months) }}" style="max-width:120px">
        <span class="db-form-hint">Marketing: first month free on first property.</span>
      </div>
      <div class="db-form-row">
        <div class="db-form-group">
          <label>Default signup fee per unit ({{ $bill }})</label>
          <input type="number" name="signup_fee" class="db-input" min="0" step="0.01" required value="{{ old('signup_fee', number_format($settings->default_signup_fee_minor_per_unit / 100, 2, '.', '')) }}">
        </div>
        <div class="db-form-group">
          <label>Default monthly fee per unit ({{ $bill }})</label>
          <input type="number" name="monthly_fee" class="db-input" min="0" step="0.01" required value="{{ old('monthly_fee', number_format($settings->default_monthly_fee_minor_per_unit / 100, 2, '.', '')) }}">
        </div>
      </div>
      <div class="db-form-group">
        <label>Default maintenance commission (%)</label>
        <input type="number" name="maintenance_commission_percent" class="db-input" min="0" max="100" step="0.01" required value="{{ old('maintenance_commission_percent', number_format($settings->default_maintenance_commission_bps / 100, 2, '.', '')) }}" style="max-width:120px">
        <span class="db-form-hint">Percent of each maintenance invoice paid through the platform (default 5%).</span>
      </div>
      <button type="submit" class="db-form-submit">Save</button>
    </form>
  </div>
</div>
<p style="margin-top:16px"><a href="{{ route('admin.settings.index') }}" class="db-table-link">← All settings</a></p>
@endsection
