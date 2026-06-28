@extends('admin.layout')
@section('title', 'Platform revenue')
@section('page-title', 'Platform revenue')
@section('breadcrumb', 'Finance')

@push('styles')
<style>
/* ── KPI strip ─────────────────────────────────────── */
.rev-kpi-strip {
  display: grid;
  grid-template-columns: repeat(6, 1fr);
  gap: 12px;
  margin-bottom: 20px;
}
.rev-kpi {
  background: var(--white);
  border: 1px solid var(--cream-dark);
  border-radius: 10px;
  padding: 14px 16px;
}
.rev-kpi.accent {
  background: var(--terra);
  border-color: var(--terra);
  color: #fff;
}
.rev-kpi.accent .rev-kpi-label,
.rev-kpi.accent .rev-kpi-sub { color: rgba(255,255,255,.75); }
.rev-kpi-label { font-size: 11px; font-weight: 600; color: var(--text-light); text-transform: uppercase; letter-spacing: .5px; margin-bottom: 4px; }
.rev-kpi-value { font-size: 22px; font-weight: 700; color: var(--text-dark); line-height: 1.15; }
.rev-kpi.accent .rev-kpi-value { color: #fff; }
.rev-kpi-sub   { font-size: 11px; color: var(--text-light); margin-top: 3px; }

/* ── Overview cards ─────────────────────────────────── */
.rev-overview {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
  margin-bottom: 20px;
}
.rev-stream-row {
  display: flex; align-items: center; justify-content: space-between;
  padding: 10px 0; border-bottom: 1px solid var(--cream-dark);
}
.rev-stream-row:last-child { border-bottom: none; }
.rev-stream-label { font-size: 13px; font-weight: 600; color: var(--text-dark); display: flex; align-items: center; gap: 8px; }
.rev-stream-dot { width: 9px; height: 9px; border-radius: 50%; display: inline-block; }
.rev-stream-val { font-size: 14px; font-weight: 700; color: var(--text-dark); }
.rev-stream-sub { font-size: 11px; color: var(--text-light); margin-top: 1px; text-align: right; }
.rev-bar-track { height: 6px; background: var(--cream-dark); border-radius: 3px; margin-top: 10px; overflow: hidden; display: flex; gap: 0; }
.rev-bar-seg { height: 100%; }

/* ── Monthly table ──────────────────────────────────── */
.rev-status {
  display: inline-flex; font-size: 11px; font-weight: 600;
  padding: 2px 8px; border-radius: 20px; white-space: nowrap;
}
.rev-status.current  { background: #e3f2fd; color: #1565c0; }
.rev-status.past     { background: #e8f5e9; color: #2e7d32; }
.rev-status.upcoming { background: #f5f5f5; color: #9e9e9e; }
.take-pill {
  display: inline-flex; font-size: 11px; font-weight: 600;
  padding: 1px 7px; border-radius: 20px;
}
.take-pill.low  { background: #fce4ec; color: #b71c1c; }
.take-pill.mid  { background: #fff8e1; color: #f57f17; }
.take-pill.good { background: #e8f5e9; color: #2e7d32; }
.db-table tbody tr:hover td { background: var(--cream); }
.sub-col  { color: var(--terra); font-weight: 600; }
.comm-col { color: #1565c0;      font-weight: 600; }
.tot-col  { color: var(--text-dark); font-weight: 700; }
.dim { color: var(--text-light); font-size: 13px; }
.tfoot-row td { background: var(--cream); font-weight: 700; border-top: 2px solid var(--cream-dark); }
</style>
@endpush

@section('content')

<p class="admin-portal-note">
  Platform revenue from two streams: <strong>landlord subscriptions</strong> (${{ number_format(900/100,0) }}/month per active lease)
  and <strong>maintenance commissions</strong> ({{ $stats['maint_rate_pct'] }}% of platform-processed maintenance payments).
  GMV is total rent collected through the platform in landlords' home currency.
</p>

{{-- ── KPI strip ──────────────────────────────────────────────────────────── --}}
<div class="rev-kpi-strip">
  <div class="rev-kpi accent">
    <div class="rev-kpi-label">MRR</div>
    <div class="rev-kpi-value">
      {{ $stats['currency'] }}
      {{ number_format($stats['mrr'] / 100, 0) }}
    </div>
    <div class="rev-kpi-sub">{{ $stats['active_leases'] }} active {{ Str::plural('lease', $stats['active_leases']) }}</div>
  </div>

  <div class="rev-kpi">
    <div class="rev-kpi-label">ARR (run-rate)</div>
    <div class="rev-kpi-value">
      {{ $stats['currency'] }}
      {{ number_format($stats['arr'] / 100, 0) }}
    </div>
    <div class="rev-kpi-sub">MRR × 12</div>
  </div>

  <div class="rev-kpi">
    <div class="rev-kpi-label">{{ now()->year }} Revenue</div>
    <div class="rev-kpi-value">
      {{ $stats['currency'] }}
      {{ number_format($stats['ytd_total'] / 100, 0) }}
    </div>
    <div class="rev-kpi-sub">Subs + commissions YTD</div>
  </div>

  <div class="rev-kpi">
    <div class="rev-kpi-label">{{ now()->year }} Rent GMV</div>
    <div class="rev-kpi-value">
      {{ $stats['currency'] }}
      {{ number_format($stats['ytd_gmv'] / 100, 0) }}
    </div>
    <div class="rev-kpi-sub">Total rent collected YTD</div>
  </div>

  <div class="rev-kpi">
    <div class="rev-kpi-label">Lifetime revenue</div>
    <div class="rev-kpi-value">
      {{ $stats['currency'] }}
      {{ number_format($stats['all_total'] / 100, 0) }}
    </div>
    <div class="rev-kpi-sub">All-time platform earnings</div>
  </div>

  <div class="rev-kpi">
    <div class="rev-kpi-label">Lifetime take rate</div>
    <div class="rev-kpi-value">
      {{ $stats['all_take_rate'] !== null ? $stats['all_take_rate'] . '%' : '—' }}
    </div>
    <div class="rev-kpi-sub">Revenue ÷ GMV</div>
  </div>
</div>

{{-- ── Overview cards ─────────────────────────────────────────────────────── --}}
<div class="rev-overview">

  {{-- Revenue stream breakdown --}}
  <div class="db-card">
    <div class="db-card-header">
      <span class="db-card-title">Revenue streams — lifetime</span>
    </div>
    <div class="db-card-body">
      @php
        $totalAll  = max($stats['all_total'], 1);
        $subPct    = round($stats['all_sub_rev']    / $totalAll * 100);
        $maintPct  = round($stats['all_maint_comm'] / $totalAll * 100);
      @endphp

      <div class="rev-stream-row">
        <div>
          <div class="rev-stream-label">
            <span class="rev-stream-dot" style="background:var(--terra)"></span>
            Landlord subscriptions
          </div>
          <div style="font-size:11px;color:var(--text-light);margin-top:2px">
            ${{ number_format(900/100,2) }}/month per active lease
          </div>
        </div>
        <div style="text-align:right">
          <div class="rev-stream-val">
            {{ $stats['currency'] }} {{ number_format($stats['all_sub_rev'] / 100, 0) }}
          </div>
          <div class="rev-stream-sub">{{ $subPct }}% of revenue</div>
        </div>
      </div>

      <div class="rev-stream-row">
        <div>
          <div class="rev-stream-label">
            <span class="rev-stream-dot" style="background:#1565c0"></span>
            Maintenance commissions
          </div>
          <div style="font-size:11px;color:var(--text-light);margin-top:2px">
            {{ $stats['maint_rate_pct'] }}% on platform-processed invoices
          </div>
        </div>
        <div style="text-align:right">
          <div class="rev-stream-val">
            {{ $stats['currency'] }} {{ number_format($stats['all_maint_comm'] / 100, 0) }}
          </div>
          <div class="rev-stream-sub">{{ $maintPct }}% of revenue</div>
        </div>
      </div>

      {{-- Visual split bar --}}
      <div class="rev-bar-track" style="margin-top:16px">
        <div class="rev-bar-seg" style="width:{{ $subPct }}%;background:var(--terra)"></div>
        <div class="rev-bar-seg" style="width:{{ $maintPct }}%;background:#1565c0"></div>
        @if($subPct + $maintPct < 100)
          <div class="rev-bar-seg" style="width:{{ 100 - $subPct - $maintPct }}%;background:var(--cream-dark)"></div>
        @endif
      </div>
      <div style="display:flex;gap:16px;margin-top:8px">
        <div style="font-size:11px;color:var(--terra);font-weight:600">■ Subscriptions {{ $subPct }}%</div>
        <div style="font-size:11px;color:#1565c0;font-weight:600">■ Commission {{ $maintPct }}%</div>
      </div>
    </div>
  </div>

  {{-- YTD summary card --}}
  <div class="db-card">
    <div class="db-card-header">
      <span class="db-card-title">{{ now()->year }} at a glance</span>
    </div>
    <div class="db-card-body">
      <div class="rev-stream-row">
        <span style="font-size:13px;color:var(--text-mid)">Subscription revenue YTD</span>
        <strong style="color:var(--terra)">
          {{ $stats['currency'] }} {{ number_format($stats['ytd_sub_rev'] / 100, 0) }}
        </strong>
      </div>
      <div class="rev-stream-row">
        <span style="font-size:13px;color:var(--text-mid)">Maintenance commission YTD</span>
        <strong style="color:#1565c0">
          {{ $stats['currency'] }} {{ number_format($stats['ytd_maint_comm'] / 100, 0) }}
        </strong>
      </div>
      <div class="rev-stream-row">
        <span style="font-size:13px;color:var(--text-mid)">Total platform revenue YTD</span>
        <strong style="color:var(--text-dark);font-size:15px">
          {{ $stats['currency'] }} {{ number_format($stats['ytd_total'] / 100, 0) }}
        </strong>
      </div>
      <div class="rev-stream-row">
        <span style="font-size:13px;color:var(--text-mid)">Rent GMV YTD</span>
        <strong>{{ $stats['currency'] }} {{ number_format($stats['ytd_gmv'] / 100, 0) }}</strong>
      </div>
      <div class="rev-stream-row" style="border-bottom:none">
        <span style="font-size:13px;color:var(--text-mid)">YTD take rate</span>
        @php
          $ytdTake = $stats['ytd_gmv'] > 0 ? round($stats['ytd_total'] / $stats['ytd_gmv'] * 100, 2) : null;
        @endphp
        <strong>{{ $ytdTake !== null ? $ytdTake . '%' : '—' }}</strong>
      </div>
    </div>
  </div>

</div>

{{-- ── Monthly revenue table ───────────────────────────────────────────────── --}}
<div class="db-card">
  <div class="db-card-header">
    <span class="db-card-title">Monthly revenue breakdown</span>
    <span class="db-card-sub">{{ count($months) }} billing {{ Str::plural('period', count($months)) }}</span>
  </div>

  @if(empty($months))
    <div class="db-card-body" style="text-align:center;padding:48px 20px;color:var(--text-light)">
      <div style="font-size:36px;margin-bottom:12px">💰</div>
      <div style="font-weight:600;color:var(--text-dark);margin-bottom:6px">No revenue data yet</div>
      <div style="font-size:13px">Revenue will appear once landlords have active leases.</div>
    </div>
  @else
    <div class="db-card-body" style="padding:0">
      <table class="db-table">
        <thead>
          <tr>
            <th>Period</th>
            <th style="text-align:right">Active leases</th>
            <th style="text-align:right">Subscription</th>
            <th style="text-align:right">Maint. commission</th>
            <th style="text-align:right">Total revenue</th>
            <th style="text-align:right">Rent GMV</th>
            <th style="text-align:center">Take rate</th>
            <th style="text-align:center">Status</th>
          </tr>
        </thead>
        <tbody>
          @foreach($months as $row)
            @php
              $isNow = $row['period']->isCurrentMonth();
              $isFut = $row['period']->isFuture() && !$isNow;
              $statusClass = $isNow ? 'current' : ($isFut ? 'upcoming' : 'past');
              $statusLabel = $isNow ? 'Current' : ($isFut ? 'Upcoming' : 'Confirmed');
              $take = $row['take_rate'];
              $takeClass = $take === null ? '' : ($take >= 3 ? 'good' : ($take >= 1 ? 'mid' : 'low'));
            @endphp
            <tr>
              <td style="font-weight:600;color:var(--text-dark)">
                {{ $row['period']->format('F Y') }}
              </td>
              <td style="text-align:right;color:var(--text-mid)">
                {{ $row['lease_count'] }}
              </td>
              <td style="text-align:right" class="sub-col">
                @if($row['sub_rev'] > 0)
                  {{ $stats['currency'] }} {{ number_format($row['sub_rev'] / 100, 0) }}
                @else
                  <span class="dim">—</span>
                @endif
              </td>
              <td style="text-align:right" class="comm-col">
                @if($row['maint_comm'] > 0)
                  {{ $stats['currency'] }} {{ number_format($row['maint_comm'] / 100, 0) }}
                  <div style="font-size:11px;color:var(--text-light);font-weight:400">
                    {{ $stats['currency'] }} {{ number_format($row['maint_paid'] / 100, 0) }} invoiced
                  </div>
                @else
                  <span class="dim">—</span>
                @endif
              </td>
              <td style="text-align:right" class="tot-col">
                @if($row['total_rev'] > 0)
                  {{ $stats['currency'] }} {{ number_format($row['total_rev'] / 100, 0) }}
                @else
                  <span class="dim">—</span>
                @endif
              </td>
              <td style="text-align:right;color:var(--text-mid)">
                @if($row['gmv'] > 0)
                  {{ $stats['currency'] }} {{ number_format($row['gmv'] / 100, 0) }}
                @else
                  <span class="dim">—</span>
                @endif
              </td>
              <td style="text-align:center">
                @if($take !== null)
                  <span class="take-pill {{ $takeClass }}">{{ $take }}%</span>
                @else
                  <span class="dim">—</span>
                @endif
              </td>
              <td style="text-align:center">
                <span class="rev-status {{ $statusClass }}">{{ $statusLabel }}</span>
              </td>
            </tr>
          @endforeach
        </tbody>
        <tfoot>
          <tr class="tfoot-row">
            <td><strong>Lifetime totals</strong></td>
            <td style="text-align:right;color:var(--text-mid)">—</td>
            <td style="text-align:right" class="sub-col">
              {{ $stats['currency'] }} {{ number_format($stats['all_sub_rev'] / 100, 0) }}
            </td>
            <td style="text-align:right" class="comm-col">
              {{ $stats['currency'] }} {{ number_format($stats['all_maint_comm'] / 100, 0) }}
            </td>
            <td style="text-align:right" class="tot-col">
              {{ $stats['currency'] }} {{ number_format($stats['all_total'] / 100, 0) }}
            </td>
            <td style="text-align:right;color:var(--text-mid)">
              {{ $stats['currency'] }} {{ number_format($stats['all_gmv'] / 100, 0) }}
            </td>
            <td style="text-align:center">
              @if($stats['all_take_rate'] !== null)
                <span class="take-pill {{ $stats['all_take_rate'] >= 3 ? 'good' : ($stats['all_take_rate'] >= 1 ? 'mid' : 'low') }}">
                  {{ $stats['all_take_rate'] }}%
                </span>
              @else
                <span class="dim">—</span>
              @endif
            </td>
            <td></td>
          </tr>
        </tfoot>
      </table>
    </div>
  @endif
</div>

@endsection
