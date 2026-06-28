@extends('dashboard.layout')
@section('page-title', 'Billing')

@push('styles')
<style>
.billing-status-due      { color:#b45309;font-weight:600 }
.billing-status-paid     { color:#166534;font-weight:600 }
.billing-status-upcoming { color:var(--text-light);font-weight:500 }
</style>
@endpush

@section('content')
@php
use App\Support\CurrencyDisplay;

$badgeClass = ['due' => 'badge-gold', 'paid' => 'badge-green', 'upcoming' => 'badge-grey'];
$badgeLabel = ['due' => 'Due', 'paid' => 'Paid', 'upcoming' => 'Upcoming'];
@endphp

<div class="db-stats" style="margin-bottom:28px">
  <div class="db-stat">
    <div class="db-stat-label">Rate per lease</div>
    <div class="db-stat-value">$9<span style="font-size:14px;color:var(--text-light)">/mo</span></div>
  </div>
  @if(count($months) > 0)
  <div class="db-stat">
    <div class="db-stat-label">Current month</div>
    <div class="db-stat-value">${{ number_format($months[0]['total'] / 100, 2) }}</div>
    <div style="font-size:11px;color:var(--text-light);margin-top:2px">{{ $months[0]['count'] }} lease{{ $months[0]['count'] !== 1 ? 's' : '' }}</div>
  </div>
  @endif
</div>

<div class="db-card">
  <div class="db-card-header">
    <span class="db-card-title">Monthly billing</span>
  </div>
  <div class="db-table-wrap">
    <table class="db-table">
      <thead>
        <tr>
          <th>Period</th>
          <th style="text-align:center">Active leases</th>
          <th style="text-align:center">Rate</th>
          <th style="text-align:right">Amount</th>
          <th style="text-align:center">Status</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @forelse($months as $row)
        <tr style="cursor:pointer" onclick="window.location='{{ route('billing.show', [$row['period']->year, $row['period']->month]) }}'">
          <td>
            <strong>{{ $row['period']->format('F Y') }}</strong>
            @if($row['period']->isCurrentMonth())
              <span style="font-size:11px;color:var(--terra);margin-left:6px">Current</span>
            @endif
          </td>
          <td style="text-align:center">{{ $row['count'] }}</td>
          <td style="text-align:center;color:var(--text-light)">$9.00</td>
          <td style="text-align:right;font-weight:600">${{ number_format($row['total'] / 100, 2) }}</td>
          <td style="text-align:center">
            <span class="badge {{ $badgeClass[$row['status']] }}">{{ $badgeLabel[$row['status']] }}</span>
          </td>
          <td style="text-align:right">
            <a href="{{ route('billing.show', [$row['period']->year, $row['period']->month]) }}" class="db-table-link" onclick="event.stopPropagation()">View →</a>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="6" style="text-align:center;padding:48px;color:var(--text-light)">No billing history yet — billing starts when your first lease is active.</td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
