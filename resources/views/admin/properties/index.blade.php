@extends('admin.layout')
@section('title', 'Properties')
@section('page-title', 'Properties')
@section('breadcrumb', 'Operations')

@push('styles')
<style>
.mode-chip {
  display: inline-flex;
  align-items: center;
  font-size: 11px;
  font-weight: 600;
  padding: 2px 8px;
  border-radius: 20px;
  background: var(--cream);
  color: var(--text-mid);
  border: 1px solid var(--cream-dark);
}
.mode-chip.multi { background: #e3f2fd; color: #1565c0; border-color: #90caf9; }
.occ-dot {
  display: inline-block;
  width: 7px; height: 7px;
  border-radius: 50%;
  margin-right: 5px;
  background: #9e9e9e;
}
.occ-dot.leased  { background: #43a047; }
.occ-dot.vacant  { background: #e0e0e0; }
.prop-name { font-weight: 600; color: var(--text-dark); text-decoration: none; }
.prop-name:hover { color: var(--terra); }
.prop-addr { font-size: 12px; color: var(--text-light); margin-top: 1px; }
.ll-link { font-size: 12px; color: var(--text-mid); text-decoration: none; }
.ll-link:hover { color: var(--terra); }
.db-table tbody tr { cursor: pointer; }
.db-table tbody tr:hover td { background: var(--cream); }

/* filter tabs */
.prop-filter-tabs { display: flex; gap: 4px; padding: 14px 20px 0; }
.prop-tab {
  font-size: 12px; font-weight: 600;
  padding: 5px 14px; border-radius: 20px; cursor: pointer;
  border: 1px solid var(--cream-dark); background: var(--white); color: var(--text-mid);
}
.prop-tab.active { background: var(--text-dark); color: var(--white); border-color: var(--text-dark); }
.prop-tab:hover:not(.active) { background: var(--cream); }
</style>
@endpush

@section('content')

<p class="admin-portal-note">All properties across all landlord accounts. Click any row to see full details.</p>

<div class="db-stats">
  <div class="db-stat terra">
    <div class="db-stat-label">Total properties</div>
    <div class="db-stat-value">{{ $stats['total'] }}</div>
    <div class="db-stat-sub">Across all landlords</div>
  </div>
  <div class="db-stat">
    <div class="db-stat-label">Single-unit</div>
    <div class="db-stat-value">{{ $stats['single'] }}</div>
    <div class="db-stat-sub">Individual units</div>
  </div>
  <div class="db-stat">
    <div class="db-stat-label">Multi-unit</div>
    <div class="db-stat-value">{{ $stats['multi'] }}</div>
    <div class="db-stat-sub">Buildings</div>
  </div>
  <div class="db-stat">
    <div class="db-stat-label">Active leases</div>
    <div class="db-stat-value">{{ $stats['active_leases'] }}</div>
    <div class="db-stat-sub">{{ $stats['vacant'] }} {{ Str::plural('property', $stats['vacant']) }} vacant</div>
  </div>
</div>

<div class="db-card">
  <div class="db-card-header">
    <span class="db-card-title">Property catalog ({{ $stats['total'] }})</span>
  </div>

  @php
    $filterSets = [
      'all'    => ['label' => 'All',        'props' => $properties],
      'single' => ['label' => 'Single-unit', 'props' => $properties->where('occupancy_mode','single')],
      'multi'  => ['label' => 'Multi-unit',  'props' => $properties->where('occupancy_mode','multi')],
      'vacant' => ['label' => 'Vacant',      'props' => $properties->where('active_lease_count', 0)],
    ];
  @endphp

  <div class="prop-filter-tabs">
    @foreach($filterSets as $key => $set)
      <button class="prop-tab {{ $key === 'all' ? 'active' : '' }}"
              onclick="filterProps('{{ $key }}')" data-filter="{{ $key }}">
        {{ $set['label'] }} <span style="opacity:.6;margin-left:3px">{{ $set['props']->count() }}</span>
      </button>
    @endforeach
  </div>

  <div class="db-card-body" style="padding:0;padding-top:14px">
    <div class="db-table-wrap">
      <table class="db-table" id="propTable">
        <thead>
          <tr>
            <th>Property</th>
            <th>Landlord</th>
            <th>Mode</th>
            <th>Type</th>
            <th>Country</th>
            <th>Currency</th>
            <th style="text-align:center">Capacity</th>
            <th style="text-align:center">Active leases</th>
            <th>Listing</th>
            <th style="text-align:right">Added</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @foreach($properties as $prop)
          @php
            $detailUrl   = route('admin.properties.show', $prop);
            $hasLeases   = $prop->active_lease_count > 0;
            $capacity    = $prop->occupancy_mode === 'multi' ? ($prop->unit_capacity ?? '—') : 1;
            $typeLabel   = match($prop->type) {
              'apartment'  => 'Apartment',
              'house'      => 'House',
              'commercial' => 'Commercial',
              default      => ucfirst($prop->type ?? '—'),
            };
          @endphp
          <tr onclick="window.location='{{ $detailUrl }}'"
              data-mode="{{ $prop->occupancy_mode }}"
              data-vacant="{{ $hasLeases ? '0' : '1' }}">
            <td>
              <div style="display:flex;align-items:center;gap:6px">
                <span class="occ-dot {{ $hasLeases ? 'leased' : 'vacant' }}"></span>
                <div>
                  <a href="{{ $detailUrl }}" class="prop-name" onclick="event.stopPropagation()">{{ $prop->name }}</a>
                  <div class="prop-addr">{{ $prop->address_line1 }}{{ $prop->city ? ', '.$prop->city : '' }}</div>
                </div>
              </div>
            </td>
            <td>
              @if($prop->landlord)
                <a href="{{ route('admin.landlords.show', $prop->landlord) }}" class="ll-link" onclick="event.stopPropagation()">
                  {{ $prop->landlord->first_name }} {{ $prop->landlord->last_name }}
                </a>
              @else
                <span style="color:var(--text-light)">—</span>
              @endif
            </td>
            <td>
              <span class="mode-chip {{ $prop->occupancy_mode === 'multi' ? 'multi' : '' }}">
                {{ $prop->occupancy_mode === 'multi' ? 'Multi' : 'Single' }}
              </span>
            </td>
            <td style="font-size:13px;color:var(--text-mid)">{{ $typeLabel }}</td>
            <td style="font-size:13px">{{ strtoupper($prop->country_code ?? '—') }}</td>
            <td style="font-size:13px;color:var(--text-mid)">{{ strtoupper($prop->currency_code ?? '—') }}</td>
            <td style="text-align:center;font-size:13px">{{ $capacity }}</td>
            <td style="text-align:center">
              @if($hasLeases)
                <span style="font-weight:600;color:var(--text-dark)">{{ $prop->active_lease_count }}</span>
              @else
                <span style="color:var(--text-light)">—</span>
              @endif
            </td>
            <td style="font-size:12px;color:var(--text-mid)">
              {{ $prop->listing_visibility === 'public' ? 'Public' : 'Private' }}
            </td>
            <td style="text-align:right;font-size:12px;color:var(--text-light);white-space:nowrap">
              {{ $prop->created_at?->format('d M Y') ?? '—' }}
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
function filterProps(filter) {
  document.querySelectorAll('.prop-tab').forEach(btn => {
    btn.classList.toggle('active', btn.dataset.filter === filter);
  });
  document.querySelectorAll('#propTable tbody tr').forEach(row => {
    let show = true;
    if (filter === 'single')  show = row.dataset.mode    === 'single';
    if (filter === 'multi')   show = row.dataset.mode    === 'multi';
    if (filter === 'vacant')  show = row.dataset.vacant  === '1';
    row.style.display = show ? '' : 'none';
  });
}
</script>
@endpush

@endsection
