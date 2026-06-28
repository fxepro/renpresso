@extends('dashboard.layout')
@section('page-title', 'Payments')
@section('breadcrumb', 'Finance')
@section('content')

@if(session('success'))
  <div class="db-alert db-alert-success" style="margin-bottom:16px">{{ session('success') }}</div>
@endif
@if(session('error'))
  <div class="db-alert db-alert-error" style="margin-bottom:16px">{{ session('error') }}</div>
@endif

@if(! $lease)
<div class="db-empty" style="min-height:60vh">
  <div class="db-empty-icon">💳</div>
  <h3>No active lease</h3>
  <p>Rent payments and history will appear here when you have an active lease.</p>
</div>
@else

<nav class="admin-pay-tabs" aria-label="Rent payments" style="display:flex;gap:4px;margin-bottom:20px;border-bottom:1px solid var(--cream-dark)">
  <a href="{{ route('tenant.payments', ['tab' => 'current']) }}"
     class="admin-pay-tab {{ $tab === 'current' ? 'active' : '' }}">Current payment</a>
  <a href="{{ route('tenant.payments', ['tab' => 'history']) }}"
     class="admin-pay-tab {{ $tab === 'history' ? 'active' : '' }}">Payment history</a>
</nav>

@if($tab === 'current')
  @include('dashboard.tenant.payments.partials.current')
@else
  @include('dashboard.tenant.payments.partials.history')
@endif

@endif
@endsection

@push('styles')
<style>
.admin-pay-tab {
  padding: 12px 18px;
  font-size: 14px;
  font-weight: 500;
  color: var(--text-light);
  text-decoration: none;
  border-bottom: 2px solid transparent;
  margin-bottom: -1px;
}
.admin-pay-tab:hover { color: var(--text-dark); }
.admin-pay-tab.active {
  color: var(--terra);
  border-bottom-color: var(--terra);
}
</style>
@endpush
