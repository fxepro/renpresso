@extends('admin.layout')
@section('title', $team->name)
@section('page-title', $team->name)
@section('breadcrumb', 'Maintenance teams')

@section('topbar-actions')
  <a href="{{ route('admin.maintenance-teams') }}" class="db-btn db-btn-ghost" style="text-decoration:none">← All teams</a>
@endsection

@push('styles')
<style>
.listed-badge {
  display: inline-flex; font-size: 12px; font-weight: 600;
  padding: 3px 11px; border-radius: 20px;
}
.listed-badge.yes { background: #e8f5e9; color: #2e7d32; }
.listed-badge.no  { background: #f5f5f5; color: #9e9e9e; }
.req-status {
  display: inline-flex; font-size: 11px; font-weight: 600;
  padding: 2px 8px; border-radius: 20px;
}
.req-status.submitted   { background: #fff8e1; color: #f57f17; }
.req-status.acknowledged{ background: #e3f2fd; color: #1565c0; }
.req-status.in_progress { background: #e8f5e9; color: #2e7d32; }
.req-status.resolved    { background: #f5f5f5; color: #757575; }
.inv-status {
  display: inline-flex; font-size: 11px; font-weight: 600;
  padding: 2px 8px; border-radius: 20px;
}
.inv-status.draft { background: #f5f5f5; color: #9e9e9e; }
.inv-status.sent  { background: #e3f2fd; color: #1565c0; }
.inv-status.paid  { background: #e8f5e9; color: #2e7d32; }
.inv-status.cancelled { background: #fce4ec; color: #b71c1c; }
.info-row {
  display: flex; align-items: baseline; gap: 12px;
  padding: 10px 24px; border-bottom: 1px solid var(--cream-dark); font-size: 14px;
}
.info-row:last-child { border-bottom: none; }
.info-label {
  font-size: 11px; font-weight: 700; letter-spacing: .07em;
  text-transform: uppercase; color: var(--text-light);
  min-width: 110px; flex-shrink: 0;
}
.info-val { color: var(--text-dark); font-weight: 500; }
.service-pill {
  display: inline-flex; font-size: 12px; font-weight: 500;
  padding: 3px 10px; border-radius: 20px; margin: 2px;
  background: var(--cream); border: 1px solid var(--cream-dark); color: var(--text-mid);
}
.city-pill {
  display: inline-flex; font-size: 12px; font-weight: 500;
  padding: 3px 10px; border-radius: 20px; margin: 2px;
  background: #e3f2fd; border: 1px solid #90caf9; color: #1565c0;
}
.star-rating { color: #f59e0b; font-weight: 700; }
.compliance-bar {
  height: 6px; border-radius: 3px; background: var(--cream-dark);
  overflow: hidden; margin-top: 4px;
}
.compliance-fill { height: 100%; border-radius: 3px; background: #43a047; }
.ll-link { font-weight: 500; color: var(--text-dark); text-decoration: none; font-size: 13px; }
.ll-link:hover { color: var(--terra); }
</style>
@endpush

@section('content')

@php
  $rating = $team->averageRating();
@endphp

<div class="db-stats">
  <div class="db-stat {{ $team->is_listed ? 'green' : '' }}">
    <div class="db-stat-label">Directory status</div>
    <div class="db-stat-value" style="margin-top:4px;font-size:20px">
      <span class="listed-badge {{ $team->is_listed ? 'yes' : 'no' }}">
        {{ $team->is_listed ? 'Listed' : 'Unlisted' }}
      </span>
    </div>
    <div class="db-stat-sub">{{ $team->locationLabel() }}</div>
  </div>
  <div class="db-stat {{ $rating ? 'green' : '' }}">
    <div class="db-stat-label">Rating</div>
    <div class="db-stat-value">
      @if($rating)
        <span class="star-rating">★ {{ $rating }}</span>
      @else
        —
      @endif
    </div>
    <div class="db-stat-sub">{{ $team->reviews->count() }} reviews</div>
  </div>
  <div class="db-stat">
    <div class="db-stat-label">Engaged landlords</div>
    <div class="db-stat-value">{{ $team->engagedLandlords->count() }}</div>
    <div class="db-stat-sub">Active relationships</div>
  </div>
  <div class="db-stat">
    <div class="db-stat-label">Requests assigned</div>
    <div class="db-stat-value">{{ $requests->count() }}</div>
    <div class="db-stat-sub">{{ $team->invoices->count() }} invoices raised</div>
  </div>
</div>

{{-- Team info + Owner + Coverage --}}
<div style="display:grid;grid-template-columns:1.4fr 1fr 1fr;gap:18px;margin-bottom:20px">

  <div class="db-card" style="margin:0">
    <div class="db-card-header"><span class="db-card-title">Team details</span></div>
    <div class="db-card-body" style="padding:0">
      @foreach([
        ['Name',    $team->name],
        ['City',    $team->city],
        ['Country', strtoupper($team->country_code)],
        ['Phone',   $team->phone ?? '—'],
        ['Listed',  $team->is_listed ? 'Yes' : 'No'],
        ['Joined',  $team->created_at?->format('d M Y') ?? '—'],
      ] as [$lbl, $val])
      <div class="info-row">
        <span class="info-label">{{ $lbl }}</span>
        <span class="info-val">{{ $val }}</span>
      </div>
      @endforeach
      @if($team->description)
      <div class="info-row" style="flex-direction:column;gap:6px">
        <span class="info-label">About</span>
        <span style="font-size:14px;color:var(--text-mid);line-height:1.5">{{ $team->description }}</span>
      </div>
      @endif
      <div class="info-row" style="flex-direction:column;gap:6px">
        <span class="info-label">Services</span>
        <div>
          @foreach($team->serviceList() as $svc)
            <span class="service-pill">{{ $svc }}</span>
          @endforeach
        </div>
      </div>
    </div>
  </div>

  <div class="db-card" style="margin:0">
    <div class="db-card-header"><span class="db-card-title">Owner</span></div>
    <div class="db-card-body" style="padding:0">
      @if($team->owner)
      @foreach([
        ['Name',  $team->owner->first_name.' '.$team->owner->last_name],
        ['Email', $team->owner->email],
      ] as [$lbl, $val])
      <div class="info-row">
        <span class="info-label">{{ $lbl }}</span>
        <span class="info-val" style="word-break:break-all">{{ $val }}</span>
      </div>
      @endforeach
      <div class="info-row" style="padding-top:14px">
        <a href="mailto:{{ $team->owner->email }}"
           class="db-btn db-btn-ghost" style="font-size:13px;text-decoration:none">Email owner</a>
      </div>
      @else
        <div class="info-row"><span style="color:var(--text-light)">No owner linked</span></div>
      @endif
    </div>
  </div>

  <div class="db-card" style="margin:0">
    <div class="db-card-header"><span class="db-card-title">Coverage cities</span></div>
    <div class="db-card-body">
      @if($team->cities->isEmpty())
        <p style="color:var(--text-light);font-size:13px;margin:0">No additional cities configured. Operating from primary location only.</p>
      @else
        <div>
          @foreach($team->cities as $city)
            <span class="city-pill">
              {{ $city->city }}, {{ strtoupper($city->country_code) }}
              @if($city->is_primary ?? false)
                <span style="opacity:.6;margin-left:3px">primary</span>
              @endif
            </span>
          @endforeach
        </div>
      @endif

      @if($compliance['required_total'] > 0)
      <div style="margin-top:18px">
        <div style="font-size:11px;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:var(--text-light);margin-bottom:6px">
          Compliance docs
        </div>
        <div style="font-size:13px;color:var(--text-dark)">
          {{ $compliance['verified'] }} / {{ $compliance['required_total'] }} verified
          &nbsp;·&nbsp; {{ $compliance['uploaded'] }} uploaded
        </div>
        <div class="compliance-bar" style="width:100%;margin-top:6px">
          <div class="compliance-fill" style="width:{{ $compliance['required_total'] > 0 ? round($compliance['verified'] / $compliance['required_total'] * 100) : 0 }}%"></div>
        </div>
      </div>
      @endif
    </div>
  </div>
</div>

{{-- Engaged landlords --}}
@if($team->engagedLandlords->count())
<div class="db-card" style="margin-bottom:20px">
  <div class="db-card-header">
    <span class="db-card-title">Engaged landlords ({{ $team->engagedLandlords->count() }})</span>
  </div>
  <div class="db-card-body" style="padding:0">
    <div class="db-table-wrap">
      <table class="db-table">
        <thead>
          <tr>
            <th>Landlord</th>
            <th>Country</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @foreach($team->engagedLandlords as $ll)
          <tr>
            <td>
              <div class="ll-link">{{ $ll->first_name }} {{ $ll->last_name }}</div>
              <div style="font-size:12px;color:var(--text-light)">{{ $ll->email }}</div>
            </td>
            <td style="font-size:13px">{{ strtoupper($ll->home_country ?? '—') }}</td>
            <td style="text-align:right">
              <a href="{{ route('admin.landlords.show', $ll) }}"
                 class="db-table-link" style="font-size:12px">View →</a>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>
@endif

{{-- Assigned maintenance requests --}}
<div class="db-card" style="margin-bottom:20px">
  <div class="db-card-header">
    <span class="db-card-title">Assigned requests ({{ $requests->count() }})</span>
  </div>
  <div class="db-card-body" style="{{ $requests->isEmpty() ? '' : 'padding:0' }}">
    @if($requests->isEmpty())
      <div class="db-empty" style="padding:28px 0">
        <div class="db-empty-icon">🛠️</div>
        <h3 style="font-size:16px">No requests assigned yet</h3>
        <p>Maintenance requests assigned to this team will appear here.</p>
      </div>
    @else
    <div class="db-table-wrap">
      <table class="db-table">
        <thead>
          <tr>
            <th>Request</th>
            <th>Property</th>
            <th>Raised by</th>
            <th>Status</th>
            <th style="text-align:right">Date</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @foreach($requests as $req)
          <tr>
            <td style="font-weight:500;color:var(--text-dark)">{{ $req->title }}</td>
            <td style="font-size:13px;color:var(--text-mid)">
              {{ $req->lease?->property?->name ?? '—' }}
            </td>
            <td style="font-size:13px">
              {{ $req->raisedBy ? $req->raisedBy->first_name.' '.$req->raisedBy->last_name : '—' }}
            </td>
            <td>
              <span class="req-status {{ $req->status }}">
                {{ ucwords(str_replace('_',' ',$req->status)) }}
              </span>
            </td>
            <td style="text-align:right;font-size:12px;color:var(--text-light);white-space:nowrap">
              {{ $req->created_at->format('d M Y') }}
            </td>
            <td style="text-align:right">
              <a href="{{ route('admin.maintenance-requests.show', $req) }}"
                 class="db-table-link" style="font-size:12px">View →</a>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    @endif
  </div>
</div>

{{-- Recent invoices --}}
<div class="db-card">
  <div class="db-card-header">
    <span class="db-card-title">Recent invoices ({{ $team->invoices->count() }})</span>
  </div>
  <div class="db-card-body" style="{{ $team->invoices->isEmpty() ? '' : 'padding:0' }}">
    @if($team->invoices->isEmpty())
      <div class="db-empty" style="padding:28px 0">
        <div class="db-empty-icon">📄</div>
        <h3 style="font-size:16px">No invoices yet</h3>
        <p>Invoices raised by this team will appear here.</p>
      </div>
    @else
    <div class="db-table-wrap">
      <table class="db-table">
        <thead>
          <tr>
            <th>Invoice #</th>
            <th>Property</th>
            <th style="text-align:right">Amount</th>
            <th>Status</th>
            <th style="text-align:right">Issued</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @foreach($team->invoices as $inv)
          <tr>
            <td style="font-family:monospace;font-size:13px">{{ $inv->invoice_number ?? 'INV-'.$inv->id }}</td>
            <td style="font-size:13px;color:var(--text-mid)">{{ $inv->property?->name ?? '—' }}</td>
            <td style="text-align:right;font-weight:600;font-size:13px">
              {{ strtoupper($inv->currency_code) }} {{ number_format($inv->amount_minor / 100, 2) }}
            </td>
            <td>
              <span class="inv-status {{ $inv->status }}">{{ ucwords(str_replace('_',' ',$inv->status)) }}</span>
            </td>
            <td style="text-align:right;font-size:12px;color:var(--text-light);white-space:nowrap">
              {{ $inv->issued_at?->format('d M Y') ?? $inv->created_at->format('d M Y') }}
            </td>
            <td style="text-align:right">
              <a href="{{ route('admin.maintenance-invoices.show', $inv) }}"
                 class="db-table-link" style="font-size:12px">View →</a>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    @endif
  </div>
</div>

@endsection
