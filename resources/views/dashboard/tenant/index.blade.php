@extends('dashboard.layout')
@section('page-title', 'Dashboard')
@section('content')
@if(! $lease)
<div class="db-empty" style="min-height:60vh">
  <div class="db-empty-icon">🏠</div>
  <h3>No active lease</h3>
  <p>When your landlord activates your lease, your home, rent, and payments will appear here.</p>
</div>
@else
@php
  $property = $lease->property;
  $flag = config('countries.'.$property->country_code.'.flag', '🏠');
@endphp
<div class="db-alert" style="margin-bottom:20px;background:var(--cream-dark);color:var(--text-mid);border:1px solid var(--cream-dark)">
  <span style="font-size:18px;margin-right:8px">{{ $flag }}</span>
  <strong>{{ $property->name }}</strong>
  · {{ $property->address_line1 }}@if($lease->unit_label), {{ $lease->displayUnit() }}@endif
  · {{ $property->city }}
</div>
<div class="db-stats">
  <a href="{{ route('tenant.payments', ['tab' => 'current']) }}" class="db-stat {{ $nextDue ? 'terra' : 'green' }}" style="text-decoration:none;color:inherit">
    <div class="db-stat-label">Next rent due</div>
    @if($nextDue)
      <div class="db-stat-value">{{ number_format($nextDue->amount_minor_units /100,2) }} {{ $nextDue->currency_code }}</div>
      <div class="db-stat-sub">{{ $nextDue->due_date?->format('d M Y') }}</div>
    @else
      <div class="db-stat-value">—</div>
      <div class="db-stat-sub">No pending payment</div>
    @endif
  </a>
  <div class="db-stat">
    <div class="db-stat-label">Monthly rent</div>
    <div class="db-stat-value">{{ number_format($lease->rent_minor_units /100,2) }}</div>
    <div class="db-stat-sub">{{ $lease->currency_code }} · due day {{ $lease->due_day }}</div>
  </div>
  <div class="db-stat {{ $openMaintenance > 0 ? 'terra' : '' }}">
    <div class="db-stat-label">Maintenance</div>
    <div class="db-stat-value">{{ $openMaintenance }}</div>
    <div class="db-stat-sub">Open requests</div>
  </div>
  <div class="db-stat">
    <div class="db-stat-label">Messages</div>
    <div class="db-stat-value">{{ $unreadMessages }}</div>
    <div class="db-stat-sub">Unread</div>
  </div>
</div>
<div class="db-grid-2">
  <div class="db-card">
    <div class="db-card-header"><span class="db-card-title">Quick links</span></div>
    <div class="db-card-body" style="display:flex;flex-direction:column;gap:10px">
      <a href="{{ route('tenant.home') }}" class="db-btn db-btn-ghost" style="justify-content:flex-start">Lease &amp; property details</a>
      <a href="{{ route('tenant.payments', ['tab' => 'current']) }}" class="db-btn db-btn-ghost" style="justify-content:flex-start">Pay rent</a>
      <a href="{{ route('maintenance.index') }}" class="db-btn db-btn-ghost" style="justify-content:flex-start">Maintenance</a>
      <a href="{{ route('messages.index') }}" class="db-btn db-btn-ghost" style="justify-content:flex-start">Messages</a>
    </div>
  </div>
  <div class="db-card">
    <div class="db-card-header"><span class="db-card-title">Landlord</span></div>
    <div class="db-card-body">
      @if($property?->landlord)
        <p style="font-weight:500;margin-bottom:4px">{{ $property->landlord->fullName() }}</p>
        <p style="font-size:var(--fs-step);color:var(--text-light)">{{ $property->landlord->email }}</p>
      @else
        <p style="color:var(--text-light)">Contact details unavailable.</p>
      @endif
    </div>
  </div>
</div>
@endif
@endsection
