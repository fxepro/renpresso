@extends('dashboard.maintenance-portal.layout')
@section('page-title', 'Account')
@push('styles')
@include('partials.rm-account-ui')
@endpush
@section('content')
@php
  $primaryTabs = [
    'profile'    => 'Profile',
    'services'   => 'Services',
    'company'    => 'Professional company',
    'financials' => 'Financials',
    'trade'      => 'Trade & insurance',
    'reviews'    => 'Reviews',
  ];
  $tabCounts = [
    'reviews' => $team?->reviewCount() ?? 0,
  ];
@endphp

@if($team && $compliance && ! $compliance['complete'])
  <div class="db-alert" style="margin-bottom:16px;background:var(--gold-pale);color:var(--gold);border:1px solid rgba(201,150,58,0.25)">
    <strong>{{ $team->name }}</strong> — compliance uploads {{ $compliance['uploaded'] }}/{{ $compliance['required_total'] }} required.
    Finish documents so engaged landlords can assign work.
  </div>
@endif

<div style="display:flex;align-items:center;flex-wrap:wrap;gap:12px;margin-bottom:20px">
  <nav style="display:flex;flex-wrap:wrap;background:var(--cream-dark);border-radius:8px;padding:3px;gap:2px" aria-label="Account sections">
    @foreach($primaryTabs as $key => $label)
      @php $count = $tabCounts[$key] ?? 0; @endphp
      <a href="{{ route('maint.account', ['tab' => $key]) }}" class="portfolio-tab {{ $tab === $key ? 'active' : '' }}">
        {{ $label }}@if($count > 0)<span class="rm-pm-tab-count">{{ $count }}</span>@endif
      </a>
    @endforeach
  </nav>
</div>

@if($tab === 'profile')
  @include('dashboard.maintenance-portal.account.profile')
@elseif($tab === 'services')
  @include('dashboard.maintenance-portal.account.services')
@elseif($tab === 'company')
  @include('dashboard.maintenance-portal.account.company')
@elseif($tab === 'financials')
  @include('dashboard.maintenance-portal.account.financials')
@elseif($tab === 'trade')
  @include('dashboard.maintenance-portal.account.trade')
@else
  @include('dashboard.maintenance-portal.account.reviews')
@endif
@endsection
