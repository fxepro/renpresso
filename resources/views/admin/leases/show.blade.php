@extends('admin.layout')
@section('title', ($lease->property->name ?? 'Lease').' — '.$lease->tenant?->first_name.' '.$lease->tenant?->last_name)
@section('page-title', $lease->property->name ?? 'Lease detail')
@section('breadcrumb', 'Leases')

@section('topbar-actions')
  <a href="{{ route('admin.leases') }}" class="db-btn db-btn-ghost" style="text-decoration:none">← All leases</a>
  @if($lease->property)
    <a href="{{ route('admin.properties.show', $lease->property) }}" class="db-btn db-btn-ghost" style="text-decoration:none">Property</a>
  @endif
  @if($lease->property?->landlord)
    <a href="{{ route('admin.landlords.show', $lease->property->landlord) }}" class="db-btn db-btn-ghost" style="text-decoration:none">Landlord</a>
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
  min-width: 130px; flex-shrink: 0;
}
.info-val { color: var(--text-dark); font-weight: 500; }
.lease-status-badge {
  display: inline-flex; font-size: 12px; font-weight: 600;
  padding: 3px 10px; border-radius: 20px;
}
.lease-status-badge.active     { background: #e8f5e9; color: #2e7d32; }
.lease-status-badge.expired    { background: #fff8e1; color: #f57f17; }
.lease-status-badge.terminated { background: #fce4ec; color: #b71c1c; }
.lease-status-badge.draft      { background: #f5f5f5; color: #9e9e9e; }
.pay-badge {
  display: inline-flex; font-size: 11px; font-weight: 600;
  padding: 2px 9px; border-radius: 20px;
}
.pay-badge.success { background: #e8f5e9; color: #2e7d32; }
.pay-badge.failed  { background: #fce4ec; color: #b71c1c; }
.pay-badge.pending { background: #fff8e1; color: #f57f17; }
.pay-badge.refunded{ background: #f3e5f5; color: #6a1b9a; }
.ref-code {
  font-family: monospace; font-size: 11px; color: var(--text-light);
  background: var(--cream); padding: 2px 6px; border-radius: 4px;
}
</style>
@endpush

@section('content')

@php
  $isMulti   = $lease->property?->occupancy_mode === 'multi';
  $unitLabel = $isMulti
    ? ($lease->unit_label ?: ($lease->unit_seq ? "Unit {$lease->unit_seq}" : '—'))
    : null;
@endphp

{{-- Stats row --}}
<div class="db-stats">
  <div class="db-stat {{ $lease->status === 'active' ? 'green' : '' }}">
    <div class="db-stat-label">Status</div>
    <div class="db-stat-value" style="margin-top:4px;font-size:20px">
      <span class="lease-status-badge {{ $lease->status }}">{{ ucfirst($lease->status) }}</span>
    </div>
    <div class="db-stat-sub">
      Since {{ $lease->activated_at ? \Carbon\Carbon::parse($lease->activated_at)->format('M Y') : '—' }}
    </div>
  </div>
  <div class="db-stat">
    <div class="db-stat-label">Monthly rent</div>
    <div class="db-stat-value" style="font-size:var(--fs-heading)">
      {{ number_format($lease->rent_minor_units / 100, 2) }}
    </div>
    <div class="db-stat-sub">{{ strtoupper($lease->currency_code) }} · due day {{ $lease->due_day }}</div>
  </div>
  <div class="db-stat {{ $paymentStats['failed'] > 0 ? '' : 'green' }}">
    <div class="db-stat-label">Payments collected</div>
    <div class="db-stat-value" style="{{ $paymentStats['failed'] > 0 ? 'color:#b71c1c' : '' }}">
      {{ $paymentStats['success'] }} / {{ $lease->payments->count() }}
    </div>
    <div class="db-stat-sub">
      @if($paymentStats['failed'] > 0)
        <span style="color:#b71c1c">{{ $paymentStats['failed'] }} failed</span>
        @if($paymentStats['pending'] > 0) · @endif
      @endif
      @if($paymentStats['pending'] > 0)
        <span style="color:#f57f17">{{ $paymentStats['pending'] }} pending</span>
      @endif
      @if($paymentStats['failed'] === 0 && $paymentStats['pending'] === 0)
        All clear
      @endif
    </div>
  </div>
  <div class="db-stat">
    <div class="db-stat-label">Total collected</div>
    <div class="db-stat-value" style="font-size:var(--fs-heading)">
      {{ number_format($paymentStats['total_collected_minor'] / 100, 2) }}
    </div>
    <div class="db-stat-sub">{{ strtoupper($lease->currency_code) }} · successful payments only</div>
  </div>
</div>

{{-- Three-column info cards --}}
<div style="display:grid;grid-template-columns:1.4fr 1fr 1fr;gap:18px;margin-bottom:20px">

  {{-- Lease terms --}}
  <div class="db-card" style="margin:0">
    <div class="db-card-header"><span class="db-card-title">Lease terms</span></div>
    <div class="db-card-body" style="padding:0">
      @foreach([
        ['Property',    $lease->property?->name ?? '—'],
        ['Address',     ($lease->property?->address_line1 ?? '').($lease->property?->city ? ', '.$lease->property->city : '')],
        ['Country',     strtoupper($lease->property?->country_code ?? '—')],
        ['Mode',        $isMulti ? 'Multi-unit' : 'Single-unit'],
        ['Unit',        $unitLabel ?? ($isMulti ? '—' : 'N/A')],
        ['Rent',        strtoupper($lease->currency_code).' '.number_format($lease->rent_minor_units/100,2).'/mo'],
        ['Due day',     'Day '.$lease->due_day.' of each month'],
        ['Grace period',$lease->grace_period_days.' days'],
        ['Late fee',    $lease->late_fee_minor_units ? strtoupper($lease->currency_code).' '.number_format($lease->late_fee_minor_units/100,2) : 'None'],
        ['Deposit',     $lease->deposit_minor_units  ? strtoupper($lease->currency_code).' '.number_format($lease->deposit_minor_units/100,2)  : 'None'],
        ['Frequency',   ucfirst($lease->frequency ?? 'monthly')],
        ['Start date',  $lease->start_date ? \Carbon\Carbon::parse($lease->start_date)->format('d M Y') : '—'],
        ['End date',    $lease->end_date   ? \Carbon\Carbon::parse($lease->end_date)->format('d M Y')   : 'Open-ended'],
        ['Activated',   $lease->activated_at ? \Carbon\Carbon::parse($lease->activated_at)->format('d M Y') : '—'],
      ] as [$lbl, $val])
      <div class="info-row">
        <span class="info-label">{{ $lbl }}</span>
        <span class="info-val">{{ $val }}</span>
      </div>
      @endforeach
    </div>
  </div>

  {{-- Tenant --}}
  <div class="db-card" style="margin:0">
    <div class="db-card-header"><span class="db-card-title">Tenant</span></div>
    <div class="db-card-body" style="padding:0">
      @if($lease->tenant)
      @foreach([
        ['Name',  $lease->tenant->first_name.' '.$lease->tenant->last_name],
        ['Email', $lease->tenant->email],
        ['Phone', $lease->tenant->phone ?? '—'],
      ] as [$lbl, $val])
      <div class="info-row">
        <span class="info-label">{{ $lbl }}</span>
        <span class="info-val" style="word-break:break-all">{{ $val }}</span>
      </div>
      @endforeach
      <div class="info-row" style="padding-top:14px">
        <a href="mailto:{{ $lease->tenant->email }}" class="db-btn db-btn-ghost" style="font-size:13px;text-decoration:none">
          Email tenant
        </a>
      </div>
      @else
        <div class="info-row"><span style="color:var(--text-light)">No tenant linked</span></div>
      @endif
    </div>
  </div>

  {{-- Landlord --}}
  <div class="db-card" style="margin:0">
    <div class="db-card-header"><span class="db-card-title">Landlord</span></div>
    <div class="db-card-body" style="padding:0">
      @if($lease->property?->landlord)
      @php $ll = $lease->property->landlord; @endphp
      @foreach([
        ['Name',  $ll->first_name.' '.$ll->last_name],
        ['Email', $ll->email],
      ] as [$lbl, $val])
      <div class="info-row">
        <span class="info-label">{{ $lbl }}</span>
        <span class="info-val" style="word-break:break-all">{{ $val }}</span>
      </div>
      @endforeach
      <div class="info-row" style="padding-top:14px;gap:8px">
        <a href="{{ route('admin.landlords.show', $ll) }}" class="db-btn db-btn-ghost" style="font-size:13px;text-decoration:none">
          Landlord profile →
        </a>
      </div>
      @else
        <div class="info-row"><span style="color:var(--text-light)">No landlord linked</span></div>
      @endif
    </div>
  </div>
</div>

{{-- Payment history --}}
<div class="db-card">
  <div class="db-card-header">
    <span class="db-card-title">Payment history ({{ $lease->payments->count() }})</span>
  </div>
  <div class="db-card-body" style="padding:0">
    @if($lease->payments->isEmpty())
      <div class="db-empty" style="padding:32px 20px">
        <div class="db-empty-icon">💳</div>
        <h3>No payments yet</h3>
      </div>
    @else
    <div class="db-table-wrap">
      <table class="db-table">
        <thead>
          <tr>
            <th>Due date</th>
            <th>Status</th>
            <th style="text-align:right">Amount</th>
            <th style="text-align:right">Home currency</th>
            <th>Collected</th>
            <th>Retries</th>
            <th>Processor ref</th>
          </tr>
        </thead>
        <tbody>
          @foreach($lease->payments as $payment)
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
