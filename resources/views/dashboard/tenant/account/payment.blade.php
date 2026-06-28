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
@endphp

@if($defaultMethod || $mandate || $lease)
<div class="db-card" style="margin-bottom:20px">
  <div class="db-card-body" style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:14px">
    <div>
      <p class="rm-acc-status-label">Default</p>
      @if($defaultMethod)
        <p class="rm-acc-status-value">{{ $defaultMethod->displaySummary() }}
          <span class="badge badge-green" style="margin-left:6px">{{ ucfirst($defaultMethod->method_type) }}</span>
        </p>
      @else
        <p style="margin:0;font-size:15px;color:var(--text-mid)">No default saved method — pick a type below and mark one as default.</p>
      @endif
      @if($mandate)
        <p style="margin:6px 0 0;font-size:13px;color:var(--text-mid)">Lease mandate: {{ ucfirst($mandate->processor_slug) }} · {{ ucfirst($mandate->status) }}</p>
      @endif
    </div>
    @if($lease)
      <a href="{{ route('tenant.account-ledger') }}" class="db-btn db-btn-ghost" style="text-decoration:none">Account ledger →</a>
    @endif
  </div>
</div>
@endif

<nav class="rm-pm-tabs" aria-label="Payment method types" style="margin-bottom:18px">
  <div style="display:flex;flex-wrap:wrap;background:var(--cream-dark);border-radius:8px;padding:3px;gap:2px">
    @foreach($paymentTypeOptions as $typeKey => $typeLabel)
      @php $count = $counts[$typeKey] ?? 0; @endphp
      <a href="{{ route('tenant.account', ['tab' => 'payment', 'pm' => $typeKey]) }}"
         class="portfolio-tab {{ $paymentType === $typeKey ? 'active' : '' }}">
        {{ $typeLabel }}@if($count > 0)<span class="rm-pm-tab-count">{{ $count }}</span>@endif
      </a>
    @endforeach
  </div>
</nav>

@include('dashboard.tenant.account.partials.payment-type-section', [
  'type' => $paymentType,
  'title' => $activeTitle,
  'methods' => $paymentMethods,
  'editingMethod' => $editingMethod,
  'lease' => $lease,
  'user' => $user,
])
