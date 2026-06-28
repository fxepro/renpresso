@extends('admin.layout')
@section('title', 'Rent collections')
@section('page-title', 'Rent collections')
@section('breadcrumb', 'Finance')

@push('styles')
<style>
.rc-badge {
  display: inline-flex; font-size: 11px; font-weight: 600;
  padding: 2px 9px; border-radius: 20px; white-space: nowrap;
}
.rc-badge.good    { background: #e8f5e9; color: #2e7d32; }
.rc-badge.warn    { background: #fff8e1; color: #f57f17; }
.rc-badge.danger  { background: #fce4ec; color: #b71c1c; }
.rc-badge.neutral { background: #f5f5f5; color: #616161; }
.db-table tbody tr { cursor: pointer; }
.db-table tbody tr:hover td { background: var(--cream); }
.t-name { font-weight: 600; color: var(--text-dark); }
.t-sub  { font-size: 12px; color: var(--text-light); margin-top: 1px; }
.bar-track {
  width: 100%; height: 5px; background: #eee; border-radius: 3px; margin-top: 4px; overflow: hidden;
}
.bar-fill { height: 100%; background: #4caf50; border-radius: 3px; }
.bar-fill.warn { background: #ff9800; }
.bar-fill.danger { background: #f44336; }
</style>
@endpush

@section('content')

<p class="admin-portal-note">
  Platform-wide rent payment activity, grouped by billing month. Each row covers all tenant payments
  due in that period — track collection rates, failed payments, and gross rent volume flowing through
  the platform.
</p>

{{-- ── Stats ──────────────────────────────────────────────────────────────── --}}
<div class="db-stats">
  <div class="db-stat terra">
    <div class="db-stat-label">{{ $stats['current_month_label'] }} GMV</div>
    <div class="db-stat-value">
      {{ $homeCurrency }}
      {{ number_format($stats['current_month_gmv'] / 100, 0) }}
    </div>
    <div class="db-stat-sub">Collected rent this month</div>
  </div>
  <div class="db-stat green">
    <div class="db-stat-label">YTD collected</div>
    <div class="db-stat-value">
      {{ $homeCurrency }}
      {{ number_format($stats['ytd_gmv'] / 100, 0) }}
    </div>
    <div class="db-stat-sub">{{ now()->year }} to date</div>
  </div>
  <div class="db-stat {{ $stats['failed_payments'] > 0 ? '' : 'green' }}">
    <div class="db-stat-label">Failed payments</div>
    <div class="db-stat-value" style="{{ $stats['failed_payments'] > 0 ? 'color:#b71c1c' : '' }}">
      {{ $stats['failed_payments'] }}
    </div>
    <div class="db-stat-sub">All time · need retry</div>
  </div>
  <div class="db-stat {{ $stats['success_rate'] >= 95 ? 'green' : ($stats['success_rate'] >= 80 ? '' : '') }}">
    <div class="db-stat-label">Collection rate</div>
    <div class="db-stat-value">{{ $stats['success_rate'] }}%</div>
    <div class="db-stat-sub">{{ $stats['total_payments'] }} total payments</div>
  </div>
</div>

{{-- ── Monthly table ────────────────────────────────────────────────────────── --}}
<div class="db-card">
  <div class="db-card-header">
    <span class="db-card-title">Monthly rent collection history</span>
    <span class="db-card-sub">{{ $monthly->count() }} billing {{ Str::plural('period', $monthly->count()) }}</span>
  </div>

  @if($monthly->isEmpty())
    <div class="db-card-body" style="text-align:center;padding:48px 20px;color:var(--text-light)">
      <div style="font-size:36px;margin-bottom:12px">🏠</div>
      <div style="font-weight:600;color:var(--text-dark);margin-bottom:6px">No rent payments yet</div>
      <div style="font-size:13px">Payments will appear here once tenants make their first rent payment.</div>
    </div>
  @else
    <div class="db-card-body" style="padding:0">
      <table class="db-table">
        <thead>
          <tr>
            <th>Period</th>
            <th style="text-align:right">Total</th>
            <th style="text-align:right">Collected</th>
            <th style="text-align:right">Failed</th>
            <th style="text-align:right">Pending</th>
            <th style="text-align:right">Gross rent ({{ $homeCurrency }})</th>
            <th style="text-align:center">Rate</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @foreach($monthly as $row)
            @php
              $url      = route('admin.rent-collections.month', [$row->yr, $row->mo]);
              $rate     = $row->total_count > 0 ? round($row->success_count / $row->total_count * 100) : 0;
              $rateClass= $rate >= 95 ? 'good' : ($rate >= 75 ? 'warn' : 'danger');
              $barClass = $rate >= 95 ? ''     : ($rate >= 75 ? 'warn' : 'danger');
              $isNow    = $row->yr === (int) now()->year && $row->mo === (int) now()->month;
            @endphp
            <tr onclick="window.location='{{ $url }}'">
              <td>
                <div class="t-name">
                  @php
                    $label = \Carbon\Carbon::create($row->yr, $row->mo, 1)->format('F Y');
                  @endphp
                  {{ $label }}
                  @if($isNow)
                    <span class="rc-badge neutral" style="margin-left:6px;font-size:10px">current</span>
                  @endif
                </div>
              </td>
              <td style="text-align:right;color:var(--text-mid)">{{ $row->total_count }}</td>
              <td style="text-align:right">
                <span class="rc-badge {{ $row->success_count > 0 ? 'good' : 'neutral' }}">
                  {{ $row->success_count }}
                </span>
              </td>
              <td style="text-align:right">
                @if($row->failed_count > 0)
                  <span class="rc-badge danger">{{ $row->failed_count }}</span>
                @else
                  <span style="color:var(--text-light);font-size:13px">—</span>
                @endif
              </td>
              <td style="text-align:right">
                @if($row->pending_count > 0)
                  <span class="rc-badge warn">{{ $row->pending_count }}</span>
                @else
                  <span style="color:var(--text-light);font-size:13px">—</span>
                @endif
              </td>
              <td style="text-align:right;font-weight:600;color:var(--text-dark)">
                {{ $homeCurrency }} {{ number_format($row->home_gmv / 100, 0) }}
              </td>
              <td style="text-align:center;min-width:90px">
                <span class="rc-badge {{ $rateClass }}">{{ $rate }}%</span>
                <div class="bar-track">
                  <div class="bar-fill {{ $barClass }}" style="width:{{ $rate }}%"></div>
                </div>
              </td>
              <td style="text-align:right" onclick="event.stopPropagation()">
                <a href="{{ $url }}" class="db-table-link" style="font-size:12px;white-space:nowrap">Detail →</a>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @endif
</div>

@endsection
