@extends('admin.layout')
@section('title', 'Tenants')
@section('page-title', 'Tenants')
@section('breadcrumb', 'Operations')

@push('styles')
<style>
.tenant-status {
  display: inline-flex; align-items: center; gap: 5px;
  font-size: 11px; font-weight: 600; padding: 2px 9px;
  border-radius: 20px; white-space: nowrap;
}
.tenant-status.leased  { background: #e8f5e9; color: #2e7d32; }
.tenant-status.no-lease{ background: #fce4ec; color: #b71c1c; }
.pay-warn {
  display: inline-flex; font-size: 11px; font-weight: 600;
  padding: 2px 7px; border-radius: 20px;
  background: #fce4ec; color: #b71c1c;
}
.t-name { font-weight: 600; color: var(--text-dark); text-decoration: none; }
.t-name:hover { color: var(--terra); }
.t-sub  { font-size: 12px; color: var(--text-light); margin-top: 1px; }
.prop-link { font-size: 12px; color: var(--text-mid); text-decoration: none; }
.prop-link:hover { color: var(--terra); }
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

<p class="admin-portal-note">All registered tenants. Tenants with no active lease or failed payments are support and retention priorities.</p>

<div class="db-stats">
  <div class="db-stat terra">
    <div class="db-stat-label">Total tenants</div>
    <div class="db-stat-value">{{ $stats['total'] }}</div>
    <div class="db-stat-sub">Registered accounts</div>
  </div>
  <div class="db-stat green">
    <div class="db-stat-label">Active lease</div>
    <div class="db-stat-value">{{ $stats['with_lease'] }}</div>
    <div class="db-stat-sub">Currently renting</div>
  </div>
  <div class="db-stat {{ $stats['no_lease'] > 0 ? '' : 'green' }}">
    <div class="db-stat-label">No active lease</div>
    <div class="db-stat-value" style="{{ $stats['no_lease'] > 0 ? 'color:#b71c1c' : '' }}">
      {{ $stats['no_lease'] }}
    </div>
    <div class="db-stat-sub">Churn risk · follow up</div>
  </div>
  <div class="db-stat {{ $stats['payment_alerts'] > 0 ? '' : 'green' }}">
    <div class="db-stat-label">Payment alerts</div>
    <div class="db-stat-value" style="{{ $stats['payment_alerts'] > 0 ? 'color:#b71c1c' : '' }}">
      {{ $stats['payment_alerts'] }}
    </div>
    <div class="db-stat-sub">Tenants with failed payments</div>
  </div>
</div>

<div class="db-card">
  <div class="db-card-header">
    <span class="db-card-title">Tenant register ({{ $stats['total'] }})</span>
  </div>

  <div class="filter-tabs">
    @foreach([
      'all'     => ['All',            $stats['total']],
      'leased'  => ['Active lease',   $stats['with_lease']],
      'no-lease'=> ['No lease',       $stats['no_lease']],
      'alerts'  => ['Payment alerts', $stats['payment_alerts']],
    ] as $key => [$label, $cnt])
    <button class="filter-tab {{ $key === 'all' ? 'active' : '' }}"
            onclick="filterTenants('{{ $key }}')" data-filter="{{ $key }}">
      {{ $label }} <span style="opacity:.6;margin-left:3px">{{ $cnt }}</span>
    </button>
    @endforeach
  </div>

  <div class="db-card-body" style="padding:0;padding-top:14px">
    <div class="db-table-wrap">
      <table class="db-table" id="tenantTable">
        <thead>
          <tr>
            <th>Tenant</th>
            <th>Lease status</th>
            <th>Property</th>
            <th>Landlord</th>
            <th style="text-align:right">Rent / mo</th>
            <th>Country</th>
            <th>Since</th>
            <th style="text-align:center">Payments</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @foreach($tenants as $tenant)
          @php
            $detailUrl   = route('admin.tenants.show', $tenant);
            $hasLease    = $tenant->active_lease_count > 0;
            $lease       = $tenant->leases->first();
            $failedCount = $failedByTenant[$tenant->id] ?? 0;
            $filterClass = $hasLease ? 'leased' : 'no-lease';
          @endphp
          <tr onclick="window.location='{{ $detailUrl }}'"
              data-filter="{{ $filterClass }}"
              data-alert="{{ $failedCount > 0 ? '1' : '0' }}">
            <td>
              <a href="{{ $detailUrl }}" class="t-name" onclick="event.stopPropagation()">
                {{ $tenant->first_name }} {{ $tenant->last_name }}
              </a>
              <div class="t-sub">{{ $tenant->email }}</div>
            </td>
            <td>
              <span class="tenant-status {{ $filterClass }}">
                {{ $hasLease ? 'Active' : 'No lease' }}
              </span>
            </td>
            <td>
              @if($lease?->property)
                <a href="{{ route('admin.properties.show', $lease->property) }}"
                   class="prop-link" onclick="event.stopPropagation()">
                  {{ $lease->property->name }}
                </a>
                <div class="t-sub">{{ $lease->property->city }}, {{ strtoupper($lease->property->country_code) }}</div>
              @else
                <span style="color:var(--text-light);font-size:13px">—</span>
              @endif
            </td>
            <td>
              @if($lease?->property?->landlord)
                <a href="{{ route('admin.landlords.show', $lease->property->landlord) }}"
                   class="prop-link" style="font-weight:500" onclick="event.stopPropagation()">
                  {{ $lease->property->landlord->first_name }} {{ $lease->property->landlord->last_name }}
                </a>
              @else
                <span style="color:var(--text-light);font-size:13px">—</span>
              @endif
            </td>
            <td style="text-align:right;font-weight:600;font-size:13px">
              @if($lease)
                {{ strtoupper($lease->currency_code) }} {{ number_format($lease->rent_minor_units / 100, 2) }}
              @else
                <span style="color:var(--text-light)">—</span>
              @endif
            </td>
            <td style="font-size:13px">
              {{ $lease?->property?->country_code ? strtoupper($lease->property->country_code) : (strtoupper($tenant->home_country ?? '—')) }}
            </td>
            <td style="font-size:12px;color:var(--text-light);white-space:nowrap">
              {{ $tenant->created_at?->format('d M Y') ?? '—' }}
            </td>
            <td style="text-align:center">
              @if($failedCount > 0)
                <span class="pay-warn">✕ {{ $failedCount }} failed</span>
              @elseif($hasLease)
                <span style="font-size:12px;color:var(--text-light)">OK</span>
              @else
                <span style="color:var(--text-light)">—</span>
              @endif
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
function filterTenants(filter) {
  document.querySelectorAll('.filter-tab').forEach(btn => {
    btn.classList.toggle('active', btn.dataset.filter === filter);
  });
  document.querySelectorAll('#tenantTable tbody tr').forEach(row => {
    let show = true;
    if (filter === 'leased')   show = row.dataset.filter === 'leased';
    if (filter === 'no-lease') show = row.dataset.filter === 'no-lease';
    if (filter === 'alerts')   show = row.dataset.alert  === '1';
    row.style.display = show ? '' : 'none';
  });
}
</script>
@endpush

@endsection
