@extends('admin.layout')
@section('title', 'Maintenance requests')
@section('page-title', 'Maintenance requests')
@section('breadcrumb', 'Operations')

@push('styles')
<style>
.req-status {
  display: inline-flex; font-size: 11px; font-weight: 600;
  padding: 2px 9px; border-radius: 20px; white-space: nowrap;
}
.req-status.submitted   { background: #fff8e1; color: #f57f17; }
.req-status.acknowledged{ background: #e3f2fd; color: #1565c0; }
.req-status.in_progress { background: #e8f5e9; color: #2e7d32; }
.req-status.resolved    { background: #f5f5f5; color: #757575; }
.req-status.closed      { background: #f5f5f5; color: #9e9e9e; }
.cat-chip {
  display: inline-flex; font-size: 11px; font-weight: 600;
  padding: 2px 8px; border-radius: 20px;
  background: var(--cream); color: var(--text-mid); border: 1px solid var(--cream-dark);
}
.warn-chip {
  display: inline-flex; font-size: 11px; font-weight: 600;
  padding: 2px 8px; border-radius: 20px;
  background: #fce4ec; color: #b71c1c;
}
.t-name { font-weight: 600; color: var(--text-dark); text-decoration: none; }
.t-name:hover { color: var(--terra); }
.t-sub  { font-size: 12px; color: var(--text-light); margin-top: 1px; }
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

<p class="admin-portal-note">All maintenance requests raised by tenants. Unassigned requests need a team allocation.</p>

<div class="db-stats">
  <div class="db-stat terra">
    <div class="db-stat-label">Total requests</div>
    <div class="db-stat-value">{{ $stats['total'] }}</div>
    <div class="db-stat-sub">All time</div>
  </div>
  <div class="db-stat {{ $stats['submitted'] > 0 ? '' : 'green' }}">
    <div class="db-stat-label">Submitted</div>
    <div class="db-stat-value" style="{{ $stats['submitted'] > 0 ? 'color:#f57f17' : '' }}">
      {{ $stats['submitted'] }}
    </div>
    <div class="db-stat-sub">Awaiting acknowledgement</div>
  </div>
  <div class="db-stat">
    <div class="db-stat-label">In progress</div>
    <div class="db-stat-value">{{ $stats['acknowledged'] + $stats['in_progress'] }}</div>
    <div class="db-stat-sub">{{ $stats['acknowledged'] }} acknowledged · {{ $stats['in_progress'] }} active</div>
  </div>
  <div class="db-stat {{ $stats['unassigned'] > 0 ? '' : 'green' }}">
    <div class="db-stat-label">Unassigned</div>
    <div class="db-stat-value" style="{{ $stats['unassigned'] > 0 ? 'color:#b71c1c' : '' }}">
      {{ $stats['unassigned'] }}
    </div>
    <div class="db-stat-sub">No team allocated</div>
  </div>
</div>

<div class="db-card">
  <div class="db-card-header">
    <span class="db-card-title">Request log ({{ $stats['total'] }})</span>
  </div>

  <div class="filter-tabs">
    @foreach([
      'all'         => ['All',         $stats['total']],
      'submitted'   => ['Submitted',   $stats['submitted']],
      'acknowledged'=> ['Acknowledged',$stats['acknowledged']],
      'in_progress' => ['In progress', $stats['in_progress']],
      'resolved'    => ['Resolved',    $stats['resolved']],
      'unassigned'  => ['Unassigned',  $stats['unassigned']],
    ] as $key => [$label, $cnt])
    <button class="filter-tab {{ $key === 'all' ? 'active' : '' }}"
            onclick="filterReqs('{{ $key }}')" data-filter="{{ $key }}">
      {{ $label }} <span style="opacity:.6;margin-left:3px">{{ $cnt }}</span>
    </button>
    @endforeach
  </div>

  <div class="db-card-body" style="padding:0;padding-top:14px">
    @if($requests->isEmpty())
      <div class="db-empty" style="padding:40px 20px">
        <div class="db-empty-icon">🛠️</div>
        <h3>No maintenance requests yet</h3>
        <p>Requests raised by tenants will appear here.</p>
      </div>
    @else
    <div class="db-table-wrap">
      <table class="db-table" id="reqTable">
        <thead>
          <tr>
            <th>Request</th>
            <th>Category</th>
            <th>Property</th>
            <th>Tenant</th>
            <th>Team</th>
            <th>Status</th>
            <th>Follow-ups</th>
            <th style="text-align:right">Raised</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @foreach($requests as $req)
          @php
            $detailUrl  = route('admin.maintenance-requests.show', $req);
            $property   = $req->lease?->property;
            $tenant     = $req->raisedBy;
            $isUnassigned = ! $req->maintenanceTeam;
            $age        = $req->created_at->diffForHumans();
          @endphp
          <tr onclick="window.location='{{ $detailUrl }}'"
              data-status="{{ $req->status }}"
              data-unassigned="{{ $isUnassigned ? '1' : '0' }}">
            <td>
              <a href="{{ $detailUrl }}" class="t-name" onclick="event.stopPropagation()">
                {{ $req->title }}
              </a>
              @if($req->resolved_at)
                <div class="t-sub">Resolved {{ $req->resolved_at->format('d M Y') }}</div>
              @elseif($req->acknowledged_at)
                <div class="t-sub">Ack. {{ $req->acknowledged_at->format('d M Y') }}</div>
              @endif
            </td>
            <td>
              <span class="cat-chip">{{ ucfirst($req->category ?? '—') }}</span>
            </td>
            <td>
              @if($property)
                <a href="{{ route('admin.properties.show', $property) }}"
                   class="t-name" style="font-weight:500;font-size:13px" onclick="event.stopPropagation()">
                  {{ $property->name }}
                </a>
                <div class="t-sub">{{ $property->city }}, {{ strtoupper($property->country_code) }}</div>
              @else
                <span style="color:var(--text-light)">—</span>
              @endif
            </td>
            <td>
              @if($tenant)
                <div style="font-size:13px;font-weight:500;color:var(--text-dark)">
                  {{ $tenant->first_name }} {{ $tenant->last_name }}
                </div>
                <div class="t-sub">{{ $tenant->email }}</div>
              @else
                <span style="color:var(--text-light)">—</span>
              @endif
            </td>
            <td>
              @if($req->maintenanceTeam)
                <span style="font-size:13px;color:var(--text-mid)">{{ $req->maintenanceTeam->name }}</span>
              @else
                <span class="warn-chip">Unassigned</span>
              @endif
            </td>
            <td>
              <span class="req-status {{ $req->status }}">
                {{ ucwords(str_replace('_', ' ', $req->status)) }}
              </span>
            </td>
            <td style="text-align:center;font-size:13px;color:var(--text-mid)">
              {{ $req->follow_ups_count ?: '—' }}
            </td>
            <td style="text-align:right;font-size:12px;color:var(--text-light);white-space:nowrap">
              {{ $age }}
            </td>
            <td style="text-align:right" onclick="event.stopPropagation()">
              <a href="{{ $detailUrl }}" class="db-table-link" style="font-size:12px">View →</a>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    @endif
  </div>
</div>

@push('scripts')
<script>
function filterReqs(filter) {
  document.querySelectorAll('.filter-tab').forEach(btn => {
    btn.classList.toggle('active', btn.dataset.filter === filter);
  });
  document.querySelectorAll('#reqTable tbody tr').forEach(row => {
    let show = true;
    if (filter === 'submitted')    show = row.dataset.status === 'submitted';
    if (filter === 'acknowledged') show = row.dataset.status === 'acknowledged';
    if (filter === 'in_progress')  show = row.dataset.status === 'in_progress';
    if (filter === 'resolved')     show = row.dataset.status === 'resolved';
    if (filter === 'unassigned')   show = row.dataset.unassigned === '1';
    row.style.display = show ? '' : 'none';
  });
}
</script>
@endpush

@endsection
