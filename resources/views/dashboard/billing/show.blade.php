@extends('dashboard.layout')
@section('page-title', $period->format('F Y').' · Billing')

@section('topbar-actions')
<a href="{{ route('billing.index') }}" class="db-btn db-btn-ghost">← All billing</a>
@endsection

@section('content')
@php
$badgeClass = ['due' => 'badge-gold', 'paid' => 'badge-green', 'upcoming' => 'badge-grey'];
$badgeLabel = ['due' => 'Due', 'paid' => 'Paid', 'upcoming' => 'Upcoming'];
@endphp

<div class="db-stats" style="margin-bottom:28px">
  <div class="db-stat">
    <div class="db-stat-label">Period</div>
    <div class="db-stat-value" style="font-size:18px">{{ $period->format('F Y') }}</div>
  </div>
  <div class="db-stat">
    <div class="db-stat-label">Active leases</div>
    <div class="db-stat-value">{{ $count }}</div>
  </div>
  <div class="db-stat">
    <div class="db-stat-label">Rate / lease</div>
    <div class="db-stat-value">$9.00</div>
  </div>
  <div class="db-stat">
    <div class="db-stat-label">Total due</div>
    <div class="db-stat-value">${{ number_format($total / 100, 2) }}</div>
    <div style="margin-top:4px"><span class="badge {{ $badgeClass[$status] }}">{{ $badgeLabel[$status] }}</span></div>
  </div>
</div>

<div class="db-card">
  <div class="db-card-header" style="display:flex;align-items:center;justify-content:space-between">
    <span class="db-card-title">Line items</span>
    <span style="font-size:13px;color:var(--text-light)">{{ $count }} lease{{ $count !== 1 ? 's' : '' }} × $9.00 = <strong style="color:var(--text-dark)">${{ number_format($total / 100, 2) }}</strong></span>
  </div>
  @if($leases->isEmpty())
    <div class="db-empty" style="padding:40px">
      <p style="margin:0;color:var(--text-mid)">No active leases in this period.</p>
    </div>
  @else
  <div class="db-table-wrap">
    <table class="db-table">
      <thead>
        <tr>
          <th class="db-col-num">#</th>
          <th>Property</th>
          <th>Unit</th>
          <th>Tenant</th>
          <th>Country</th>
          <th style="text-align:right">Charge</th>
        </tr>
      </thead>
      <tbody>
        @foreach($leases as $i => $lease)
        <tr>
          <td class="db-col-num">{{ $i + 1 }}</td>
          <td>
            <div style="font-weight:600;color:var(--text-dark)">{{ $lease->property->name ?? '—' }}</div>
            <div style="font-size:11px;color:var(--text-light)">{{ $lease->property->occupancy_mode === 'multi' ? 'Multi-unit' : 'Single-unit' }}</div>
          </td>
          <td>
            @if($lease->unit_label)
              <span style="font-size:13px;color:var(--text-mid)">Unit {{ $lease->unit_label }}</span>
            @else
              <span style="color:var(--text-light)">—</span>
            @endif
          </td>
          <td>{{ $lease->tenant?->first_name }} {{ $lease->tenant?->last_name }}</td>
          <td>{{ $lease->property->country_code ?? '—' }}</td>
          <td style="text-align:right;font-weight:600">$9.00</td>
        </tr>
        @endforeach
      </tbody>
      <tfoot>
        <tr style="border-top:2px solid var(--cream-dark)">
          <td colspan="5" style="text-align:right;font-weight:700;font-size:14px;padding-top:14px">Total</td>
          <td style="text-align:right;font-weight:700;font-size:16px;font-family:'Fraunces',serif;padding-top:14px">${{ number_format($total / 100, 2) }}</td>
        </tr>
      </tfoot>
    </table>
  </div>
  @endif
</div>
@endsection
