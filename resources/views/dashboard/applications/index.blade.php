@extends('dashboard.layout')
@section('page-title', 'Applications')
@section('breadcrumb', 'Portfolio')

@section('content')
@php
  $total = $statusCounts->sum();
  $pending = (int) ($statusCounts['pending'] ?? 0);
  $reviewing = (int) ($statusCounts['reviewing'] ?? 0);
  $approved = (int) ($statusCounts['approved'] ?? 0);
  $rejected = (int) ($statusCounts['rejected'] ?? 0);
  $filterParams = array_filter(['property' => $propertyId]);
@endphp

@if($total > 0)
<div class="db-stats" style="margin-bottom:28px">
  <div class="db-stat {{ $pending > 0 ? 'terra' : '' }}">
    <div class="db-stat-label">Pending</div>
    <div class="db-stat-value">{{ $pending }}</div>
    <div class="db-stat-sub">Awaiting review</div>
  </div>
  <div class="db-stat">
    <div class="db-stat-label">Reviewing</div>
    <div class="db-stat-value">{{ $reviewing }}</div>
    <div class="db-stat-sub">In progress</div>
  </div>
  <div class="db-stat green">
    <div class="db-stat-label">Approved</div>
    <div class="db-stat-value">{{ $approved }}</div>
    <div class="db-stat-sub">Ready for lease</div>
  </div>
  <div class="db-stat">
    <div class="db-stat-label">All applications</div>
    <div class="db-stat-value">{{ $total }}</div>
    <div class="db-stat-sub">Across {{ $properties->count() }} propert{{ $properties->count() === 1 ? 'y' : 'ies' }}</div>
  </div>
</div>
@endif

<div class="db-card">
  <div class="db-card-header" style="flex-wrap:wrap;gap:12px">
    <span class="db-card-title">All applications</span>
    <form method="GET" action="{{ route('applications.index') }}" style="display:flex;align-items:center;gap:10px;margin-left:auto;flex-wrap:wrap">
      @if($status)<input type="hidden" name="status" value="{{ $status }}">@endif
      <select name="property" class="db-input" style="width:auto;padding:8px 12px;min-width:180px" onchange="this.form.submit()">
        <option value="">All properties</option>
        @foreach($properties as $property)
          <option value="{{ $property->id }}" @selected($propertyId === $property->id)>{{ $property->name }}</option>
        @endforeach
      </select>
    </form>
  </div>

  <nav class="mt-tabs" aria-label="Application status" style="display:flex;gap:4px;padding:0 24px;border-bottom:1px solid var(--cream-dark);background:var(--cream);flex-wrap:wrap">
    @foreach([
      null => 'All',
      'pending' => 'Pending',
      'reviewing' => 'Reviewing',
      'approved' => 'Approved',
      'rejected' => 'Rejected',
    ] as $key => $label)
      @php $active = ($status === $key) || ($key === null && ! $status); @endphp
      <a href="{{ route('applications.index', array_merge($filterParams, $key ? ['status' => $key] : [])) }}"
         class="mt-tab {{ $active ? 'active' : '' }}"
         style="padding:14px 18px;font-weight:500;color:{{ $active ? 'var(--terra)' : 'var(--text-light)' }};text-decoration:none;border-bottom:2px solid {{ $active ? 'var(--terra)' : 'transparent' }};margin-bottom:-1px">
        {{ $label }}
        @if($key && ($statusCounts[$key] ?? 0) > 0)
          <span style="margin-left:6px;font-size:12px;opacity:0.85">({{ $statusCounts[$key] }})</span>
        @endif
      </a>
    @endforeach
  </nav>

  @if($applications->isEmpty())
    <div class="db-empty" style="min-height:50vh">
      <div class="db-empty-icon">📋</div>
      <h3>No applications{{ $status ? ' · '.ucfirst($status) : '' }}.</h3>
      <p>Add applicants from a property panel, or share your listing link.</p>
      <a href="{{ route('properties.index') }}" class="db-btn db-btn-primary">Go to properties</a>
    </div>
  @else
  <div class="db-table-wrap">
    <table class="db-table">
      <thead>
        <tr>
          <th>Applicant</th>
          <th>Property</th>
          <th>Unit</th>
          <th>Move-in</th>
          <th>Income</th>
          <th>Checks</th>
          <th>Status</th>
          <th>Applied</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @foreach($applications as $application)
        @php $property = $application->property; @endphp
        <tr>
          <td>
            <div style="font-weight:500;color:var(--text-dark)">{{ $application->fullName() }}</div>
            <div style="font-size:11px;color:var(--text-light)">{{ $application->email }}</div>
            @if($application->phone)
              <div style="font-size:11px;color:var(--text-light)">{{ $application->phone }}</div>
            @endif
          </td>
          <td>
            <div class="db-flag-name">
              <span class="db-flag">{{ config('countries.'.$property->country_code.'.flag', '🏠') }}</span>
              <div>
                <div class="db-name">{{ $property->name }}</div>
                <div class="db-sub">{{ $property->city }}</div>
              </div>
            </div>
          </td>
          <td>{{ $application->target_unit_label ?: '—' }}</td>
          <td>{{ $application->move_in_date?->format('d M Y') ?? '—' }}</td>
          <td>{{ $application->formattedIncome() }}</td>
          <td style="text-align:center">
            @if($application->backgroundChecks->count() > 0)
              <a href="{{ route('background-checks.index', ['property' => $property->id]) }}" class="db-table-link">{{ $application->backgroundChecks->count() }}</a>
            @else
              <span style="color:var(--text-light)">0</span>
            @endif
          </td>
          <td><span class="badge badge-{{ $application->statusColor() }}">{{ $application->statusLabel() }}</span></td>
          <td>{{ $application->created_at->format('d M Y') }}</td>
          <td>
            <a href="{{ route('properties.index', ['open' => $property->id, 'tab' => 'applications', 'application' => $application->id]) }}" class="db-btn db-btn-ghost" style="font-size:12px;padding:5px 10px;white-space:nowrap">Open property</a>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @if($applications->hasPages())
  <div style="padding:16px 20px;border-top:1px solid var(--cream-dark)">
    {{ $applications->links() }}
  </div>
  @endif
  @endif
</div>
@endsection

@push('styles')
<style>
.mt-tab:hover { color: var(--text-dark); }
</style>
@endpush
