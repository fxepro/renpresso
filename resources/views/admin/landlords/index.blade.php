@extends('admin.layout')
@section('title', 'Landlords')
@section('page-title', 'Landlords')
@section('breadcrumb', 'Operations')

@push('styles')
<style>
.landlord-status {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  font-size: 12px;
  font-weight: 600;
  padding: 3px 9px;
  border-radius: 20px;
  white-space: nowrap;
}
.landlord-status.active   { background: #e8f5e9; color: #2e7d32; }
.landlord-status.inactive { background: #fce4ec; color: #b71c1c; }
.landlord-status.pending  { background: #fff8e1; color: #f57f17; }
.kyc-badge {
  display: inline-flex;
  align-items: center;
  font-size: 11px;
  font-weight: 600;
  padding: 2px 8px;
  border-radius: 20px;
  white-space: nowrap;
}
.kyc-badge.verified  { background: #e8f5e9; color: #2e7d32; }
.kyc-badge.pending   { background: #fff8e1; color: #f57f17; }
.kyc-badge.none      { background: #f5f5f5; color: #9e9e9e; }
.ll-name  { font-weight: 600; color: var(--text-dark); line-height: 1.3; }
.ll-email { font-size: 12px; color: var(--text-light); margin-top: 1px; }
.ll-props { font-size: 13px; color: var(--text-mid); }
.ll-props .prop-count { font-weight: 600; color: var(--text-dark); }
.ll-mrr   { font-weight: 600; font-size: 14px; color: var(--text-dark); }
.ll-mrr.zero { color: var(--text-light); font-weight: 400; }
</style>
@endpush

@section('content')

<p class="admin-portal-note">All landlord accounts on the platform. MRR = active leases × $9/mo.</p>

<div class="db-stats">
  <div class="db-stat terra">
    <div class="db-stat-label">Total landlords</div>
    <div class="db-stat-value">{{ $stats['total'] }}</div>
    <div class="db-stat-sub">Accounts</div>
  </div>
  <div class="db-stat green">
    <div class="db-stat-label">Active accounts</div>
    <div class="db-stat-value">{{ $stats['active'] }}</div>
    <div class="db-stat-sub">Paying subscribers</div>
  </div>
  <div class="db-stat">
    <div class="db-stat-label">Inactive / free</div>
    <div class="db-stat-value">{{ $stats['inactive'] }}</div>
    <div class="db-stat-sub">Not yet on a plan</div>
  </div>
  <div class="db-stat green">
    <div class="db-stat-label">Platform MRR</div>
    <div class="db-stat-value" style="font-size:var(--fs-heading)">${{ number_format($stats['platform_mrr_minor'] / 100, 2) }}</div>
    <div class="db-stat-sub">Recurring · all landlords</div>
  </div>
</div>

<div class="db-card">
  <div class="db-card-header">
    <span class="db-card-title">Landlord accounts ({{ $stats['total'] }})</span>
  </div>
  <div class="db-card-body" style="padding:0">
    @if($landlords->isEmpty())
      <div class="db-empty" style="padding:40px 20px">
        <div class="db-empty-icon">🏢</div>
        <h3>No landlords yet</h3>
        <p>Landlord accounts will appear here once someone signs up.</p>
      </div>
    @else
    <div class="db-table-wrap">
      <table class="db-table">
        <thead>
          <tr>
            <th>Landlord</th>
            <th>Country</th>
            <th>Account</th>
            <th>KYC</th>
            <th style="text-align:center">Properties</th>
            <th style="text-align:center">Active leases</th>
            <th style="text-align:right">MRR</th>
            <th style="text-align:right">Joined</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @foreach($landlords as $ll)
          @php
            $leases    = $leaseCounts[$ll->id] ?? 0;
            $mrr       = $leases * 900;
            $accStatus = $ll->landlord_account_status ?? 'pending_activation';
            $accLabel  = match($accStatus) {
              'active'              => ['active',   'Active'],
              'pending', 'pending_activation' => ['pending',  'Pending'],
              default               => ['inactive', 'Inactive'],
            };
            $kycStatus = match($ll->kyc_status ?? 'none') {
              'verified'  => 'verified',
              'submitted' => 'pending',
              default     => 'none',
            };
            $kycLabel  = match($kycStatus) {
              'verified' => 'Verified',
              'pending'  => 'Submitted',
              default    => 'None',
            };
            $totalProps  = ($ll->single_count ?? 0) + ($ll->multi_count ?? 0);
            $detailUrl   = route('admin.landlords.show', $ll);
          @endphp
          <tr style="cursor:pointer" onclick="window.location='{{ $detailUrl }}'">
            <td>
              <a href="{{ $detailUrl }}" class="db-table-link" style="font-weight:600;text-decoration:none;color:var(--text-dark)" onclick="event.stopPropagation()">
                {{ $ll->first_name }} {{ $ll->last_name }}
              </a>
              <div class="ll-email">{{ $ll->email }}</div>
            </td>
            <td>{{ strtoupper($ll->home_country ?? '—') }}</td>
            <td>
              <span class="landlord-status {{ $accLabel[0] }}">
                {{ $accLabel[1] }}
              </span>
            </td>
            <td>
              <span class="kyc-badge {{ $kycStatus }}">{{ $kycLabel }}</span>
            </td>
            <td style="text-align:center">
              @if($totalProps > 0)
                <span class="ll-props">
                  <span class="prop-count">{{ $totalProps }}</span>
                  @if(($ll->single_count ?? 0) > 0 && ($ll->multi_count ?? 0) > 0)
                    <span style="font-size:11px;color:var(--text-light)">
                      ({{ $ll->single_count }}s / {{ $ll->multi_count }}m)
                    </span>
                  @endif
                </span>
              @else
                <span style="color:var(--text-light);font-size:13px">—</span>
              @endif
            </td>
            <td style="text-align:center">
              @if($leases > 0)
                <span class="ll-props"><span class="prop-count">{{ $leases }}</span></span>
              @else
                <span style="color:var(--text-light);font-size:13px">—</span>
              @endif
            </td>
            <td style="text-align:right">
              <span class="ll-mrr {{ $mrr === 0 ? 'zero' : '' }}">
                {{ $mrr > 0 ? '$'.number_format($mrr / 100, 2) : '—' }}
              </span>
            </td>
            <td style="text-align:right;font-size:12px;color:var(--text-light);white-space:nowrap">
              {{ $ll->created_at ? $ll->created_at->format('d M Y') : '—' }}
            </td>
            <td style="text-align:right" onclick="event.stopPropagation()">
              <a href="{{ $detailUrl }}" class="db-table-link" style="font-size:12px;white-space:nowrap">View →</a>
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
