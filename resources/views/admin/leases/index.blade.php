@extends('admin.layout')
@section('title', 'Leases')
@section('page-title', 'Leases')
@section('breadcrumb', 'Operations')

@push('styles')
<style>
.lease-status-badge {
  display: inline-flex; font-size: 11px; font-weight: 600;
  padding: 2px 9px; border-radius: 20px; white-space: nowrap;
}
.lease-status-badge.active     { background: #e8f5e9; color: #2e7d32; }
.lease-status-badge.expired    { background: #fff8e1; color: #f57f17; }
.lease-status-badge.terminated { background: #fce4ec; color: #b71c1c; }
.lease-status-badge.draft      { background: #f5f5f5; color: #9e9e9e; }
.pay-warn {
  display: inline-flex; align-items: center; gap: 4px;
  font-size: 11px; font-weight: 600; padding: 2px 7px;
  border-radius: 20px; background: #fce4ec; color: #b71c1c;
}
.pay-pending {
  display: inline-flex; align-items: center; gap: 4px;
  font-size: 11px; font-weight: 600; padding: 2px 7px;
  border-radius: 20px; background: #fff8e1; color: #f57f17;
}
.ll-sub { font-size: 12px; color: var(--text-light); margin-top: 1px; }
.prop-link, .ll-link { font-weight: 600; color: var(--text-dark); text-decoration: none; }
.prop-link:hover, .ll-link:hover { color: var(--terra); }
.db-table tbody tr { cursor: pointer; }
.db-table tbody tr:hover td { background: var(--cream); }
.filter-tabs { display: flex; gap: 4px; padding: 14px 20px 0; }
.filter-tab {
  font-size: 12px; font-weight: 600; padding: 5px 14px;
  border-radius: 20px; cursor: pointer; border: 1px solid var(--cream-dark);
  background: var(--white); color: var(--text-mid);
}
.filter-tab.active { background: var(--text-dark); color: var(--white); border-color: var(--text-dark); }
.filter-tab:hover:not(.active) { background: var(--cream); }
</style>
@endpush

@section('content')

<p class="admin-portal-note">All leases across the platform. Failed or pending payments flag leases needing attention.</p>

<div class="db-stats">
  <div class="db-stat terra">
    <div class="db-stat-label">Total leases</div>
    <div class="db-stat-value">{{ $stats['total'] }}</div>
    <div class="db-stat-sub">All time</div>
  </div>
  <div class="db-stat green">
    <div class="db-stat-label">Active</div>
    <div class="db-stat-value">{{ $stats['active'] }}</div>
    <div class="db-stat-sub">Currently running</div>
  </div>
  <div class="db-stat">
    <div class="db-stat-label">Expired / terminated</div>
    <div class="db-stat-value">{{ $stats['expired'] + $stats['terminated'] }}</div>
    <div class="db-stat-sub">{{ $stats['expired'] }} expired · {{ $stats['terminated'] }} terminated</div>
  </div>
  <div class="db-stat {{ $stats['failed_payments'] > 0 ? '' : '' }}">
    <div class="db-stat-label">Payment alerts</div>
    <div class="db-stat-value" style="{{ $stats['failed_payments'] > 0 ? 'color:#b71c1c' : '' }}">
      {{ $stats['failed_payments'] + $stats['pending_payments'] }}
    </div>
    <div class="db-stat-sub">{{ $stats['failed_payments'] }} failed · {{ $stats['pending_payments'] }} pending</div>
  </div>
</div>

<div class="db-card">
  <div class="db-card-header">
    <span class="db-card-title">Lease register ({{ $stats['total'] }})</span>
  </div>

  <div class="filter-tabs">
    @foreach(['all' => 'All', 'active' => 'Active', 'expired' => 'Expired', 'terminated' => 'Terminated', 'alerts' => 'Payment alerts'] as $key => $label)
    @php
      $cnt = match($key) {
        'all'        => $stats['total'],
        'active'     => $stats['active'],
        'expired'    => $stats['expired'],
        'terminated' => $stats['terminated'],
        'alerts'     => $stats['failed_payments'] + $stats['pending_payments'],
      };
    @endphp
    <button class="filter-tab {{ $key === 'all' ? 'active' : '' }}"
            onclick="filterLeases('{{ $key }}')" data-filter="{{ $key }}">
      {{ $label }} <span style="opacity:.6;margin-left:3px">{{ $cnt }}</span>
    </button>
    @endforeach
  </div>

  <div class="db-card-body" style="padding:0;padding-top:14px">
    <div class="db-table-wrap">
      <table class="db-table" id="leaseTable">
        <thead>
          <tr>
            <th>Property</th>
            <th>Tenant</th>
            <th>Landlord</th>
            <th style="text-align:center">Unit</th>
            <th>Status</th>
            <th style="text-align:right">Rent / mo</th>
            <th style="text-align:center">Due day</th>
            <th>Activated</th>
            <th>End</th>
            <th style="text-align:center">Payments</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @foreach($leases as $lease)
          @php
            $detailUrl  = route('admin.leases.show', $lease);
            $hasAlert   = $lease->failed_payment_count > 0 || $lease->pending_payment_count > 0;
            $isMulti    = $lease->property?->occupancy_mode === 'multi';
            $unitLabel  = $isMulti
              ? ($lease->unit_label ?: ($lease->unit_seq ? "Unit {$lease->unit_seq}" : '—'))
              : '—';
          @endphp
          <tr onclick="window.location='{{ $detailUrl }}'"
              data-status="{{ $lease->status }}"
              data-alert="{{ $hasAlert ? '1' : '0' }}">
            <td>
              @if($lease->property)
                <a href="{{ route('admin.properties.show', $lease->property) }}"
                   class="prop-link" onclick="event.stopPropagation()">
                  {{ $lease->property->name }}
                </a>
                <div class="ll-sub">{{ $lease->property->city }}, {{ strtoupper($lease->property->country_code) }}</div>
              @else
                <span style="color:var(--text-light)">—</span>
              @endif
            </td>
            <td>
              @if($lease->tenant)
                <div style="font-weight:500;color:var(--text-dark)">{{ $lease->tenant->first_name }} {{ $lease->tenant->last_name }}</div>
                <div class="ll-sub">{{ $lease->tenant->email }}</div>
              @else
                <span style="color:var(--text-light)">—</span>
              @endif
            </td>
            <td>
              @if($lease->property?->landlord)
                <a href="{{ route('admin.landlords.show', $lease->property->landlord) }}"
                   class="ll-link" style="font-size:13px;font-weight:500" onclick="event.stopPropagation()">
                  {{ $lease->property->landlord->first_name }} {{ $lease->property->landlord->last_name }}
                </a>
              @else
                <span style="color:var(--text-light)">—</span>
              @endif
            </td>
            <td style="text-align:center;font-size:12px;color:var(--text-mid)">
              {{ $isMulti ? $unitLabel : '—' }}
            </td>
            <td>
              <span class="lease-status-badge {{ $lease->status }}">{{ ucfirst($lease->status) }}</span>
            </td>
            <td style="text-align:right;font-weight:600;font-size:13px">
              {{ strtoupper($lease->currency_code) }} {{ number_format($lease->rent_minor_units / 100, 2) }}
            </td>
            <td style="text-align:center;font-size:13px;color:var(--text-mid)">
              {{ $lease->due_day }}
            </td>
            <td style="font-size:12px;color:var(--text-mid);white-space:nowrap">
              {{ $lease->activated_at ? \Carbon\Carbon::parse($lease->activated_at)->format('d M Y') : '—' }}
            </td>
            <td style="font-size:12px;color:var(--text-mid);white-space:nowrap">
              {{ $lease->end_date ? \Carbon\Carbon::parse($lease->end_date)->format('d M Y') : 'Open' }}
            </td>
            <td style="text-align:center">
              <div style="display:flex;gap:4px;justify-content:center;flex-wrap:wrap">
                @if($lease->failed_payment_count > 0)
                  <span class="pay-warn">✕ {{ $lease->failed_payment_count }}</span>
                @endif
                @if($lease->pending_payment_count > 0)
                  <span class="pay-pending">⏳ {{ $lease->pending_payment_count }}</span>
                @endif
                @if(!$hasAlert)
                  <span style="font-size:12px;color:var(--text-light)">{{ $lease->total_payment_count }} ok</span>
                @endif
              </div>
            </td>
            <td style="text-align:right" onclick="event.stopPropagation()">
              <a href="{{ $detailUrl }}" class="db-table-link" style="font-size:12px;white-space:nowrap">View →</a>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>

@push('scripts')
<script>
function filterLeases(filter) {
  document.querySelectorAll('.filter-tab').forEach(btn => {
    btn.classList.toggle('active', btn.dataset.filter === filter);
  });
  document.querySelectorAll('#leaseTable tbody tr').forEach(row => {
    let show = true;
    if (filter === 'active')     show = row.dataset.status === 'active';
    if (filter === 'expired')    show = row.dataset.status === 'expired';
    if (filter === 'terminated') show = row.dataset.status === 'terminated';
    if (filter === 'alerts')     show = row.dataset.alert  === '1';
    row.style.display = show ? '' : 'none';
  });
}
</script>
@endpush

@endsection
