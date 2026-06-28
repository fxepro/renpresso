@extends('admin.layout')
@section('title', 'Maintenance teams')
@section('page-title', 'Maintenance teams')
@section('breadcrumb', 'Operations')

@push('styles')
<style>
.listed-badge {
  display: inline-flex; font-size: 11px; font-weight: 600;
  padding: 2px 9px; border-radius: 20px;
}
.listed-badge.yes { background: #e8f5e9; color: #2e7d32; }
.listed-badge.no  { background: #f5f5f5; color: #9e9e9e; }
.star-rating { color: #f59e0b; font-size: 13px; font-weight: 700; }
.service-pill {
  display: inline-flex; font-size: 11px; font-weight: 500;
  padding: 1px 7px; border-radius: 20px; margin: 1px;
  background: var(--cream); border: 1px solid var(--cream-dark); color: var(--text-mid);
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

<p class="admin-portal-note">
  All maintenance service teams on the platform. Listed teams appear in the landlord directory.
  Teams with engaged landlords and invoices are generating commission revenue.
</p>

<div class="db-stats">
  <div class="db-stat terra">
    <div class="db-stat-label">Total teams</div>
    <div class="db-stat-value">{{ $stats['total'] }}</div>
    <div class="db-stat-sub">Registered</div>
  </div>
  <div class="db-stat green">
    <div class="db-stat-label">Listed</div>
    <div class="db-stat-value">{{ $stats['listed'] }}</div>
    <div class="db-stat-sub">In landlord directory</div>
  </div>
  <div class="db-stat">
    <div class="db-stat-label">Unlisted</div>
    <div class="db-stat-value">{{ $stats['unlisted'] }}</div>
    <div class="db-stat-sub">Not in directory</div>
  </div>
  <div class="db-stat">
    <div class="db-stat-label">Countries</div>
    <div class="db-stat-value">{{ $stats['countries'] }}</div>
    <div class="db-stat-sub">Coverage</div>
  </div>
</div>

<div class="db-card">
  <div class="db-card-header">
    <span class="db-card-title">Team directory ({{ $stats['total'] }})</span>
  </div>

  <div class="filter-tabs">
    @foreach(['all' => ['All', $stats['total']], 'listed' => ['Listed', $stats['listed']], 'unlisted' => ['Unlisted', $stats['unlisted']]] as $key => [$label, $cnt])
    <button class="filter-tab {{ $key === 'all' ? 'active' : '' }}"
            onclick="filterTeams('{{ $key }}')" data-filter="{{ $key }}">
      {{ $label }} <span style="opacity:.6;margin-left:3px">{{ $cnt }}</span>
    </button>
    @endforeach
  </div>

  <div class="db-card-body" style="padding:0;padding-top:14px">
    <div class="db-table-wrap">
      <table class="db-table" id="teamTable">
        <thead>
          <tr>
            <th>Team</th>
            <th>Owner</th>
            <th>Location</th>
            <th>Services</th>
            <th style="text-align:center">Listed</th>
            <th style="text-align:center">Rating</th>
            <th style="text-align:center">Landlords</th>
            <th style="text-align:center">Invoices</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @foreach($teams as $team)
          @php
            $detailUrl = route('admin.maintenance-teams.show', $team);
            $rating    = $team->averageRating();
          @endphp
          <tr onclick="window.location='{{ $detailUrl }}'"
              data-listed="{{ $team->is_listed ? 'listed' : 'unlisted' }}">
            <td>
              <a href="{{ $detailUrl }}" class="t-name" onclick="event.stopPropagation()">
                {{ $team->name }}
              </a>
              @if($team->description)
                <div class="t-sub" style="max-width:260px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                  {{ $team->description }}
                </div>
              @endif
            </td>
            <td>
              @if($team->owner)
                <div style="font-size:13px;font-weight:500;color:var(--text-dark)">
                  {{ $team->owner->first_name }} {{ $team->owner->last_name }}
                </div>
                <div class="t-sub">{{ $team->owner->email }}</div>
              @else
                <span style="color:var(--text-light)">—</span>
              @endif
            </td>
            <td>
              <div style="font-size:13px;font-weight:500">{{ $team->city }}</div>
              <div class="t-sub">{{ strtoupper($team->country_code) }}
                @if($team->cities_count > 1)
                  · +{{ $team->cities_count - 1 }} cities
                @endif
              </div>
            </td>
            <td>
              <div style="display:flex;flex-wrap:wrap;max-width:240px">
                @foreach($team->serviceList()->take(4) as $svc)
                  <span class="service-pill">{{ $svc }}</span>
                @endforeach
                @if($team->serviceList()->count() > 4)
                  <span class="service-pill" style="background:transparent;border-color:transparent;color:var(--text-light)">
                    +{{ $team->serviceList()->count() - 4 }}
                  </span>
                @endif
              </div>
            </td>
            <td style="text-align:center">
              <span class="listed-badge {{ $team->is_listed ? 'yes' : 'no' }}">
                {{ $team->is_listed ? 'Listed' : 'Unlisted' }}
              </span>
            </td>
            <td style="text-align:center">
              @if($rating)
                <span class="star-rating">★ {{ $rating }}</span>
                <div class="t-sub">{{ $team->reviews_count }} reviews</div>
              @else
                <span style="color:var(--text-light);font-size:13px">—</span>
              @endif
            </td>
            <td style="text-align:center;font-size:13px">
              {{ $team->engaged_landlords_count ?: '—' }}
            </td>
            <td style="text-align:center;font-size:13px">
              {{ $team->invoices_count ?: '—' }}
            </td>
            <td style="text-align:right" onclick="event.stopPropagation()">
              <a href="{{ $detailUrl }}" class="db-table-link" style="font-size:12px">View →</a>
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
function filterTeams(filter) {
  document.querySelectorAll('.filter-tab').forEach(b => b.classList.toggle('active', b.dataset.filter === filter));
  document.querySelectorAll('#teamTable tbody tr').forEach(row => {
    row.style.display = (filter === 'all' || row.dataset.listed === filter) ? '' : 'none';
  });
}
</script>
@endpush

@endsection
