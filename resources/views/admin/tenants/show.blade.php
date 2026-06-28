@extends('admin.layout')
@section('title', $user->first_name.' '.$user->last_name.' — Tenant')
@section('page-title', $user->first_name.' '.$user->last_name)
@section('breadcrumb', 'Tenants')

@section('topbar-actions')
  <a href="{{ route('admin.tenants') }}" class="db-btn db-btn-ghost" style="text-decoration:none">← All tenants</a>
  <a href="mailto:{{ $user->email }}" class="db-btn db-btn-ghost" style="text-decoration:none">Email tenant</a>
  @if($activeLease?->property?->landlord)
    <a href="{{ route('admin.landlords.show', $activeLease->property->landlord) }}"
       class="db-btn db-btn-ghost" style="text-decoration:none">Landlord</a>
  @endif
@endsection

@push('styles')
<style>
.info-row {
  display: flex; align-items: baseline; gap: 12px;
  padding: 10px 24px; border-bottom: 1px solid var(--cream-dark); font-size: 14px;
}
.info-row:last-child { border-bottom: none; }
.info-label {
  font-size: 11px; font-weight: 700; letter-spacing: .07em;
  text-transform: uppercase; color: var(--text-light);
  min-width: 120px; flex-shrink: 0;
}
.info-val { color: var(--text-dark); font-weight: 500; word-break: break-all; }
.lease-badge {
  display: inline-flex; font-size: 12px; font-weight: 600;
  padding: 3px 10px; border-radius: 20px;
}
.lease-badge.active     { background: #e8f5e9; color: #2e7d32; }
.lease-badge.expired    { background: #fff8e1; color: #f57f17; }
.lease-badge.terminated { background: #fce4ec; color: #b71c1c; }
.lease-badge.no-lease   { background: #fce4ec; color: #b71c1c; }
.pay-badge {
  display: inline-flex; font-size: 11px; font-weight: 600;
  padding: 2px 9px; border-radius: 20px;
}
.pay-badge.success { background: #e8f5e9; color: #2e7d32; }
.pay-badge.failed  { background: #fce4ec; color: #b71c1c; }
.pay-badge.pending { background: #fff8e1; color: #f57f17; }
.ref-code {
  font-family: monospace; font-size: 11px; color: var(--text-light);
  background: var(--cream); padding: 2px 6px; border-radius: 4px;
}
.hist-lease-label {
  font-size: 11px; font-weight: 700; letter-spacing: .06em;
  text-transform: uppercase; color: var(--text-light);
  padding: 10px 16px 4px; display: block;
}
</style>
@endpush

@section('content')

@php
  $hasLease = (bool) $activeLease;
  $tenure   = $activeLease?->activated_at
    ? \Carbon\Carbon::parse($activeLease->activated_at)->diffForHumans(now(), \Carbon\CarbonInterface::DIFF_ABSOLUTE)
    : null;
@endphp

{{-- Stats row --}}
<div class="db-stats">
  <div class="db-stat {{ $hasLease ? 'green' : '' }}">
    <div class="db-stat-label">Lease status</div>
    <div class="db-stat-value" style="margin-top:4px;font-size:20px">
      <span class="lease-badge {{ $hasLease ? 'active' : 'no-lease' }}">
        {{ $hasLease ? 'Active' : 'No active lease' }}
      </span>
    </div>
    <div class="db-stat-sub">{{ $tenure ? 'Tenant for '.$tenure : 'Not currently renting' }}</div>
  </div>
  <div class="db-stat">
    <div class="db-stat-label">Monthly rent</div>
    <div class="db-stat-value" style="font-size:var(--fs-heading)">
      @if($activeLease)
        {{ number_format($activeLease->rent_minor_units / 100, 2) }}
      @else
        —
      @endif
    </div>
    <div class="db-stat-sub">
      @if($activeLease)
        {{ strtoupper($activeLease->currency_code) }} · due day {{ $activeLease->due_day }}
      @else
        No active lease
      @endif
    </div>
  </div>
  <div class="db-stat {{ $paymentStats['failed'] > 0 ? '' : ($paymentStats['success'] > 0 ? 'green' : '') }}">
    <div class="db-stat-label">Payments</div>
    <div class="db-stat-value" style="{{ $paymentStats['failed'] > 0 ? 'color:#b71c1c' : '' }}">
      {{ $paymentStats['success'] }} / {{ $allPayments->count() }}
    </div>
    <div class="db-stat-sub">
      @if($paymentStats['failed'] > 0)
        <span style="color:#b71c1c">{{ $paymentStats['failed'] }} failed</span>
      @elseif($paymentStats['pending'] > 0)
        <span style="color:#f57f17">{{ $paymentStats['pending'] }} pending</span>
      @elseif($allPayments->count() > 0)
        All collected
      @else
        None yet
      @endif
    </div>
  </div>
  <div class="db-stat">
    <div class="db-stat-label">Total paid</div>
    <div class="db-stat-value" style="font-size:var(--fs-heading)">
      @if($paymentStats['total_collected_minor'] > 0)
        {{ number_format($paymentStats['total_collected_minor'] / 100, 2) }}
      @else
        —
      @endif
    </div>
    <div class="db-stat-sub">
      {{ $paymentStats['currency'] ? strtoupper($paymentStats['currency']).' · successful only' : 'No payments yet' }}
    </div>
  </div>
</div>

{{-- Profile + Current lease + Landlord --}}
<div style="display:grid;grid-template-columns:1fr 1.4fr 1fr;gap:18px;margin-bottom:20px">

  {{-- Tenant profile --}}
  <div class="db-card" style="margin:0">
    <div class="db-card-header"><span class="db-card-title">Profile</span></div>
    <div class="db-card-body" style="padding:0">
      @foreach([
        ['Name',     $user->first_name.' '.$user->last_name],
        ['Email',    $user->email],
        ['Phone',    $user->phone ?? '—'],
        ['Joined',   $user->created_at?->format('d M Y') ?? '—'],
        ['Leases',   $user->leases->count().' total'],
      ] as [$lbl, $val])
      <div class="info-row">
        <span class="info-label">{{ $lbl }}</span>
        <span class="info-val">{{ $val }}</span>
      </div>
      @endforeach
    </div>
  </div>

  {{-- Active lease --}}
  <div class="db-card" style="margin:0">
    <div class="db-card-header"><span class="db-card-title">Current lease</span></div>
    <div class="db-card-body" style="padding:0">
      @if($activeLease)
      @foreach([
        ['Property',    $activeLease->property?->name ?? '—'],
        ['Address',     ($activeLease->property?->address_line1 ?? '').($activeLease->property?->city ? ', '.$activeLease->property->city : '')],
        ['Country',     strtoupper($activeLease->property?->country_code ?? '—')],
        ['Rent',        strtoupper($activeLease->currency_code).' '.number_format($activeLease->rent_minor_units/100,2).'/mo'],
        ['Due day',     'Day '.$activeLease->due_day],
        ['Grace period',$activeLease->grace_period_days.' days'],
        ['Activated',   $activeLease->activated_at ? \Carbon\Carbon::parse($activeLease->activated_at)->format('d M Y') : '—'],
        ['End date',    $activeLease->end_date ? \Carbon\Carbon::parse($activeLease->end_date)->format('d M Y') : 'Open-ended'],
      ] as [$lbl, $val])
      <div class="info-row">
        <span class="info-label">{{ $lbl }}</span>
        <span class="info-val">{{ $val }}</span>
      </div>
      @endforeach
      <div class="info-row" style="padding-top:14px">
        <a href="{{ route('admin.leases.show', $activeLease) }}"
           class="db-btn db-btn-ghost" style="font-size:13px;text-decoration:none">
          Full lease detail →
        </a>
        @if($activeLease->property)
        <a href="{{ route('admin.properties.show', $activeLease->property) }}"
           class="db-btn db-btn-ghost" style="font-size:13px;text-decoration:none;margin-left:6px">
          Property →
        </a>
        @endif
      </div>
      @else
        <div class="info-row">
          <span style="color:#b71c1c;font-size:14px">No active lease. This tenant may need follow-up.</span>
        </div>
      @endif
    </div>
  </div>

  {{-- Landlord --}}
  <div class="db-card" style="margin:0">
    <div class="db-card-header"><span class="db-card-title">Landlord</span></div>
    <div class="db-card-body" style="padding:0">
      @if($activeLease?->property?->landlord)
      @php $ll = $activeLease->property->landlord; @endphp
      @foreach([
        ['Name',  $ll->first_name.' '.$ll->last_name],
        ['Email', $ll->email],
      ] as [$lbl, $val])
      <div class="info-row">
        <span class="info-label">{{ $lbl }}</span>
        <span class="info-val">{{ $val }}</span>
      </div>
      @endforeach
      <div class="info-row" style="padding-top:14px">
        <a href="{{ route('admin.landlords.show', $ll) }}"
           class="db-btn db-btn-ghost" style="font-size:13px;text-decoration:none">
          Landlord profile →
        </a>
      </div>
      @else
        <div class="info-row"><span style="color:var(--text-light)">—</span></div>
      @endif
    </div>
  </div>
</div>

{{-- Full payment history across all leases --}}
<div class="db-card">
  <div class="db-card-header">
    <span class="db-card-title">Payment history ({{ $allPayments->count() }} across {{ $user->leases->count() }} {{ Str::plural('lease', $user->leases->count()) }})</span>
  </div>
  <div class="db-card-body" style="padding:0">
    @if($allPayments->isEmpty())
      <div class="db-empty" style="padding:32px 20px">
        <div class="db-empty-icon">💳</div>
        <h3>No payment history</h3>
        <p>No payments have been processed for this tenant.</p>
      </div>
    @else
    <div class="db-table-wrap">
      <table class="db-table">
        <thead>
          <tr>
            <th>Due date</th>
            <th>Status</th>
            <th style="text-align:right">Amount</th>
            <th style="text-align:right">Home (USD equiv)</th>
            <th>Collected</th>
            <th>Retries</th>
            <th>Processor ref</th>
          </tr>
        </thead>
        <tbody>
          @foreach($allPayments->sortByDesc('due_date') as $payment)
          <tr>
            <td style="font-weight:600;white-space:nowrap">
              {{ \Carbon\Carbon::parse($payment->due_date)->format('d M Y') }}
            </td>
            <td>
              <span class="pay-badge {{ $payment->status }}">{{ ucfirst($payment->status) }}</span>
            </td>
            <td style="text-align:right;font-weight:600">
              {{ strtoupper($payment->currency_code) }} {{ number_format($payment->amount_minor_units / 100, 2) }}
            </td>
            <td style="text-align:right;font-size:13px;color:var(--text-mid)">
              @if($payment->home_amount_minor_units && $payment->home_currency_code)
                {{ strtoupper($payment->home_currency_code) }} {{ number_format($payment->home_amount_minor_units / 100, 2) }}
              @else
                —
              @endif
            </td>
            <td style="font-size:12px;color:var(--text-mid);white-space:nowrap">
              {{ $payment->collected_at ? \Carbon\Carbon::parse($payment->collected_at)->format('d M Y') : '—' }}
            </td>
            <td style="text-align:center;font-size:13px;color:{{ $payment->retry_count > 0 ? '#f57f17' : 'var(--text-light)' }}">
              {{ $payment->retry_count > 0 ? $payment->retry_count : '—' }}
            </td>
            <td>
              @if($payment->processor_ref)
                <span class="ref-code">{{ $payment->processor_ref }}</span>
              @else
                <span style="color:var(--text-light)">—</span>
              @endif
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    @endif
  </div>
</div>

@endsection
