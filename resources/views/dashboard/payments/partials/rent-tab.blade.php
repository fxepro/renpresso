@php
  $homeCurrency = strtoupper(auth()->user()->home_currency ?? 'USD');
  $homeSym = \App\Support\CurrencyDisplay::symbol($homeCurrency);
@endphp
<div class="db-stats" style="margin-bottom:20px">
  <div class="db-stat green">
    <div class="db-stat-label">This month ({{ $homeCurrency }} ledger)</div>
    <div class="db-stat-value">{{ $homeSym }}{{ number_format($thisMonth/100, \App\Support\CurrencyDisplay::decimalPlaces($homeCurrency)) }}</div>
    <div class="db-stat-sub">{{ now()->format('M Y') }}</div>
  </div>
  <div class="db-stat">
    <div class="db-stat-label">This year</div>
    <div class="db-stat-value">{{ $homeSym }}{{ number_format($thisYear/100, \App\Support\CurrencyDisplay::decimalPlaces($homeCurrency)) }}</div>
    <div class="db-stat-sub">{{ now()->year }}</div>
  </div>
  <div class="db-stat terra">
    <div class="db-stat-label">Pending</div>
    <div class="db-stat-value">{{ $pending }}</div>
    <div class="db-stat-sub">Awaiting collection</div>
  </div>
  <div class="db-stat">
    <div class="db-stat-label">Failed</div>
    <div class="db-stat-value">{{ $failed }}</div>
    <div class="db-stat-sub">Require attention</div>
  </div>
</div>

<div class="db-table-wrap">
  <table class="db-table">
    <thead><tr><th>Property</th><th>Tenant</th><th>Due</th><th>Collected (local)</th><th>{{ $homeCurrency }} ledger</th><th>Collected</th><th>Status</th></tr></thead>
    <tbody>
      @forelse($rentPayments as $pay)
      <tr>
        <td>
          <div class="db-flag-name">
            <span class="db-flag">{{ config('countries.'.$pay->lease->property->country_code.'.flag','🏠') }}</span>
            <div><div class="db-name">{{ $pay->lease->property->name }}</div><div class="db-sub">{{ $pay->lease->property->city }}</div></div>
          </div>
        </td>
        <td>{{ $pay->lease->tenant->first_name ?? '—' }}</td>
        <td>{{ $pay->due_date?->format('d M Y') }}</td>
        <td><strong>{{ \App\Support\CurrencyDisplay::formatMinor($pay->amount_minor_units, $pay->currency_code) }}</strong></td>
        <td>{{ \App\Support\CurrencyDisplay::formatMinor($pay->home_amount_minor_units, $pay->home_currency_code) }}</td>
        <td>{{ $pay->collected_at?->format('d M Y') ?? '—' }}</td>
        <td><span class="badge badge-{{ $pay->status==='success'?'green':($pay->status==='failed'?'red':'gold') }}">{{ ucfirst($pay->status) }}</span></td>
      </tr>
      @empty
      <tr><td colspan="7" style="text-align:center;padding:32px;color:var(--text-light)">No rent payments yet.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
@if($rentPayments->hasPages())
<div style="padding:16px 0;border-top:1px solid var(--cream-dark);margin-top:16px">
  {{ $rentPayments->links() }}
</div>
@endif
