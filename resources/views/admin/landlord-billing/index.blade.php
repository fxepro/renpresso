@extends('admin.layout')
@section('title', 'Landlord subscriptions')
@section('page-title', 'Landlord subscriptions')
@section('breadcrumb', 'Finance')

@push('styles')
<style>
.bill-status {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  font-size: 12px;
  font-weight: 600;
  padding: 3px 10px;
  border-radius: 20px;
  white-space: nowrap;
}
.bill-status.paid     { background: #e8f5e9; color: #2e7d32; }
.bill-status.due      { background: #fff3e0; color: #e65100; }
.bill-status.upcoming { background: #f5f5f5; color: #9e9e9e; }
.bill-row-link { display: contents; }
.db-table tbody tr { cursor: pointer; }
.db-table tbody tr:hover td { background: var(--cream); }
</style>
@endpush

@section('content')

<p class="admin-portal-note">
  Platform subscription revenue. Each landlord is billed <strong>$9 per active lease per month</strong>.
  Click any month to see the per-landlord breakdown.
</p>

<div class="db-stats">
  <div class="db-stat green">
    <div class="db-stat-label">Current MRR</div>
    <div class="db-stat-value" style="font-size:var(--fs-heading)">${{ number_format($stats['mrr_minor'] / 100, 2) }}</div>
    <div class="db-stat-sub">This month · {{ now()->format('M Y') }}</div>
  </div>
  <div class="db-stat">
    <div class="db-stat-label">Active leases</div>
    <div class="db-stat-value">{{ $stats['active_leases'] }}</div>
    <div class="db-stat-sub">Across platform · this month</div>
  </div>
  <div class="db-stat">
    <div class="db-stat-label">Rate</div>
    <div class="db-stat-value" style="font-size:var(--fs-heading)">${{ number_format($stats['rate_minor'] / 100, 2) }}</div>
    <div class="db-stat-sub">Per lease per month</div>
  </div>
  <div class="db-stat">
    <div class="db-stat-label">Billing history</div>
    <div class="db-stat-value">{{ $stats['billed_months'] }}</div>
    <div class="db-stat-sub">Months on record</div>
  </div>
</div>

<div class="db-card">
  <div class="db-card-header">
    <span class="db-card-title">Monthly billing history</span>
  </div>
  <div class="db-card-body" style="padding:0">
    @if(empty($months))
      <div class="db-empty" style="padding:40px 20px">
        <div class="db-empty-icon">🧾</div>
        <h3>No billing history yet</h3>
        <p>Billing records will appear once landlords have active leases.</p>
      </div>
    @else
    <div class="db-table-wrap">
      <table class="db-table">
        <thead>
          <tr>
            <th>Period</th>
            <th style="text-align:center">Landlords billed</th>
            <th style="text-align:center">Active leases</th>
            <th style="text-align:right">Rate</th>
            <th style="text-align:right">Total</th>
            <th style="text-align:center">Status</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @foreach($months as $row)
          @php $url = route('admin.landlord-billing.month', [$row['period']->year, $row['period']->month]); @endphp
          <tr onclick="window.location='{{ $url }}'">
            <td style="font-weight:600;color:var(--text-dark)">
              {{ $row['period']->format('F Y') }}
              @if($row['period']->isCurrentMonth())
                <span style="font-size:11px;font-weight:500;color:var(--terra);margin-left:6px">Current</span>
              @endif
            </td>
            <td style="text-align:center">
              {{ $row['landlord_count'] ?: '—' }}
            </td>
            <td style="text-align:center">
              {{ $row['lease_count'] ?: '—' }}
            </td>
            <td style="text-align:right;color:var(--text-mid);font-size:13px">
              ${{ number_format($stats['rate_minor'] / 100, 2) }}/lease
            </td>
            <td style="text-align:right;font-weight:600;color:var(--text-dark)">
              @if($row['total_minor'] > 0)
                ${{ number_format($row['total_minor'] / 100, 2) }}
              @else
                <span style="color:var(--text-light);font-weight:400">$0.00</span>
              @endif
            </td>
            <td style="text-align:center">
              <span class="bill-status {{ $row['status'] }}">
                {{ ucfirst($row['status']) }}
              </span>
            </td>
            <td style="text-align:right">
              <a href="{{ $url }}" class="db-table-link" style="font-size:12px" onclick="event.stopPropagation()">
                Detail →
              </a>
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
