@extends('dashboard.layout')
@section('page-title', 'Tenants')
@section('content')
@php $stats = $stats ?? ['total' => 0, 'active_lease' => 0, 'mandate_active' => 0, 'mandate_pending' => 0, 'inactive' => 0]; @endphp

@if($stats['total'] > 0)
<div class="db-stats">
  <div class="db-stat">
    <div class="db-stat-label">Total tenants</div>
    <div class="db-stat-value">{{ $stats['total'] }}</div>
    <div class="db-stat-sub">On your properties</div>
  </div>
  <div class="db-stat green">
    <div class="db-stat-label">Active lease</div>
    <div class="db-stat-value">{{ $stats['active_lease'] }}</div>
    <div class="db-stat-sub">{{ $stats['inactive'] }} without active lease</div>
  </div>
  <div class="db-stat green">
    <div class="db-stat-label">Mandate active</div>
    <div class="db-stat-value">{{ $stats['mandate_active'] }}</div>
    <div class="db-stat-sub">Ready to collect rent</div>
  </div>
  <div class="db-stat {{ $stats['mandate_pending'] > 0 ? 'terra' : '' }}">
    <div class="db-stat-label">Mandate pending</div>
    <div class="db-stat-value">{{ $stats['mandate_pending'] }}</div>
    <div class="db-stat-sub">Invite or setup outstanding</div>
  </div>
</div>
@endif

@if($tenants->isEmpty())
  <div class="db-empty" style="min-height:60vh">
    <div class="db-empty-icon">👥</div>
    <h3>No tenants yet.</h3>
    <p>Tenants are added when you create a lease.</p>
    <a href="{{ route('properties.index') }}" class="db-btn db-btn-primary">Go to properties</a>
  </div>
@else
<div class="db-card">
  <div class="db-table-wrap">
    <table class="db-table">
      <thead><tr><th class="db-col-num">#</th><th>Tenant</th><th>Property</th><th>Unit</th><th>Rent</th><th>Mandate</th><th>Status</th><th></th></tr></thead>
      <tbody>
        @foreach($tenants as $t)
        @php $lease = $t->leases->where('status','active')->first() ?? $t->leases->first(); @endphp
        <tr>
          <td class="db-col-num">{{ $loop->iteration }}</td>
          <td>
            <div style="display:flex;align-items:center;gap:9px">
              <div style="width:28px;height:28px;border-radius:50%;background:var(--navy-mid);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:var(--white);flex-shrink:0">{{ strtoupper(substr($t->first_name,0,1)) }}</div>
              <div><div style="font-weight:500;color:var(--text-dark)">{{ $t->first_name }} {{ $t->last_name }}</div><div style="font-size:11px;color:var(--text-light)">{{ $t->email }}</div></div>
            </div>
          </td>
          <td>@if($lease)<span>{{ $lease->property->name }}</span><div style="font-size:12px;color:var(--text-light)">{{ $lease->property->country_code }}</div>@else<span style="color:var(--text-light)">—</span>@endif</td>
          <td>@if($lease)<span style="color:var(--text-light);font-size:13px">{{ $lease->property->isMultiUnit() ? $lease->displayUnit() : $lease->displayUnitLabel() }}</span>@else —@endif</td>
          <td>@if($lease)<strong>{{ number_format($lease->rent_minor_units/100,2) }} {{ $lease->currency_code }}</strong>@else —@endif</td>
          <td>@if($lease && $lease->mandates->where('status','active')->count())<span class="badge badge-green">Active</span>@else<span class="badge badge-gold">Pending</span>@endif</td>
          <td><span class="badge badge-{{ $lease?->status==='active'?'green':'grey' }}">{{ ucfirst($lease?->status ?? 'inactive') }}</span></td>
          <td>@if($lease)<a href="{{ route('leases.show',$lease) }}" class="db-btn db-btn-ghost" style="font-size:12px;padding:5px 10px">View lease</a>@endif</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endif
@endsection
