@extends('admin.layout')
@section('title', $period->format('F Y').' — Landlord billing')
@section('page-title', $period->format('F Y'))
@section('breadcrumb', 'Landlord subscriptions')

@section('topbar-actions')
  <a href="{{ route('admin.landlord-billing') }}" class="db-btn db-btn-ghost" style="text-decoration:none">← All months</a>
@endsection

@push('styles')
<style>
.bill-status {
  display: inline-flex;
  align-items: center;
  font-size: 12px;
  font-weight: 600;
  padding: 3px 10px;
  border-radius: 20px;
  white-space: nowrap;
}
.bill-status.paid     { background: #e8f5e9; color: #2e7d32; }
.bill-status.due      { background: #fff3e0; color: #e65100; }
.bill-status.upcoming { background: #f5f5f5; color: #9e9e9e; }
.acc-chip {
  display: inline-flex;
  font-size: 11px;
  font-weight: 600;
  padding: 2px 7px;
  border-radius: 20px;
}
.acc-chip.active   { background: #e8f5e9; color: #2e7d32; }
.acc-chip.pending  { background: #fff8e1; color: #f57f17; }
.acc-chip.inactive { background: #fce4ec; color: #b71c1c; }
.ll-name { font-weight: 600; color: var(--text-dark); text-decoration: none; }
.ll-name:hover { color: var(--terra); }
.ll-email { font-size: 12px; color: var(--text-light); margin-top: 1px; }
.lease-pill {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  font-size: 12px;
  background: var(--cream);
  border: 1px solid var(--cream-dark);
  border-radius: 12px;
  padding: 2px 8px;
  color: var(--text-mid);
  white-space: nowrap;
}
</style>
@endpush

@section('content')

<div class="db-stats">
  <div class="db-stat {{ $status === 'paid' ? 'green' : '' }}">
    <div class="db-stat-label">Period</div>
    <div class="db-stat-value" style="font-size:22px;line-height:1.2">{{ $period->format('M Y') }}</div>
    <div class="db-stat-sub">
      <span class="bill-status {{ $status }}">{{ ucfirst($status) }}</span>
    </div>
  </div>
  <div class="db-stat">
    <div class="db-stat-label">Landlords billed</div>
    <div class="db-stat-value">{{ $byLandlord->count() }}</div>
    <div class="db-stat-sub">With active leases</div>
  </div>
  <div class="db-stat">
    <div class="db-stat-label">Total leases</div>
    <div class="db-stat-value">{{ $totalLeases }}</div>
    <div class="db-stat-sub">Across all landlords</div>
  </div>
  <div class="db-stat green">
    <div class="db-stat-label">Total due</div>
    <div class="db-stat-value" style="font-size:var(--fs-heading)">${{ number_format($totalMinor / 100, 2) }}</div>
    <div class="db-stat-sub">$9.00 × {{ $totalLeases }} leases</div>
  </div>
</div>

<div class="db-card">
  <div class="db-card-header">
    <span class="db-card-title">Per-landlord breakdown ({{ $byLandlord->count() }})</span>
  </div>
  <div class="db-card-body" style="padding:0">
    @if($byLandlord->isEmpty())
      <div class="db-empty" style="padding:40px 20px">
        <div class="db-empty-icon">🧾</div>
        <h3>No active leases this month</h3>
        <p>No landlords had active leases during {{ $period->format('F Y') }}.</p>
      </div>
    @else
    <div class="db-table-wrap">
      <table class="db-table">
        <thead>
          <tr>
            <th>Landlord</th>
            <th>Country</th>
            <th>Account</th>
            <th style="text-align:center">Active leases</th>
            <th style="text-align:right">Rate</th>
            <th style="text-align:right">Charge</th>
            <th style="text-align:left;padding-left:16px">Properties</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @foreach($byLandlord as $row)
          @php
            $ll        = $row['landlord'];
            $accStatus = $ll->landlord_account_status ?? 'pending_activation';
            $accChip   = match($accStatus) {
              'active'                        => ['active',   'Active'],
              'pending', 'pending_activation' => ['pending',  'Pending'],
              default                         => ['inactive', 'Inactive'],
            };
            $properties = $row['leases']->pluck('property')->unique('id');
          @endphp
          <tr>
            <td>
              <a href="{{ route('admin.landlords.show', $ll) }}" class="ll-name">
                {{ $ll->first_name }} {{ $ll->last_name }}
              </a>
              <div class="ll-email">{{ $ll->email }}</div>
            </td>
            <td style="font-size:13px">{{ strtoupper($ll->home_country ?? '—') }}</td>
            <td>
              <span class="acc-chip {{ $accChip[0] }}">{{ $accChip[1] }}</span>
            </td>
            <td style="text-align:center;font-weight:600">
              {{ $row['lease_count'] }}
            </td>
            <td style="text-align:right;color:var(--text-mid);font-size:13px">
              $9.00 each
            </td>
            <td style="text-align:right;font-weight:700;color:var(--text-dark)">
              ${{ number_format($row['total_minor'] / 100, 2) }}
            </td>
            <td style="padding-left:16px">
              <div style="display:flex;flex-wrap:wrap;gap:4px">
                @foreach($properties as $prop)
                  <span class="lease-pill">
                    {{ $prop->name }}
                    @if($prop->occupancy_mode === 'multi')
                      <span style="font-size:10px;opacity:.7">multi</span>
                    @endif
                  </span>
                @endforeach
              </div>
            </td>
            <td style="text-align:right">
              <a href="{{ route('admin.landlords.show', $ll) }}" class="db-table-link" style="font-size:12px;white-space:nowrap">
                View →
              </a>
            </td>
          </tr>
          @endforeach
        </tbody>
        <tfoot>
          <tr style="background:var(--cream)">
            <td colspan="3" style="font-weight:700;color:var(--text-dark);padding:12px 16px">Total</td>
            <td style="text-align:center;font-weight:700">{{ $totalLeases }}</td>
            <td></td>
            <td style="text-align:right;font-weight:700;color:var(--text-dark)">${{ number_format($totalMinor / 100, 2) }}</td>
            <td colspan="2"></td>
          </tr>
        </tfoot>
      </table>
    </div>
    @endif
  </div>
</div>

@endsection
