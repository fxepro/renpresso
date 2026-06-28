@extends('dashboard.layout')
@section('page-title', 'Payments')
@section('breadcrumb', 'Finance')
@section('content')
@php
  $awaitingCount = $awaitingApprovalCount;
@endphp

<div class="db-card">
  <nav class="mt-tabs" aria-label="Payment types" style="display:flex;gap:4px;padding:0 24px;border-bottom:1px solid var(--cream-dark);background:var(--cream)">
    <a href="{{ route('payments.index', ['tab' => 'rent']) }}" class="mt-tab {{ $tab === 'rent' ? 'active' : '' }}" style="padding:14px 18px;font-weight:500;color:{{ $tab === 'rent' ? 'var(--terra)' : 'var(--text-light)' }};text-decoration:none;border-bottom:2px solid {{ $tab === 'rent' ? 'var(--terra)' : 'transparent' }};margin-bottom:-1px">Rent</a>
    <a href="{{ route('payments.index', ['tab' => 'maintenance']) }}" class="mt-tab {{ $tab === 'maintenance' ? 'active' : '' }}" style="padding:14px 18px;font-weight:500;color:{{ $tab === 'maintenance' ? 'var(--terra)' : 'var(--text-light)' }};text-decoration:none;border-bottom:2px solid {{ $tab === 'maintenance' ? 'var(--terra)' : 'transparent' }};margin-bottom:-1px">
      Maintenance
      @if($awaitingCount > 0)<span class="db-nav-badge" style="margin-left:6px">{{ $awaitingCount }}</span>@endif
    </a>
  </nav>

  <div class="db-card-body">
    @if($tab === 'rent')
      @include('dashboard.payments.partials.rent-tab')
    @else
      @include('dashboard.payments.partials.maintenance-tab')
    @endif
  </div>
</div>
@endsection

@push('styles')
<style>
.mt-tab:hover { color: var(--text-dark); }
</style>
@endpush
