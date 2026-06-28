@php
  $paymentMethodLabel = $defaultMethod?->displaySummary() ?? '—';
  $billingAddress = $defaultMethod?->formattedBillingAddress($user) ?? '—';
  if ($billingAddress === '—') {
    $billingAddress = $user->formattedIdAddress();
  }
@endphp

<div class="db-grid-2" style="margin-bottom:20px">
  <div class="db-card">
    <div class="db-card-header"><span class="db-card-title">Amount due</span></div>
    <div class="db-card-body">
      <p style="font-size:28px;font-weight:600;color:var(--text-dark);margin:0 0 8px">
        {{ number_format($amountDueMinor / 100, 2) }} {{ $amountDueCurrency }}
      </p>
      <p style="margin:0;font-size:15px;color:var(--text-mid)">
        <strong>Due date:</strong> {{ $dueDate?->format('l, d M Y') ?? '—' }}
      </p>
    </div>
  </div>
  <div class="db-card">
    <div class="db-card-header"><span class="db-card-title">Payment method</span></div>
    <div class="db-card-body">
      <p style="margin:0;font-size:15px;color:var(--text-dark)">{{ $paymentMethodLabel }}</p>
    </div>
  </div>
</div>

<div class="db-card" style="margin-bottom:20px">
  <div class="db-card-header"><span class="db-card-title">Billing address</span></div>
  <div class="db-card-body">
    <p style="margin:0;font-size:15px;line-height:1.5;color:var(--text-dark)">{{ $billingAddress }}</p>
  </div>
</div>

<div class="db-card">
  <div class="db-card-body" style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:16px">
    <div>
      <p style="margin:0;font-size:15px;font-weight:500;color:var(--text-dark)">Pay {{ number_format($amountDueMinor / 100, 2) }} {{ $amountDueCurrency }}</p>
      <p style="margin:6px 0 0;font-size:13px;color:var(--text-light)">Due {{ $dueDate?->format('d M Y') }}</p>
    </div>
    <form method="POST" action="{{ route('tenant.payments.complete') }}">
      @csrf
      <button type="submit" class="db-form-submit" @disabled(! $canPay)>Complete payment</button>
    </form>
  </div>
</div>
