@extends('admin.layout')
@section('title', $period->format('F Y') . ' — Rent collections')
@section('page-title', $period->format('F Y') . ' — Rent collections')
@section('breadcrumb', 'Finance')

@push('styles')
<style>
.pay-status {
  display: inline-flex; font-size: 11px; font-weight: 600;
  padding: 2px 9px; border-radius: 20px; white-space: nowrap;
}
.pay-status.success { background: #e8f5e9; color: #2e7d32; }
.pay-status.failed  { background: #fce4ec; color: #b71c1c; }
.pay-status.pending { background: #fff8e1; color: #f57f17; }
.t-name { font-weight: 600; color: var(--text-dark); text-decoration: none; }
.t-name:hover { color: var(--terra); }
.t-sub  { font-size: 12px; color: var(--text-light); margin-top: 1px; }
.db-table tbody tr:hover td { background: var(--cream); }
.retry-dot {
  display: inline-block; width: 7px; height: 7px; border-radius: 50%; background: #ff9800;
  vertical-align: middle; margin-left: 2px;
}
.ref-code {
  font-size: 11px; font-family: monospace; color: var(--text-light);
  background: var(--cream); padding: 1px 6px; border-radius: 4px;
}
</style>
@endpush

@section('content')

<div style="margin-bottom:16px">
  <a href="{{ route('admin.rent-collections') }}" class="db-btn db-btn-ghost" style="text-decoration:none">← All periods</a>
</div>

<p class="admin-portal-note">
  All rent payments with a due date in <strong>{{ $period->format('F Y') }}</strong>.
  Collected amounts shown in each lease's billing currency and (where available) in the landlord's home currency.
</p>

{{-- ── Stats ──────────────────────────────────────────────────────────────── --}}
<div class="db-stats">
  <div class="db-stat terra">
    <div class="db-stat-label">Period</div>
    <div class="db-stat-value" style="font-size:22px">{{ $period->format('M Y') }}</div>
    <div class="db-stat-sub">{{ $payments->count() }} payment {{ Str::plural('record', $payments->count()) }}</div>
  </div>
  <div class="db-stat green">
    <div class="db-stat-label">Collected</div>
    <div class="db-stat-value">{{ $summary['success'] }}</div>
    <div class="db-stat-sub">
      {{ $summary['home_currency'] }}
      {{ number_format($summary['home_gmv'] / 100, 0) }} GMV
    </div>
  </div>
  <div class="db-stat {{ $summary['failed'] > 0 ? '' : 'green' }}">
    <div class="db-stat-label">Failed</div>
    <div class="db-stat-value" style="{{ $summary['failed'] > 0 ? 'color:#b71c1c' : '' }}">
      {{ $summary['failed'] }}
    </div>
    <div class="db-stat-sub">Requires follow-up</div>
  </div>
  <div class="db-stat {{ $summary['pending'] > 0 ? '' : 'green' }}">
    <div class="db-stat-label">Pending</div>
    <div class="db-stat-value" style="{{ $summary['pending'] > 0 ? 'color:#f57f17' : '' }}">
      {{ $summary['pending'] }}
    </div>
    <div class="db-stat-sub">Awaiting collection</div>
  </div>
</div>

{{-- ── Payment table ────────────────────────────────────────────────────────── --}}
<div class="db-card">
  <div class="db-card-header">
    <span class="db-card-title">Payment ledger — {{ $period->format('F Y') }}</span>
    <span class="db-card-sub">{{ $payments->count() }} {{ Str::plural('entry', $payments->count()) }}</span>
  </div>

  @if($payments->isEmpty())
    <div class="db-card-body" style="text-align:center;padding:48px 20px;color:var(--text-light)">
      <div style="font-size:36px;margin-bottom:12px">📭</div>
      <div style="font-weight:600;color:var(--text-dark);margin-bottom:6px">No payments for this period</div>
      <div style="font-size:13px">There are no rent payments due in {{ $period->format('F Y') }}.</div>
    </div>
  @else
    <div class="db-card-body" style="padding:0">
      <table class="db-table">
        <thead>
          <tr>
            <th>Tenant</th>
            <th>Property</th>
            <th>Landlord</th>
            <th>Due date</th>
            <th style="text-align:right">Amount</th>
            <th style="text-align:right">Home equiv.</th>
            <th style="text-align:center">Status</th>
            <th>Collected</th>
            <th>Retries</th>
            <th>Ref</th>
          </tr>
        </thead>
        <tbody>
          @foreach($payments as $payment)
            @php
              $lease    = $payment->lease;
              $property = $lease?->property;
              $tenant   = $lease?->tenant;
              $landlord = $property?->landlord;
              $propUrl  = $property ? route('admin.properties.show', $property) : null;
              $llUrl    = $landlord ? route('admin.landlords.show', $landlord) : null;
              $leaseUrl = $lease    ? route('admin.leases.show', $lease)       : null;
            @endphp
            <tr>
              <td>
                @if($tenant)
                  <div class="t-name">{{ $tenant->first_name }} {{ $tenant->last_name }}</div>
                @else
                  <span style="color:var(--text-light);font-size:13px">—</span>
                @endif
              </td>
              <td>
                @if($property)
                  <a href="{{ $propUrl }}" class="t-name">{{ $property->name }}</a>
                  <div class="t-sub">{{ $property->city }}, {{ $property->country_code }}</div>
                @else
                  <span style="color:var(--text-light);font-size:13px">—</span>
                @endif
              </td>
              <td>
                @if($landlord)
                  <a href="{{ $llUrl }}" class="t-name">
                    {{ $landlord->first_name }} {{ $landlord->last_name }}
                  </a>
                @else
                  <span style="color:var(--text-light);font-size:13px">—</span>
                @endif
              </td>
              <td style="white-space:nowrap;color:var(--text-mid)">
                {{ \Carbon\Carbon::parse($payment->due_date)->format('d M Y') }}
              </td>
              <td style="text-align:right;font-weight:600;white-space:nowrap">
                {{ strtoupper($payment->currency_code) }}
                {{ number_format($payment->amount_minor_units / 100, 0) }}
              </td>
              <td style="text-align:right;color:var(--text-mid);white-space:nowrap">
                @if($payment->home_amount_minor_units && $payment->home_currency_code)
                  {{ strtoupper($payment->home_currency_code) }}
                  {{ number_format($payment->home_amount_minor_units / 100, 0) }}
                @else
                  <span style="color:var(--text-light)">—</span>
                @endif
              </td>
              <td style="text-align:center">
                <span class="pay-status {{ $payment->status }}">
                  {{ ucfirst($payment->status) }}
                </span>
              </td>
              <td style="white-space:nowrap;color:var(--text-mid);font-size:13px">
                @if($payment->collected_at)
                  {{ \Carbon\Carbon::parse($payment->collected_at)->format('d M H:i') }}
                @else
                  <span style="color:var(--text-light)">—</span>
                @endif
              </td>
              <td style="text-align:center;color:var(--text-mid)">
                @if($payment->retry_count > 0)
                  <span title="{{ $payment->retry_count }} retr{{ $payment->retry_count === 1 ? 'y' : 'ies' }}">
                    {{ $payment->retry_count }}
                    <span class="retry-dot"></span>
                  </span>
                @else
                  <span style="color:var(--text-light);font-size:12px">—</span>
                @endif
              </td>
              <td>
                @if($payment->processor_ref)
                  <span class="ref-code">{{ Str::limit($payment->processor_ref, 18) }}</span>
                @else
                  <span style="color:var(--text-light);font-size:12px">—</span>
                @endif
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    {{-- ── Totals footer ────────────────────────────────────────────────────── --}}
    <div style="padding:14px 20px;border-top:1px solid var(--cream-dark);display:flex;gap:24px;align-items:center;flex-wrap:wrap">
      <div style="font-size:12px;color:var(--text-light)">Period totals</div>
      @php
        $currencies = $payments->where('status','success')->groupBy('currency_code');
      @endphp
      @foreach($currencies as $cur => $group)
        <div style="font-size:13px">
          <span style="color:var(--text-light)">{{ strtoupper($cur) }}</span>
          <strong style="margin-left:4px">{{ number_format($group->sum('amount_minor_units') / 100, 0) }}</strong>
        </div>
      @endforeach
      @if($summary['home_gmv'] > 0)
        <div style="font-size:13px;margin-left:auto;font-weight:600;color:var(--text-dark)">
          Home total:
          {{ strtoupper($summary['home_currency']) }}
          {{ number_format($summary['home_gmv'] / 100, 2) }}
        </div>
      @endif
    </div>

  @endif
</div>

@endsection
