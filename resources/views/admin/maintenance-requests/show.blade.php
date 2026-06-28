@extends('admin.layout')
@section('title', $request->title)
@section('page-title', $request->title)
@section('breadcrumb', 'Maintenance requests')

@section('topbar-actions')
  <a href="{{ route('admin.maintenance-requests') }}" class="db-btn db-btn-ghost" style="text-decoration:none">← All requests</a>
  @if($request->lease?->property)
    <a href="{{ route('admin.properties.show', $request->lease->property) }}" class="db-btn db-btn-ghost" style="text-decoration:none">Property</a>
  @endif
@endsection

@push('styles')
<style>
.req-status {
  display: inline-flex; font-size: 12px; font-weight: 600;
  padding: 3px 11px; border-radius: 20px;
}
.req-status.submitted   { background: #fff8e1; color: #f57f17; }
.req-status.acknowledged{ background: #e3f2fd; color: #1565c0; }
.req-status.in_progress { background: #e8f5e9; color: #2e7d32; }
.req-status.resolved    { background: #f5f5f5; color: #757575; }
.req-status.closed      { background: #f5f5f5; color: #9e9e9e; }
.info-row {
  display: flex; align-items: baseline; gap: 12px;
  padding: 10px 24px; border-bottom: 1px solid var(--cream-dark); font-size: 14px;
}
.info-row:last-child { border-bottom: none; }
.info-label {
  font-size: 11px; font-weight: 700; letter-spacing: .07em;
  text-transform: uppercase; color: var(--text-light);
  min-width: 120px; flex-shrink: 0;
}
.info-val { color: var(--text-dark); font-weight: 500; word-break: break-word; }
.followup-item {
  padding: 14px 0; border-bottom: 1px solid var(--cream-dark);
}
.followup-item:last-child { border-bottom: none; }
.followup-meta { font-size: 12px; color: var(--text-light); margin-bottom: 4px; }
.followup-body { font-size: 14px; color: var(--text-dark); line-height: 1.55; }
.desc-box {
  background: var(--cream); border: 1px solid var(--cream-dark);
  border-radius: var(--radius); padding: 14px 18px;
  font-size: 14px; color: var(--text-dark); line-height: 1.6; margin-bottom: 18px;
}
</style>
@endpush

@section('content')

@php
  $property = $request->lease?->property;
  $tenant   = $request->raisedBy ?? $request->lease?->tenant;
  $team     = $request->maintenanceTeam;
  $age      = $request->created_at->diffForHumans();
@endphp

<div class="db-stats">
  <div class="db-stat">
    <div class="db-stat-label">Status</div>
    <div class="db-stat-value" style="margin-top:4px;font-size:20px">
      <span class="req-status {{ $request->status }}">
        {{ ucwords(str_replace('_', ' ', $request->status)) }}
      </span>
    </div>
    <div class="db-stat-sub">{{ $age }}</div>
  </div>
  <div class="db-stat">
    <div class="db-stat-label">Category</div>
    <div class="db-stat-value" style="font-size:22px">{{ ucfirst($request->category ?? '—') }}</div>
    <div class="db-stat-sub">Issue type</div>
  </div>
  <div class="db-stat {{ $team ? 'green' : '' }}">
    <div class="db-stat-label">Assigned team</div>
    <div class="db-stat-value" style="font-size:{{ $team ? '18px' : '22px' }}">
      {{ $team ? $team->name : '—' }}
    </div>
    <div class="db-stat-sub">{{ $team ? $team->country_code : 'Not yet assigned' }}</div>
  </div>
  <div class="db-stat">
    <div class="db-stat-label">Follow-ups</div>
    <div class="db-stat-value">{{ $request->followUps->count() }}</div>
    <div class="db-stat-sub">Updates from tenant or team</div>
  </div>
</div>

{{-- Description --}}
@if($request->description)
<div class="desc-box">
  <div style="font-size:11px;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:var(--text-light);margin-bottom:6px">Description</div>
  {{ $request->description }}
</div>
@endif

{{-- Three-column cards --}}
<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:18px;margin-bottom:20px">

  {{-- Property / Lease --}}
  <div class="db-card" style="margin:0">
    <div class="db-card-header"><span class="db-card-title">Property</span></div>
    <div class="db-card-body" style="padding:0">
      @if($property)
      @foreach([
        ['Name',    $property->name],
        ['Address', ($property->address_line1 ?? '').($property->city ? ', '.$property->city : '')],
        ['Country', strtoupper($property->country_code ?? '—')],
      ] as [$lbl, $val])
      <div class="info-row">
        <span class="info-label">{{ $lbl }}</span>
        <span class="info-val">{{ $val }}</span>
      </div>
      @endforeach
      <div class="info-row" style="padding-top:14px">
        <a href="{{ route('admin.properties.show', $property) }}"
           class="db-btn db-btn-ghost" style="font-size:13px;text-decoration:none">Property →</a>
        @if($request->lease)
        <a href="{{ route('admin.leases.show', $request->lease) }}"
           class="db-btn db-btn-ghost" style="font-size:13px;text-decoration:none;margin-left:6px">Lease →</a>
        @endif
      </div>
      @else
        <div class="info-row"><span style="color:var(--text-light)">No property linked</span></div>
      @endif
    </div>
  </div>

  {{-- Tenant --}}
  <div class="db-card" style="margin:0">
    <div class="db-card-header"><span class="db-card-title">Raised by</span></div>
    <div class="db-card-body" style="padding:0">
      @if($tenant)
      @foreach([
        ['Name',  $tenant->first_name.' '.$tenant->last_name],
        ['Email', $tenant->email],
      ] as [$lbl, $val])
      <div class="info-row">
        <span class="info-label">{{ $lbl }}</span>
        <span class="info-val">{{ $val }}</span>
      </div>
      @endforeach
      <div class="info-row" style="padding-top:14px">
        <a href="mailto:{{ $tenant->email }}"
           class="db-btn db-btn-ghost" style="font-size:13px;text-decoration:none">Email tenant</a>
      </div>
      @else
        <div class="info-row"><span style="color:var(--text-light)">—</span></div>
      @endif
    </div>
  </div>

  {{-- Maintenance team --}}
  <div class="db-card" style="margin:0">
    <div class="db-card-header"><span class="db-card-title">Maintenance team</span></div>
    <div class="db-card-body" style="padding:0">
      @if($team)
      @foreach([
        ['Name',    $team->name],
        ['City',    $team->city ?? '—'],
        ['Country', strtoupper($team->country_code ?? '—')],
        ['Phone',   $team->phone ?? '—'],
      ] as [$lbl, $val])
      <div class="info-row">
        <span class="info-label">{{ $lbl }}</span>
        <span class="info-val">{{ $val }}</span>
      </div>
      @endforeach
      @else
        <div class="info-row">
          <span style="color:#b71c1c;font-size:14px">No team assigned yet.</span>
        </div>
      @endif
      @foreach([
        ['Acknowledged', $request->acknowledged_at?->format('d M Y H:i') ?? '—'],
        ['Assigned',     $request->assigned_at?->format('d M Y H:i') ?? '—'],
        ['Resolved',     $request->resolved_at?->format('d M Y H:i') ?? '—'],
      ] as [$lbl, $val])
      <div class="info-row">
        <span class="info-label" style="min-width:100px">{{ $lbl }}</span>
        <span class="info-val" style="font-size:13px">{{ $val }}</span>
      </div>
      @endforeach
    </div>
  </div>
</div>

{{-- Resolution notes --}}
@if($request->resolution_notes)
<div class="desc-box" style="margin-bottom:20px">
  <div style="font-size:11px;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:var(--text-light);margin-bottom:6px">Resolution notes</div>
  {{ $request->resolution_notes }}
</div>
@endif

{{-- Follow-up timeline --}}
<div class="db-card">
  <div class="db-card-header">
    <span class="db-card-title">Follow-up thread ({{ $request->followUps->count() }})</span>
  </div>
  <div class="db-card-body">
    @if($request->followUps->isEmpty())
      <div class="db-empty" style="padding:24px 0">
        <div class="db-empty-icon">💬</div>
        <h3>No updates yet</h3>
        <p>Follow-ups from the tenant or team will appear here.</p>
      </div>
    @else
      @foreach($request->followUps as $update)
      <div class="followup-item">
        <div class="followup-meta">
          {{ $update->created_at->format('d M Y · H:i') }}
          @if($update->author_name ?? null)
            · <strong>{{ $update->author_name }}</strong>
          @endif
        </div>
        <div class="followup-body">{{ $update->body ?? $update->note ?? $update->message ?? '—' }}</div>
      </div>
      @endforeach
    @endif
  </div>
</div>

@endsection
