@extends('dashboard.maintenance-portal.layout')
@section('page-title', 'Dashboard')
@section('content')
@if(!$team)
  <div class="db-card">
    <div class="db-card-body">
      <p style="color:var(--text-mid);margin:0 0 16px">Complete your team profile to use the maintenance dashboard.</p>
      <a href="{{ route('maint.team.edit') }}" class="db-btn db-btn-ghost">Set up team →</a>
    </div>
  </div>
@else
<div class="db-stats">
  <div class="db-stat">
    <div class="db-stat-label">Open jobs</div>
    <div class="db-stat-value">{{ $stats['open_requests'] + $stats['in_progress'] }}</div>
    <div class="db-stat-sub">{{ $stats['in_progress'] }} in progress</div>
  </div>
  <div class="db-stat">
    <div class="db-stat-label">Resolved (30d)</div>
    <div class="db-stat-value">{{ $stats['resolved_30d'] }}</div>
  </div>
  <div class="db-stat">
    <div class="db-stat-label">Outstanding invoices</div>
    <div class="db-stat-value">{{ $stats['outstanding_invoices'] }}</div>
  </div>
  <div class="db-stat">
    <div class="db-stat-label">Paid (30d)</div>
    <div class="db-stat-value">{{ number_format($stats['received_30d_minor'] /100,2) }}</div>
    <div class="db-stat-sub">Total collected</div>
  </div>
</div>

<div class="maint-quick-links" style="margin-bottom:20px">
  <a href="{{ route('maintenance.index') }}" class="maint-quick-link"><strong>Requests</strong><span>Status, photos, follow-ups</span></a>
  <a href="{{ route('maint.cities.index') }}" class="maint-quick-link"><strong>Operating cities</strong><span>{{ $stats['cities'] }} area{{ $stats['cities'] === 1 ? '' : 's' }} you serve</span></a>
  <a href="{{ route('maint.payments.invoices') }}" class="maint-quick-link"><strong>Invoices</strong><span>Bill landlords for completed work</span></a>
</div>

<div class="maint-grid-2">
  <div class="db-card">
    <div class="db-card-header"><span class="db-card-title">Recent requests</span><a href="{{ route('maintenance.index') }}" class="db-btn db-btn-ghost">View all</a></div>
    <div class="db-card-body" style="padding:0">
      @if($recentRequests->isEmpty())
        <p style="padding:22px;color:var(--text-light);margin:0">No assigned requests yet.</p>
      @else
      <div class="db-table-wrap">
        <table class="db-table">
          <thead><tr><th>Property</th><th>Title</th><th>Status</th></tr></thead>
          <tbody>
            @foreach($recentRequests as $mr)
            <tr>
              <td>{{ $mr->lease->property->name ?? '—' }}</td>
              <td><a href="{{ route('maintenance.show', $mr) }}" style="color:var(--text-dark);font-weight:500;text-decoration:none"><strong>{{ $mr->title }}</strong></a></td>
              <td><span class="badge badge-{{ match($mr->status){'resolved'=>'green','in_progress'=>'navy',default=>'terra'} }}">{{ ucfirst(str_replace('_',' ',$mr->status)) }}</span></td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      @endif
    </div>
  </div>
  <div class="db-card">
    <div class="db-card-header"><span class="db-card-title">Recent payments</span><a href="{{ route('maint.payments') }}" class="db-btn db-btn-ghost">View all</a></div>
    <div class="db-card-body" style="padding:0">
      @if($recentPayments->isEmpty())
        <p style="padding:22px;color:var(--text-light);margin:0">No payments logged yet.</p>
      @else
      <div class="db-table-wrap">
        <table class="db-table">
          <thead><tr><th>Date</th><th>Amount</th><th>Reference</th></tr></thead>
          <tbody>
            @foreach($recentPayments as $p)
            <tr>
              <td>{{ $p->paid_on->format('d M Y') }}</td>
              <td><strong>{{ $p->formattedAmount() }}</strong></td>
              <td>{{ $p->reference ?? $p->invoice?->invoice_number ?? '—' }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      @endif
    </div>
  </div>
</div>
<p style="font-size:14px;color:var(--text-light);margin:0">Linked landlords: <strong>{{ $stats['landlords_linked'] }}</strong> · Team: {{ $team->name }}</p>
@endif
@endsection
