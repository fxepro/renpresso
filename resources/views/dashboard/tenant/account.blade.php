@extends('dashboard.layout')
@section('page-title', 'Account')
@push('styles')
@include('partials.rm-account-ui')
@endpush
@section('content')
@php
  $profileActive = $tab === 'profile';
  $backgroundActive = $tab === 'background';
  $paymentActive = $tab === 'payment';
  $paymentTabQuery = ['tab' => 'payment'];
  if (request('pm')) {
    $paymentTabQuery['pm'] = request('pm');
  }
  if (request()->filled('edit')) {
    $paymentTabQuery['edit'] = request('edit');
  }
@endphp

<div style="display:flex;align-items:center;flex-wrap:wrap;gap:12px;margin-bottom:20px">
  <nav style="display:flex;background:var(--cream-dark);border-radius:8px;padding:3px;gap:2px" aria-label="Account sections">
    <a href="{{ route('tenant.account', ['tab' => 'profile']) }}" class="portfolio-tab {{ $profileActive ? 'active' : '' }}">Profile</a>
    <a href="{{ route('tenant.account', ['tab' => 'background']) }}" class="portfolio-tab {{ $backgroundActive ? 'active' : '' }}">Background</a>
    <a href="{{ route('tenant.account', $paymentTabQuery) }}" class="portfolio-tab {{ $paymentActive ? 'active' : '' }}">Payment</a>
  </nav>
</div>

@if($profileActive)
  @include('dashboard.tenant.account.profile')
@elseif($backgroundActive)
  @include('dashboard.tenant.account.background')
@else
  @include('dashboard.tenant.account.payment')
@endif
@endsection
