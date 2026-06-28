@extends('dashboard.layout')

@section('page-title', 'Account')

@section('breadcrumb', 'Landlord')

@push('styles')

@include('partials.rm-account-ui')

@endpush

@section('content')

@php

  $paymentTabQuery = ['tab' => 'payment'];

  if (request('pm')) {

    $paymentTabQuery['pm'] = request('pm');

  }

  if (request()->filled('edit')) {

    $paymentTabQuery['edit'] = request('edit');

  }

  $primaryTabs = [

    'identity'  => 'Identity',

    'business'  => 'Business',

    'payment'   => 'Payment',

    'banks'     => 'Banks',

    'portfolio' => 'Portfolio',

  ];

  $paymentMethodCount = isset($paymentMethods) ? $paymentMethods->count() : 0;

@endphp



@if(session('success'))

  <div class="db-alert db-alert-success" style="margin-bottom:16px">{{ session('success') }}</div>

@endif

@if(session('error'))

  <div class="db-alert db-alert-error" style="margin-bottom:16px">{{ session('error') }}</div>

@endif



<div style="display:flex;align-items:center;flex-wrap:wrap;gap:12px;margin-bottom:20px">

  <nav style="display:flex;flex-wrap:wrap;background:var(--cream-dark);border-radius:8px;padding:3px;gap:2px" aria-label="Account sections">

    @foreach($primaryTabs as $key => $label)

      <a href="{{ route('landlord.account', ['tab' => $key]) }}" class="portfolio-tab {{ $tab === $key ? 'active' : '' }}">

        {{ $label }}

        @if($key === 'payment' && $paymentMethodCount > 0)

          <span class="rm-pm-tab-count">{{ $paymentMethodCount }}</span>

        @endif

        @if($key === 'banks' && ($payoutAccountCount ?? 0) > 0)

          <span class="rm-pm-tab-count">{{ $payoutAccountCount }}</span>

        @endif

      </a>

    @endforeach

  </nav>

</div>



@if($tab === 'identity')

  @include('dashboard.landlord.account.identity')

@elseif($tab === 'business')

  @include('dashboard.landlord.account.business')

@elseif($tab === 'payment')

  @include('dashboard.landlord.account.payment')

@elseif($tab === 'banks')

  @include('dashboard.landlord.account.banks')

@else

  @include('dashboard.landlord.account.portfolio')

@endif

@endsection

