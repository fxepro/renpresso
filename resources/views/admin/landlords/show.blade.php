@extends('admin.layout')
@section('title', $user->first_name.' '.$user->last_name.' — Landlord')
@section('page-title', $user->first_name.' '.$user->last_name)
@section('breadcrumb', 'Landlords')

@section('topbar-actions')
  <a href="{{ route('admin.landlords') }}" class="db-btn db-btn-ghost" style="text-decoration:none">← All landlords</a>
  <a href="mailto:{{ $user->email }}" class="db-btn db-btn-ghost" style="text-decoration:none">Email landlord</a>
@endsection

@push('styles')
<style>
.ll-profile-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0;
}
.ll-profile-row {
  display: flex;
  align-items: baseline;
  gap: 12px;
  padding: 10px 24px;
  border-bottom: 1px solid var(--cream-dark);
  font-size: 14px;
}
.ll-profile-row:last-child { border-bottom: none; }
.ll-profile-label {
  font-size: 11px;
  font-weight: 700;
  letter-spacing: .07em;
  text-transform: uppercase;
  color: var(--text-light);
  min-width: 110px;
  flex-shrink: 0;
}
.ll-profile-val { color: var(--text-dark); font-weight: 500; }
.acc-badge {
  display: inline-flex;
  align-items: center;
  font-size: 12px;
  font-weight: 600;
  padding: 3px 10px;
  border-radius: 20px;
}
.acc-badge.active   { background: #e8f5e9; color: #2e7d32; }
.acc-badge.inactive { background: #fce4ec; color: #b71c1c; }
.acc-badge.pending  { background: #fff8e1; color: #f57f17; }
.kyc-badge {
  display: inline-flex;
  font-size: 11px;
  font-weight: 600;
  padding: 2px 8px;
  border-radius: 20px;
}
.kyc-badge.verified  { background: #e8f5e9; color: #2e7d32; }
.kyc-badge.pending   { background: #fff8e1; color: #f57f17; }
.kyc-badge.none      { background: #f5f5f5; color: #9e9e9e; }
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
.prop-status-dot {
  display: inline-block;
  width: 7px; height: 7px;
  border-radius: 50%;
  margin-right: 5px;
  background: #9e9e9e;
}
.prop-status-dot.active   { background: #43a047; }
.prop-status-dot.inactive { background: #e53935; }
.prop-name-link { font-weight: 600; color: var(--text-dark); text-decoration: none; }
.prop-name-link:hover { color: var(--terra); }
.prop-addr { font-size: 12px; color: var(--text-light); margin-top: 1px; }
</style>
@endpush

@section('content')

@php
  $accStatus = $user->landlord_account_status ?? 'pending_activation';
  $accLabel  = match($accStatus) {
    'active'                          => ['active',   'Active'],
    'pending', 'pending_activation'   => ['pending',  'Pending'],
    default                           => ['inactive', 'Inactive'],
  };
  $kycStatus = match($user->kyc_status ?? 'none') {
    'verified'  => 'verified',
    'submitted' => 'pending',
    default     => 'none',
  };
  $kycLabel  = match($kycStatus) {
    'verified' => 'Verified',
    'pending'  => 'Submitted',
    default    => 'None',
  };
  $singleCount = $properties->where('occupancy_mode', 'single')->count();
  $multiCount  = $properties->where('occupancy_mode', 'multi')->count();
@endphp

<div class="db-stats" style="grid-template-columns:repeat(4,1fr)">
  <div class="db-stat">
    <div class="db-stat-label">Properties</div>
    <div class="db-stat-value">{{ $properties->count() }}</div>
    <div class="db-stat-sub">{{ $singleCount }} single · {{ $multiCount }} multi</div>
  </div>
  <div class="db-stat">
    <div class="db-stat-label">Active leases</div>
    <div class="db-stat-value">{{ $activeLeases }}</div>
    <div class="db-stat-sub">Across all properties</div>
  </div>
  <div class="db-stat {{ $mrr > 0 ? 'green' : '' }}">
    <div class="db-stat-label">MRR</div>
    <div class="db-stat-value" style="font-size:var(--fs-heading)">${{ number_format($mrr / 100, 2) }}</div>
    <div class="db-stat-sub">@ $9/lease/mo</div>
  </div>
  <div class="db-stat">
    <div class="db-stat-label">Account</div>
    <div class="db-stat-value" style="font-size:20px;margin-top:4px">
      <span class="acc-badge {{ $accLabel[0] }}">{{ $accLabel[1] }}</span>
    </div>
    <div class="db-stat-sub">{{ $user->created_at ? 'Since '.$user->created_at->format('M Y') : '—' }}</div>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:20px">
  <div class="db-card" style="margin:0">
    <div class="db-card-header"><span class="db-card-title">Profile</span></div>
    <div class="db-card-body" style="padding:0">
      <div class="ll-profile-grid">
        @foreach([
          ['Name',          $user->first_name.' '.$user->last_name],
          ['Email',         $user->email],
          ['Country',       strtoupper($user->home_country ?? '—')],
          ['Home currency', strtoupper($user->home_currency ?? '—')],
          ['Account',       null],
          ['KYC',           null],
          ['Joined',        $user->created_at ? $user->created_at->format('d M Y') : '—'],
        ] as [$lbl, $val])
        <div class="ll-profile-row">
          <span class="ll-profile-label">{{ $lbl }}</span>
          @if($lbl === 'Account')
            <span class="acc-badge {{ $accLabel[0] }}">{{ $accLabel[1] }}</span>
          @elseif($lbl === 'KYC')
            <span class="kyc-badge {{ $kycStatus }}">{{ $kycLabel }}</span>
          @else
            <span class="ll-profile-val">{{ $val }}</span>
          @endif
        </div>
        @endforeach
      </div>
    </div>
  </div>

  <div class="db-card" style="margin:0">
    <div class="db-card-header"><span class="db-card-title">Billing</span></div>
    <div class="db-card-body" style="padding:0">
      @foreach([
        ['Rate',            '$9.00 / active lease / mo'],
        ['Active leases',   $activeLeases],
        ['Current MRR',     $mrr > 0 ? '$'.number_format($mrr/100,2).'/mo' : '—'],
        ['Total properties', $properties->count()],
        ['Single-unit',     $singleCount],
        ['Multi-unit',      $multiCount],
      ] as [$lbl, $val])
      <div class="ll-profile-row">
        <span class="ll-profile-label">{{ $lbl }}</span>
        <span class="ll-profile-val">{{ $val }}</span>
      </div>
      @endforeach
    </div>
  </div>
</div>

<div class="db-card">
  <div class="db-card-header">
    <span class="db-card-title">Properties ({{ $properties->count() }})</span>
  </div>
  <div class="db-card-body" style="padding:0">
    @if($properties->isEmpty())
      <div class="db-empty" style="padding:40px 20px">
        <div class="db-empty-icon">🏘️</div>
        <h3>No properties yet</h3>
        <p>This landlord hasn't added any properties.</p>
      </div>
    @else
    <div class="db-table-wrap">
      <table class="db-table">
        <thead>
          <tr>
            <th>Property</th>
            <th>Mode</th>
            <th style="text-align:center">Units</th>
            <th>Country</th>
            <th>Currency</th>
            <th style="text-align:center">Active leases</th>
            <th style="text-align:center">Total leases</th>
            <th>Listing</th>
            <th>Rental</th>
            <th style="text-align:right">Added</th>
          </tr>
        </thead>
        <tbody>
          @foreach($properties as $prop)
          @php
            $isActive = $prop->active_lease_count > 0 || $prop->status === 'active';
            $statusDot = $prop->active_lease_count > 0 ? 'active' : 'inactive';
          @endphp
          <tr>
            <td>
              <div style="display:flex;align-items:center;gap:6px">
                <span class="prop-status-dot {{ $statusDot }}"></span>
                <div>
                  <div class="prop-name-link">{{ $prop->name }}</div>
                  <div class="prop-addr">{{ $prop->address_line1 }}{{ $prop->city ? ', '.$prop->city : '' }}</div>
                </div>
              </div>
            </td>
            <td>
              <span class="mode-chip {{ $prop->occupancy_mode === 'multi' ? 'multi' : '' }}">
                {{ $prop->occupancy_mode === 'multi' ? 'Multi-unit' : 'Single' }}
              </span>
            </td>
            <td style="text-align:center">
              @if($prop->occupancy_mode === 'multi')
                {{ $prop->unit_capacity ?? '—' }}
              @else
                <span style="color:var(--text-light)">1</span>
              @endif
            </td>
            <td style="font-size:13px">{{ strtoupper($prop->country_code ?? '—') }}</td>
            <td style="font-size:13px">{{ strtoupper($prop->currency_code ?? '—') }}</td>
            <td style="text-align:center">
              @if($prop->active_lease_count > 0)
                <span style="font-weight:600;color:var(--text-dark)">{{ $prop->active_lease_count }}</span>
              @else
                <span style="color:var(--text-light)">—</span>
              @endif
            </td>
            <td style="text-align:center;color:var(--text-mid);font-size:13px">
              {{ $prop->total_lease_count ?: '—' }}
            </td>
            <td style="font-size:12px;color:var(--text-mid)">
              {{ ucfirst($prop->listing_visibility ?? 'private') }}
            </td>
            <td style="font-size:12px;color:var(--text-mid)">
              {{ $prop->rental_mode === 'short_term' ? 'Short-term' : 'Long-term' }}
            </td>
            <td style="text-align:right;font-size:12px;color:var(--text-light);white-space:nowrap">
              {{ $prop->created_at ? $prop->created_at->format('d M Y') : '—' }}
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
