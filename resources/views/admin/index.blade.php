@extends('admin.layout')
@section('title', 'Platform dashboard')
@section('page-title', 'Platform dashboard')
@section('breadcrumb', 'Overview')

@push('styles')
<style>
/* ── Quick-link grid ─────────────────────────────────── */
.ql-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 12px;
  margin-bottom: 20px;
}
.ql-card {
  background: var(--white);
  border: 1px solid var(--cream-dark);
  border-radius: 10px;
  padding: 14px 16px;
  text-decoration: none;
  display: block;
  transition: border-color .15s, box-shadow .15s;
}
.ql-card:hover {
  border-color: var(--terra);
  box-shadow: 0 2px 8px rgba(0,0,0,.06);
}
.ql-icon  { font-size: 20px; margin-bottom: 6px; }
.ql-label { font-size: 12px; font-weight: 700; color: var(--text-dark); }
.ql-sub   { font-size: 11px; color: var(--text-light); margin-top: 2px; }

/* ── Alert strip ──────────────────────────────────────── */
.alert-strip {
  background: #fff8e1;
  border: 1px solid #ffe082;
  border-radius: 8px;
  padding: 10px 16px;
  font-size: 13px;
  color: #5d4037;
  margin-bottom: 16px;
  display: flex;
  align-items: center;
  gap: 10px;
}
.alert-strip a { color: var(--terra); font-weight: 600; }

/* ── Health pills ─────────────────────────────────────── */
.health-pill {
  display: inline-flex; font-size: 11px; font-weight: 600;
  padding: 2px 9px; border-radius: 20px;
}
.health-pill.green  { background: #e8f5e9; color: #2e7d32; }
.health-pill.amber  { background: #fff8e1; color: #f57f17; }
.health-pill.red    { background: #fce4ec; color: #b71c1c; }

/* ── Revenue mini-bar ─────────────────────────────────── */
.rev-bar-track { height: 5px; background: var(--cream-dark); border-radius: 3px; margin-top: 6px; overflow: hidden; display: flex; }
.rev-bar-seg   { height: 100%; }
</style>
@endpush

@section('content')

{{-- ── Alerts ─────────────────────────────────────────────────────────────── --}}
@if($stats['failed_month'] > 0)
  <div class="alert-strip">
    ⚠️ <strong>{{ $stats['failed_month'] }} failed {{ Str::plural('payment', $stats['failed_month']) }}</strong> this month.
    <a href="{{ route('admin.leases') }}">Review in Leases →</a>
  </div>
@endif
@if($stats['invoices_overdue'] > 0)
  <div class="alert-strip">
    ⚠️ <strong>{{ $stats['invoices_overdue'] }} overdue maintenance {{ Str::plural('invoice', $stats['invoices_overdue']) }}</strong>.
    <a href="{{ route('admin.maintenance-invoices') }}">Review →</a>
  </div>
@endif

{{-- ── Platform revenue KPIs ──────────────────────────────────────────────── --}}
<div class="db-stats" style="margin-bottom:14px">
  <div class="db-stat terra">
    <div class="db-stat-label">MRR</div>
    <div class="db-stat-value">
      {{ $stats['currency'] }} {{ number_format($stats['mrr'] / 100, 0) }}
    </div>
    <div class="db-stat-sub">{{ $stats['active_leases'] }} active {{ Str::plural('lease', $stats['active_leases']) }} × $9</div>
  </div>
  <div class="db-stat green">
    <div class="db-stat-label">{{ now()->year }} Revenue</div>
    <div class="db-stat-value">
      {{ $stats['currency'] }} {{ number_format($stats['ytd_total'] / 100, 0) }}
    </div>
    <div class="db-stat-sub">Subscriptions + commissions</div>
  </div>
  <div class="db-stat green">
    <div class="db-stat-label">Rent GMV ({{ now()->format('M') }})</div>
    <div class="db-stat-value">
      {{ $stats['currency'] }} {{ number_format($stats['rent_gmv_month'] / 100, 0) }}
    </div>
    <div class="db-stat-sub">
      @if($stats['collection_rate'] !== null)
        <span class="health-pill {{ $stats['collection_rate'] >= 90 ? 'green' : ($stats['collection_rate'] >= 70 ? 'amber' : 'red') }}">
          {{ $stats['collection_rate'] }}% collected
        </span>
      @else
        No payments yet
      @endif
    </div>
  </div>
  <div class="db-stat {{ $stats['invoices_overdue'] > 0 ? '' : 'green' }}">
    <div class="db-stat-label">Maint. GMV ({{ now()->format('M') }})</div>
    <div class="db-stat-value">
      {{ number_format($stats['maint_gmv_month'] / 100, 0) }}
    </div>
    <div class="db-stat-sub">Platform-processed invoices</div>
  </div>
</div>

{{-- ── Operations KPIs ─────────────────────────────────────────────────────── --}}
<div class="db-stats" style="margin-bottom:20px">
  <div class="db-stat">
    <div class="db-stat-label">Landlords</div>
    <div class="db-stat-value">{{ $stats['landlords'] }}</div>
    <div class="db-stat-sub"><a href="{{ route('admin.landlords') }}" class="db-table-link">View all →</a></div>
  </div>
  <div class="db-stat">
    <div class="db-stat-label">Tenants</div>
    <div class="db-stat-value">{{ $stats['tenants'] }}</div>
    <div class="db-stat-sub"><a href="{{ route('admin.tenants') }}" class="db-table-link">View all →</a></div>
  </div>
  <div class="db-stat">
    <div class="db-stat-label">Properties</div>
    <div class="db-stat-value">{{ $stats['properties'] }}</div>
    <div class="db-stat-sub"><a href="{{ route('admin.properties') }}" class="db-table-link">View all →</a></div>
  </div>
  <div class="db-stat">
    <div class="db-stat-label">Maint. teams</div>
    <div class="db-stat-value">{{ $stats['maint_teams'] }}</div>
    <div class="db-stat-sub"><a href="{{ route('admin.maintenance-teams') }}" class="db-table-link">View all →</a></div>
  </div>
</div>

{{-- ── YTD revenue breakdown + quick links ─────────────────────────────────── --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px">

  {{-- Revenue breakdown card --}}
  <div class="db-card">
    <div class="db-card-header">
      <span class="db-card-title">{{ now()->year }} Revenue breakdown</span>
      <a href="{{ route('admin.revenue') }}" class="db-table-link" style="font-size:12px">Full report →</a>
    </div>
    <div class="db-card-body">
      @php
        $ytdTotal = max($stats['ytd_total'], 1);
        $subPct   = round($stats['ytd_sub_rev']    / $ytdTotal * 100);
        $comPct   = round($stats['ytd_maint_comm'] / $ytdTotal * 100);
      @endphp
      <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid var(--cream-dark)">
        <div>
          <div style="font-size:12px;font-weight:600;color:var(--terra)">Landlord subscriptions</div>
          <div style="font-size:11px;color:var(--text-light)">$9 × active leases × months</div>
        </div>
        <div style="text-align:right">
          <div style="font-weight:700;color:var(--terra)">
            {{ $stats['currency'] }} {{ number_format($stats['ytd_sub_rev'] / 100, 0) }}
          </div>
          <div style="font-size:11px;color:var(--text-light)">{{ $subPct }}%</div>
        </div>
      </div>
      <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0">
        <div>
          <div style="font-size:12px;font-weight:600;color:#1565c0">Maintenance commissions</div>
          <div style="font-size:11px;color:var(--text-light)">5% on platform-processed invoices</div>
        </div>
        <div style="text-align:right">
          <div style="font-weight:700;color:#1565c0">
            {{ $stats['currency'] }} {{ number_format($stats['ytd_maint_comm'] / 100, 0) }}
          </div>
          <div style="font-size:11px;color:var(--text-light)">{{ $comPct }}%</div>
        </div>
      </div>
      <div class="rev-bar-track">
        <div class="rev-bar-seg" style="width:{{ $subPct }}%;background:var(--terra)"></div>
        <div class="rev-bar-seg" style="width:{{ $comPct }}%;background:#1565c0"></div>
      </div>
      <div style="margin-top:12px;padding-top:10px;border-top:1px solid var(--cream-dark);display:flex;justify-content:space-between;align-items:center">
        <span style="font-size:12px;color:var(--text-light)">Total YTD</span>
        <strong style="font-size:16px;color:var(--text-dark)">
          {{ $stats['currency'] }} {{ number_format($stats['ytd_total'] / 100, 0) }}
        </strong>
      </div>
    </div>
  </div>

  {{-- Quick links --}}
  <div class="db-card">
    <div class="db-card-header">
      <span class="db-card-title">Finance &amp; Operations</span>
    </div>
    <div class="db-card-body" style="padding:10px 0">
      @php
        $links = [
          ['route' => 'admin.revenue',              'icon' => '💰', 'label' => 'Platform revenue',       'section' => 'Finance'],
          ['route' => 'admin.rent-collections',     'icon' => '🏠', 'label' => 'Rent collections',       'section' => 'Finance'],
          ['route' => 'admin.landlord-billing',     'icon' => '🧾', 'label' => 'Landlord subscriptions', 'section' => 'Finance'],
          ['route' => 'admin.landlords',            'icon' => '🏢', 'label' => 'Landlords',              'section' => 'Operations'],
          ['route' => 'admin.tenants',              'icon' => '👥', 'label' => 'Tenants',                'section' => 'Operations'],
          ['route' => 'admin.leases',               'icon' => '📋', 'label' => 'Leases',                 'section' => 'Operations'],
          ['route' => 'admin.properties',           'icon' => '🏘️', 'label' => 'Properties',             'section' => 'Operations'],
          ['route' => 'admin.maintenance-teams',    'icon' => '🧰', 'label' => 'Maint. teams',           'section' => 'Operations'],
          ['route' => 'admin.maintenance-invoices', 'icon' => '📄', 'label' => 'Maint. invoices',        'section' => 'Operations'],
        ];
      @endphp
      @foreach($links as $link)
        <a href="{{ route($link['route']) }}" style="display:flex;align-items:center;gap:10px;padding:7px 20px;text-decoration:none;border-radius:0;transition:background .1s" onmouseover="this.style.background='var(--cream)'" onmouseout="this.style.background=''">
          <span style="font-size:15px;width:22px;text-align:center">{{ $link['icon'] }}</span>
          <span style="font-size:13px;font-weight:600;color:var(--text-dark)">{{ $link['label'] }}</span>
          <span style="font-size:11px;color:var(--text-light);margin-left:auto">{{ $link['section'] }}</span>
        </a>
      @endforeach
    </div>
  </div>

</div>

{{-- ── Platform health row ─────────────────────────────────────────────────── --}}
<div class="db-card">
  <div class="db-card-header">
    <span class="db-card-title">Platform health</span>
    <span class="db-card-sub">{{ now()->format('F Y') }}</span>
  </div>
  <div class="db-card-body">
    <div style="display:grid;grid-template-columns:repeat(5,1fr);gap:0;text-align:center">

      <div style="padding:12px;border-right:1px solid var(--cream-dark)">
        <div style="font-size:11px;color:var(--text-light);margin-bottom:4px">Collection rate</div>
        @if($stats['collection_rate'] !== null)
          <div style="font-size:20px;font-weight:700">{{ $stats['collection_rate'] }}%</div>
          <span class="health-pill {{ $stats['collection_rate'] >= 90 ? 'green' : ($stats['collection_rate'] >= 70 ? 'amber' : 'red') }}" style="margin-top:4px">
            {{ $stats['collection_rate'] >= 90 ? 'Healthy' : ($stats['collection_rate'] >= 70 ? 'At risk' : 'Poor') }}
          </span>
        @else
          <div style="font-size:20px;font-weight:700;color:var(--text-light)">—</div>
        @endif
      </div>

      <div style="padding:12px;border-right:1px solid var(--cream-dark)">
        <div style="font-size:11px;color:var(--text-light);margin-bottom:4px">Failed payments</div>
        <div style="font-size:20px;font-weight:700;color:{{ $stats['failed_month'] > 0 ? '#b71c1c' : '#2e7d32' }}">
          {{ $stats['failed_month'] }}
        </div>
        <span class="health-pill {{ $stats['failed_month'] === 0 ? 'green' : 'red' }}" style="margin-top:4px">
          {{ $stats['failed_month'] === 0 ? 'None' : 'Action needed' }}
        </span>
      </div>

      <div style="padding:12px;border-right:1px solid var(--cream-dark)">
        <div style="font-size:11px;color:var(--text-light);margin-bottom:4px">Overdue invoices</div>
        <div style="font-size:20px;font-weight:700;color:{{ $stats['invoices_overdue'] > 0 ? '#f57f17' : '#2e7d32' }}">
          {{ $stats['invoices_overdue'] }}
        </div>
        <span class="health-pill {{ $stats['invoices_overdue'] === 0 ? 'green' : 'amber' }}" style="margin-top:4px">
          {{ $stats['invoices_overdue'] === 0 ? 'Clear' : 'Follow up' }}
        </span>
      </div>

      <div style="padding:12px;border-right:1px solid var(--cream-dark)">
        <div style="font-size:11px;color:var(--text-light);margin-bottom:4px">Active leases</div>
        <div style="font-size:20px;font-weight:700">{{ $stats['active_leases'] }}</div>
        <span class="health-pill green" style="margin-top:4px">MRR {{ $stats['currency'] }} {{ number_format($stats['mrr']/100, 0) }}</span>
      </div>

      <div style="padding:12px">
        <div style="font-size:11px;color:var(--text-light);margin-bottom:4px">Waitlist</div>
        <div style="font-size:20px;font-weight:700">{{ $stats['waitlist'] }}</div>
        <span class="health-pill green" style="margin-top:4px">Marketing</span>
      </div>

    </div>
  </div>
</div>

@endsection
