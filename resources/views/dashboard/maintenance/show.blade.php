@extends(auth()->user()->isMaintenance() ? 'dashboard.maintenance-portal.layout' : 'dashboard.layout')
@section('page-title', 'Maintenance request')
@section('breadcrumb')
  <a href="{{ route('maintenance.index') }}" class="db-breadcrumb">← Maintenance requests</a>
@endsection

@push('styles')
<style>
.mr-hero { background:var(--white); border:1px solid var(--cream-dark); border-radius:var(--radius); padding:24px 28px; margin-bottom:20px; display:grid; grid-template-columns:1fr auto; gap:20px; align-items:start; }
@media(max-width:768px){ .mr-hero { grid-template-columns:1fr; } }
.mr-hero h1 { font-family:'Fraunces',serif; font-size:var(--fs-heading); font-weight:500; margin-bottom:8px; }
.mr-meta { font-size:var(--fs-step); color:var(--text-light); margin-bottom:12px; }
.mr-grid { display:grid; grid-template-columns:1fr 320px; gap:20px; align-items:start; }
@media(max-width:900px){ .mr-grid { grid-template-columns:1fr; } }
.mr-timeline { display:flex; flex-direction:column; gap:14px; }
.mr-entry { background:var(--white); border:1px solid var(--cream-dark); border-radius:var(--radius); padding:20px 22px; }
.mr-entry-original { border-left:4px solid var(--terra); }
.mr-entry-head { display:flex; justify-content:space-between; align-items:flex-start; gap:12px; margin-bottom:10px; flex-wrap:wrap; }
.mr-entry-badge { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--text-light); }
.mr-entry-author { font-weight:600; color:var(--text-dark); }
.mr-entry-role { font-size:var(--fs-step); color:var(--text-light); }
.mr-body { color:var(--text-mid); line-height:1.6; font-size:var(--fs-body); }
.mr-photos { display:flex; flex-wrap:wrap; gap:10px; margin-top:14px; }
.mr-photos img { max-width:180px; max-height:180px; object-fit:cover; border-radius:8px; border:1px solid var(--cream-dark); }
.mr-side-card .db-card-body { padding:20px; }
</style>
@endpush

@section('content')
@php
  $lease = $maintenanceRequest->lease;
  $prop = $lease->property;
  $tenantUser = $lease->tenant;
@endphp

<div class="mr-hero">
  <div>
    <h1>{{ $maintenanceRequest->title }}</h1>
    <div class="mr-meta">
      <span class="badge badge-navy">{{ ucfirst($maintenanceRequest->category) }}</span>
      <span class="badge badge-{{ match($maintenanceRequest->status){'submitted'=>'terra','acknowledged'=>'gold','in_progress'=>'navy','resolved'=>'green',default=>'grey'} }}">{{ ucfirst(str_replace('_',' ', $maintenanceRequest->status)) }}</span>
      · {{ $prop->name }} · {{ $prop->city }}, {{ $prop->country_code }}
    </div>
    <p class="mr-body" style="margin:0">Submitted {{ $maintenanceRequest->created_at->format('d M Y H:i') }} by {{ $maintenanceRequest->raisedBy->fullName() }}</p>
    @if($canEdit ?? false)
      <div style="margin-top:14px;display:flex;flex-wrap:wrap;gap:10px">
        <a href="{{ route('maintenance.edit', $maintenanceRequest) }}" class="db-btn db-btn-ghost" style="text-decoration:none;font-size:13px">Edit request</a>
        @if($canDelete ?? false)
          <form method="POST" action="{{ route('maintenance.destroy', $maintenanceRequest) }}" onsubmit="return confirm('Delete this request?');" style="display:inline">
            @csrf @method('DELETE')
            <button type="submit" class="db-btn db-btn-ghost" style="font-size:13px;color:var(--red)">Delete</button>
          </form>
        @endif
      </div>
    @endif
  </div>
  @if(auth()->user()->isLandlord() && auth()->id() === $prop->landlord_id)
    <div style="min-width:200px">
      <form method="POST" action="{{ route('maintenance.assign', $maintenanceRequest) }}" class="db-form" style="gap:10px;max-width:none">
        @csrf @method('PATCH')
        <label class="db-form-group" style="margin:0">
          <span class="db-form-hint">Assign team</span>
          <select name="maintenance_team_id" class="db-select" onchange="this.form.submit()">
            <option value="">— Unassigned —</option>
            @foreach(auth()->user()->engagedMaintenanceTeams()->with('owner')->orderBy('name')->get() as $team)
              <option value="{{ $team->id }}" {{ $maintenanceRequest->maintenance_team_id === $team->id ? 'selected' : '' }}>{{ $team->name }} · {{ $team->owner?->fullName() }}</option>
            @endforeach
          </select>
        </label>
      </form>
      <form method="POST" action="{{ route('maintenance.update', $maintenanceRequest) }}" class="db-form" style="gap:10px;margin-top:12px;max-width:none">
        @csrf @method('PATCH')
        <label class="db-form-group" style="margin:0">
          <span class="db-form-hint">Status</span>
          <select name="status" class="db-select" onchange="this.form.submit()">
            @foreach(['submitted','acknowledged','in_progress','resolved'] as $s)
              <option value="{{ $s }}" {{ $maintenanceRequest->status === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ', $s)) }}</option>
            @endforeach
          </select>
        </label>
      </form>
    </div>
  @elseif(auth()->user()->isMaintenance() && auth()->user()->can('update', $maintenanceRequest))
    <div style="min-width:200px">
      <form method="POST" action="{{ route('maintenance.update', $maintenanceRequest) }}" class="db-form" style="gap:10px;max-width:none">
        @csrf @method('PATCH')
        <label class="db-form-group" style="margin:0">
          <span class="db-form-hint">Update status</span>
          <select name="status" class="db-select" onchange="this.form.submit()">
            @foreach(['submitted','acknowledged','in_progress','resolved'] as $s)
              <option value="{{ $s }}" {{ $maintenanceRequest->status === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ', $s)) }}</option>
            @endforeach
          </select>
        </label>
      </form>
    </div>
  @endif
</div>

<div class="mr-grid">
  <div>
    <h2 class="db-card-title" style="margin-bottom:14px;font-size:var(--fs-title)">Activity</h2>
    <div class="mr-timeline">
      <article class="mr-entry mr-entry-original">
        <div class="mr-entry-head">
          <div>
            <span class="mr-entry-badge">Original request</span>
            <div class="mr-entry-author">{{ $maintenanceRequest->raisedBy->fullName() }}</div>
            <div class="mr-entry-role">{{ $maintenanceRequest->created_at->format('d M Y, H:i') }}</div>
          </div>
        </div>
        <div class="mr-body">{!! nl2br(e($maintenanceRequest->description)) !!}</div>
        @if($maintenanceRequest->documents->isNotEmpty())
          <div class="mr-photos">
            @foreach($maintenanceRequest->documents as $doc)
              @if(str_starts_with((string) $doc->mime_type, 'image/'))
                <a href="{{ route('documents.file', $doc) }}" target="_blank" rel="noopener">
                  <img src="{{ route('documents.file', $doc) }}" alt="{{ $doc->original_filename }}">
                </a>
              @endif
            @endforeach
          </div>
        @endif
      </article>

      @foreach($maintenanceRequest->followUps as $fu)
        @php
          $roleLabel = $fu->author->id === $tenantUser->id ? 'Tenant' : ($fu->author->isMaintenance() ? 'Maintenance team' : 'User');
        @endphp
        <article class="mr-entry">
          <div class="mr-entry-head">
            <div>
              <span class="mr-entry-badge">Update</span>
              <div class="mr-entry-author">{{ $fu->author->fullName() }} <span class="mr-entry-role">· {{ $roleLabel }}</span></div>
              <div class="mr-entry-role">{{ $fu->created_at->format('d M Y, H:i') }}</div>
            </div>
          </div>
          @if($fu->body)
            <div class="mr-body">{!! nl2br(e($fu->body)) !!}</div>
          @endif
          @if($fu->documents->isNotEmpty())
            <div class="mr-photos">
              @foreach($fu->documents as $doc)
                @if(str_starts_with((string) $doc->mime_type, 'image/'))
                  <a href="{{ route('documents.file', $doc) }}" target="_blank" rel="noopener">
                    <img src="{{ route('documents.file', $doc) }}" alt="{{ $doc->original_filename }}">
                  </a>
                @else
                  <a href="{{ route('documents.file', $doc) }}" class="db-btn db-btn-ghost" style="font-size:13px">{{ $doc->original_filename }}</a>
                @endif
              @endforeach
            </div>
          @endif
        </article>
      @endforeach
    </div>
  </div>

  <div class="mr-side-card db-card">
    <div class="db-card-header"><h2 class="db-card-title">Details</h2></div>
    <div class="db-card-body">
      <p class="db-form-hint" style="margin-bottom:12px"><strong>Property</strong><br>{{ $prop->name }}</p>
      <p class="db-form-hint" style="margin-bottom:12px"><strong>Tenant</strong><br>{{ $tenantUser->fullName() }}</p>
      @if($maintenanceRequest->maintenanceTeam)
        <p class="db-form-hint" style="margin-bottom:12px"><strong>Assigned team</strong><br>
          @if(auth()->user()->isLandlord())
            <a href="{{ route('landlord.maintenance-team.show', $maintenanceRequest->maintenanceTeam) }}">{{ $maintenanceRequest->maintenanceTeam->name }}</a>
          @else
            {{ $maintenanceRequest->maintenanceTeam->name }}
          @endif
        </p>
      @endif
      @if($maintenanceRequest->resolution_notes)
        <p class="db-form-hint" style="margin-bottom:0"><strong>Resolution notes</strong><br>{{ $maintenanceRequest->resolution_notes }}</p>
      @endif
    </div>

    @if($canFollowUp)
      <div class="db-card-header" style="border-top:1px solid var(--cream-dark)"><h2 class="db-card-title">Add update</h2></div>
      <div class="db-card-body">
        <p class="db-form-hint" style="margin-bottom:14px">Add more details and/or photos. Everyone with access to this request will see them.</p>
        <form method="POST" action="{{ route('maintenance.follow-up', $maintenanceRequest) }}" class="db-form" enctype="multipart/form-data" style="max-width:none">
          @csrf
          <div class="db-form-group">
            <label for="fu_body">Message</label>
            <textarea class="db-textarea" id="fu_body" name="body" rows="4" placeholder="Describe what changed, access instructions, etc.">{{ old('body') }}</textarea>
            @error('body')<span class="db-form-error">{{ $message }}</span>@enderror
          </div>
          <div class="db-form-group">
            <label for="fu_photos">Photos</label>
            <input type="file" class="db-input" id="fu_photos" name="photos[]" accept="image/*" multiple>
            @error('photos')<span class="db-form-error">{{ $message }}</span>@enderror
            @error('photos.*')<span class="db-form-error">{{ $message }}</span>@enderror
          </div>
          <button type="submit" class="db-form-submit">Submit update</button>
        </form>
      </div>
    @endif
  </div>
</div>
@endsection
