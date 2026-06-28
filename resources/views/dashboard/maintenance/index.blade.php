@extends(auth()->user()->isMaintenance() ? 'dashboard.maintenance-portal.layout' : 'dashboard.layout')
@section('page-title', 'Maintenance requests')
@section('content')
@if($viewMode === 'staff')
  @if($linkedCount === 0)
    <div class="db-empty" style="min-height:50vh">
      <div class="db-empty-icon">🔗</div>
      <h3>No landlords have added your team yet</h3>
      <p>Your team is listed in the directory for your city. Landlords with properties there can find you under <strong>Maintenance team</strong> and add you to their roster.</p>
    </div>
  @elseif($requests->isEmpty())
    <div class="db-empty" style="min-height:50vh">
      <div class="db-empty-icon">🔧</div>
      <h3>No jobs assigned yet</h3>
      <p>You are on {{ $linkedCount }} landlord roster{{ $linkedCount === 1 ? '' : 's' }}. Jobs appear here after a landlord assigns your team on a maintenance request.</p>
    </div>
  @else
  <div class="db-card">
    <div class="db-table-wrap">
      <table class="db-table">
        <thead><tr><th>Property</th><th>Tenant</th><th>Title</th><th>Category</th><th>Submitted</th><th>Status</th><th></th><th></th></tr></thead>
        <tbody>
          @foreach($requests as $mr)
          <tr>
            <td>
              <div class="db-flag-name">
                <span class="db-flag">{{ config('countries.'.$mr->lease->property->country_code.'.flag','🏠') }}</span>
                <div class="db-name">{{ $mr->lease->property->name }}</div>
              </div>
            </td>
            <td>{{ $mr->raisedBy->first_name ?? '—' }}</td>
            <td>
              <strong>{{ $mr->title }}</strong>
              @if(! $mr->maintenance_team_id)
                <span class="badge badge-gold" style="margin-left:6px">New · unassigned</span>
              @endif
            </td>
            <td><span class="badge badge-navy">{{ ucfirst($mr->category) }}</span></td>
            <td>{{ $mr->created_at->format('d M Y') }}</td>
            <td>
              <span class="badge badge-{{ match($mr->status){'submitted'=>'terra','acknowledged'=>'gold','in_progress'=>'navy','resolved'=>'green',default=>'grey'} }}">
                {{ ucfirst(str_replace('_',' ',$mr->status)) }}
              </span>
            </td>
            <td>
              <form method="POST" action="{{ route('maintenance.update',$mr) }}" style="display:inline">
                @csrf @method('PATCH')
                <select name="status" class="db-select" style="font-size:12px;padding:4px 28px 4px 8px;width:auto" onchange="this.form.submit()">
                  @foreach(['submitted','acknowledged','in_progress','resolved'] as $s)
                    <option value="{{ $s }}" {{ $mr->status===$s?'selected':'' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                  @endforeach
                </select>
              </form>
            </td>
            <td><a href="{{ route('maintenance.show', $mr) }}" class="db-table-link">Open</a></td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
  @endif
@elseif($viewMode === 'tenant')
  @if($canCreate ?? false)
  <div style="margin-bottom:16px">
    <a href="{{ route('maintenance.create') }}" class="db-btn db-btn-primary" style="text-decoration:none">+ New request</a>
  </div>
  @endif
  @if($requests->isEmpty())
    <div class="db-empty" style="min-height:60vh">
      <div class="db-empty-icon">🔧</div>
      <h3>No maintenance requests yet</h3>
      <p>Report an issue for your home — add photos so your landlord can see what's wrong.</p>
      @if($canCreate ?? false)
        <a href="{{ route('maintenance.create') }}" class="db-btn db-btn-primary" style="margin-top:16px;text-decoration:none">Submit first request</a>
      @endif
    </div>
  @else
  <div class="db-card">
    <div class="db-table-wrap">
      <table class="db-table">
        <thead><tr><th>Property</th><th>Title</th><th>Category</th><th>Submitted</th><th>Status</th><th></th></tr></thead>
        <tbody>
          @foreach($requests as $mr)
          <tr>
            <td>
              <div class="db-flag-name">
                <span class="db-flag">{{ config('countries.'.$mr->lease->property->country_code.'.flag','🏠') }}</span>
                <div class="db-name">{{ $mr->lease->property->name }}</div>
              </div>
            </td>
            <td><strong>{{ $mr->title }}</strong></td>
            <td><span class="badge badge-navy">{{ ucfirst($mr->category) }}</span></td>
            <td>{{ $mr->created_at->format('d M Y') }}</td>
            <td>
              <span class="badge badge-{{ match($mr->status){'submitted'=>'terra','acknowledged'=>'gold','in_progress'=>'navy','resolved'=>'green',default=>'grey'} }}">
                {{ ucfirst(str_replace('_',' ',$mr->status)) }}
              </span>
            </td>
            <td style="white-space:nowrap">
              <a href="{{ route('maintenance.show', $mr) }}" class="db-table-link">Open</a>
              @can('updateDetails', $mr)
                · <a href="{{ route('maintenance.edit', $mr) }}" class="db-table-link">Edit</a>
              @endcan
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
  @endif
@else
@if(($unassignedCount ?? 0) > 0)
  <div class="db-alert" style="margin-bottom:16px;background:var(--gold-pale);color:var(--gold);border:1px solid rgba(201,150,58,0.25)">
    @if($engagedTeams->isEmpty())
      <strong>{{ $unassignedCount }} tenant request{{ $unassignedCount === 1 ? '' : 's' }}</strong> waiting —
      <a href="{{ route('landlord.maintenance-team.index', ['tab' => 'discover']) }}" style="color:inherit;font-weight:600">find a maintenance team</a>
      in your property cities, add them to your roster, then assign them on each request.
    @else
      <strong>{{ $unassignedCount }} request{{ $unassignedCount === 1 ? '' : 's' }}</strong> not assigned to a team yet —
      use the <strong>Assignee</strong> column below (only teams on your roster).
    @endif
  </div>
@endif
@if($requests->isEmpty())
  <div class="db-empty" style="min-height:60vh">
    <div class="db-empty-icon">🔧</div>
    <h3>No maintenance requests.</h3>
    <p>Requests submitted by tenants will appear here.</p>
  </div>
@else
<div class="db-card">
  <div class="db-table-wrap">
    <table class="db-table">
      <thead><tr><th>Property</th><th>Tenant</th><th>Title</th><th>Category</th><th>Assignee</th><th>Submitted</th><th>Status</th><th></th><th></th></tr></thead>
      <tbody>
        @foreach($requests as $mr)
        <tr>
          <td>
            <div class="db-flag-name">
              <span class="db-flag">{{ config('countries.'.$mr->lease->property->country_code.'.flag','🏠') }}</span>
              <div class="db-name">{{ $mr->lease->property->name }}</div>
            </div>
          </td>
          <td>{{ $mr->raisedBy->first_name ?? '—' }}</td>
          <td>
            <a href="{{ route('maintenance.show', $mr) }}" class="db-table-link">{{ $mr->title }}</a>
          </td>
          <td><span class="badge badge-navy">{{ ucfirst($mr->category) }}</span></td>
          <td>
            <form method="POST" action="{{ route('maintenance.assign',$mr) }}" style="display:inline">
              @csrf @method('PATCH')
              <select name="maintenance_team_id" class="db-select" style="font-size:12px;padding:4px 28px 4px 8px;width:auto;max-width:200px" onchange="this.form.submit()" title="Assign to a team">
                <option value="">— Unassigned —</option>
                @foreach($engagedTeams as $team)
                  <option value="{{ $team->id }}" {{ $mr->maintenance_team_id === $team->id ? 'selected' : '' }}>{{ $team->name }} · {{ $team->owner?->fullName() ?? $team->city }}</option>
                @endforeach
              </select>
            </form>
            @if($engagedTeams->isEmpty())
              <span class="db-sub" style="display:block;margin-top:4px"><a href="{{ route('landlord.maintenance-team.index') }}">Find teams</a> in your property cities.</span>
            @endif
          </td>
          <td>{{ $mr->created_at->format('d M Y') }}</td>
          <td>
            <span class="badge badge-{{ match($mr->status){'submitted'=>'terra','acknowledged'=>'gold','in_progress'=>'navy','resolved'=>'green',default=>'grey'} }}">
              {{ ucfirst(str_replace('_',' ',$mr->status)) }}
            </span>
          </td>
          <td>
            <form method="POST" action="{{ route('maintenance.update',$mr) }}" style="display:inline">
              @csrf @method('PATCH')
              <select name="status" class="db-select" style="font-size:12px;padding:4px 28px 4px 8px;width:auto" onchange="this.form.submit()">
                @foreach(['submitted','acknowledged','in_progress','resolved'] as $s)
                  <option value="{{ $s }}" {{ $mr->status===$s?'selected':'' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                @endforeach
              </select>
            </form>
          </td>
          <td><a href="{{ route('maintenance.show', $mr) }}" class="db-table-link">Open</a></td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endif
@endif
@endsection
