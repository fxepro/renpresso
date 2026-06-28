{{-- resources/views/dashboard/leases/index.blade.php --}}
@extends('dashboard.layout')
@section('page-title', 'Leases')
@section('content')
@php $stats = $stats ?? ['total' => 0, 'active' => 0, 'draft' => 0, 'inactive' => 0, 'active_mandates' => 0, 'pending_mandates' => 0, 'properties_leased' => 0]; @endphp

@if($stats['total'] > 0)
<div class="db-stats">
  <div class="db-stat">
    <div class="db-stat-label">Total leases</div>
    <div class="db-stat-value">{{ $stats['total'] }}</div>
    <div class="db-stat-sub">{{ $stats['properties_leased'] }} properties with leases</div>
  </div>
  <div class="db-stat green">
    <div class="db-stat-label">Active</div>
    <div class="db-stat-value">{{ $stats['active'] }}</div>
    <div class="db-stat-sub">{{ $stats['draft'] }} draft · {{ $stats['inactive'] }} ended</div>
  </div>
  <div class="db-stat green">
    <div class="db-stat-label">Auto-pay active</div>
    <div class="db-stat-value">{{ $stats['active_mandates'] }}</div>
    <div class="db-stat-sub">Payment method on file</div>
  </div>
  <div class="db-stat {{ $stats['pending_mandates'] > 0 ? 'terra' : '' }}">
    <div class="db-stat-label">Auto-pay pending</div>
    <div class="db-stat-value">{{ $stats['pending_mandates'] }}</div>
    <div class="db-stat-sub">Active lease, setup not complete</div>
  </div>
</div>
@endif

@if($leases->isEmpty())
  <div class="db-empty" style="min-height:60vh">
    <div class="db-empty-icon">📋</div>
    <h3>No leases yet.</h3>
    <p>Add a property first, then create a lease to invite your tenant.</p>
    <a href="{{ route('properties.index') }}" class="db-btn db-btn-primary">Go to properties</a>
  </div>
@else
<div class="db-card">
  <div class="db-table-wrap">
    <table class="db-table">
      <thead>
        <tr>
          <th class="db-col-num">#</th>
          <th>Property</th>
          <th>Unit</th>
          <th>Tenant</th>
          <th>Country</th>
          <th>Rent</th>
          <th>Due</th>
          <th>Start</th>
          <th>Auto-pay</th>
          <th>Status</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @foreach($leases as $l)
        @php $mandateActive = $l->status === 'active' && $l->mandates->contains('status', 'active'); @endphp
        <tr>
          <td class="db-col-num">{{ $loop->iteration }}</td>
          <td>
            <div class="db-name">{{ $l->property->name }}</div>
            <div class="db-sub">{{ $l->property->city }}</div>
          </td>
          <td style="color:var(--text-light);font-size:13px">{{ $l->property->isMultiUnit() ? $l->displayUnit() : $l->displayUnitLabel() }}</td>
          <td>
            <strong>{{ $l->tenant->first_name }} {{ $l->tenant->last_name }}</strong>
            <div class="db-sub">{{ $l->tenant->email }}</div>
          </td>
          <td>{{ $l->property->country_code }}</td>
          <td><strong>{{ number_format($l->rent_minor_units/100,2) }} {{ $l->currency_code }}</strong></td>
          <td>{{ \App\Models\Lease::ordinalDay((int) $l->due_day) }}</td>
          <td>{{ $l->start_date->format('d M Y') }}</td>
          <td>
            @if($l->status !== 'active')
              <span style="color:var(--text-light)">—</span>
            @elseif($mandateActive)
              <span class="badge badge-green">Active</span>
            @else
              <span class="badge badge-gold">Pending</span>
            @endif
          </td>
          <td><span class="badge badge-{{ $l->status==='active'?'green':($l->status==='expired'?'grey':'gold') }}">{{ ucfirst($l->status) }}</span></td>
          <td><a href="{{ route('leases.show',$l) }}" class="db-btn db-btn-ghost" style="font-size:12px;padding:5px 10px">View</a></td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endif
@endsection
