@php
  $typeTitles = [
    'card'   => 'Credit / debit card',
    'ach'    => 'Bank account (ACH)',
    'paypal' => 'PayPal',
    'crypto' => 'Cryptocurrency',
    'other'  => 'Other',
  ];
  $activeTitle = $typeTitles[$paymentType] ?? 'Payment method';
  $defaultMethod = $paymentMethods->firstWhere('is_default', true);
  $counts = $paymentMethods->groupBy('method_type')->map->count();
  $paymentTabQuery = ['tab' => 'payment'];
  if (request('pm')) {
    $paymentTabQuery['pm'] = request('pm');
  }
  if (request()->filled('edit')) {
    $paymentTabQuery['edit'] = request('edit');
  }
@endphp

@if($defaultMethod)
<div class="db-card" style="margin-bottom:20px">
  <div class="db-card-body">
    <p class="rm-acc-status-label">Default</p>
    <p class="rm-acc-status-value">
      {{ $defaultMethod->displaySummary() }}
      <span class="badge badge-green" style="margin-left:6px">{{ ucfirst($defaultMethod->method_type) }}</span>
    </p>
  </div>
</div>
@endif

<nav class="rm-pm-tabs" aria-label="Payment method types" style="margin-bottom:18px">
  <div style="display:flex;flex-wrap:wrap;background:var(--cream-dark);border-radius:8px;padding:3px;gap:2px">
    @foreach($paymentTypeOptions as $typeKey => $typeLabel)
      @php $count = $counts[$typeKey] ?? 0; @endphp
      <a href="{{ route('landlord.account', ['tab' => 'payment', 'pm' => $typeKey]) }}"
         class="portfolio-tab {{ $paymentType === $typeKey ? 'active' : '' }}">
        {{ $typeLabel }}@if($count > 0)<span class="rm-pm-tab-count">{{ $count }}</span>@endif
      </a>
    @endforeach
  </div>
</nav>

@include('dashboard.landlord.account.partials.payment-type-section', [
  'type' => $paymentType,
  'title' => $activeTitle,
  'methods' => $paymentMethods,
  'editingMethod' => $editingMethod,
  'user' => $user,
])
