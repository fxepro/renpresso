@extends('dashboard.layout')
@section('page-title', 'Background checks')
@section('breadcrumb', 'Portfolio')

@section('content')
@php
  $total = $statusCounts->sum();
  $pending = (int) ($statusCounts['pending'] ?? 0) + (int) ($statusCounts['requested'] ?? 0);
  $manual = (int) ($statusCounts['manual_review'] ?? 0);
  $passed = (int) ($statusCounts['passed'] ?? 0);
  $failed = (int) ($statusCounts['failed'] ?? 0);
  $filterParams = array_filter(['property' => $propertyId]);
@endphp

@if($total > 0)
<div class="db-stats" style="margin-bottom:28px">
  <div class="db-stat {{ $openChecks > 0 ? 'terra' : '' }}">
    <div class="db-stat-label">Open</div>
    <div class="db-stat-value">{{ $openChecks }}</div>
    <div class="db-stat-sub">Requested · pending · review</div>
  </div>
  <div class="db-stat green">
    <div class="db-stat-label">Passed</div>
    <div class="db-stat-value">{{ $passed }}</div>
    <div class="db-stat-sub">Cleared</div>
  </div>
  <div class="db-stat {{ $failed > 0 ? 'terra' : '' }}">
    <div class="db-stat-label">Failed</div>
    <div class="db-stat-value">{{ $failed }}</div>
    <div class="db-stat-sub">Did not clear</div>
  </div>
  <div class="db-stat">
    <div class="db-stat-label">All checks</div>
    <div class="db-stat-value">{{ $total }}</div>
    <div class="db-stat-sub">Portfolio-wide</div>
  </div>
</div>
@endif

<div class="db-card">
  <div class="db-card-header" style="flex-wrap:wrap;gap:12px">
    <span class="db-card-title">All background checks</span>
    <form method="GET" action="{{ route('background-checks.index') }}" style="display:flex;align-items:center;gap:10px;margin-left:auto;flex-wrap:wrap">
      @if($status)<input type="hidden" name="status" value="{{ $status }}">@endif
      <select name="property" class="db-input" style="width:auto;padding:8px 12px;min-width:180px" onchange="this.form.submit()">
        <option value="">All properties</option>
        @foreach($properties as $property)
          <option value="{{ $property->id }}" @selected($propertyId === $property->id)>{{ $property->name }}</option>
        @endforeach
      </select>
    </form>
  </div>

  <nav class="mt-tabs" aria-label="Check status" style="display:flex;gap:4px;padding:0 24px;border-bottom:1px solid var(--cream-dark);background:var(--cream);flex-wrap:wrap">
    @foreach([
      null => 'All',
      'requested' => 'Requested',
      'pending' => 'Pending',
      'manual_review' => 'Manual review',
      'passed' => 'Passed',
      'failed' => 'Failed',
    ] as $key => $label)
      @php $active = ($status === $key) || ($key === null && ! $status); @endphp
      <a href="{{ route('background-checks.index', array_merge($filterParams, $key ? ['status' => $key] : [])) }}"
         class="mt-tab {{ $active ? 'active' : '' }}"
         style="padding:14px 18px;font-weight:500;color:{{ $active ? 'var(--terra)' : 'var(--text-light)' }};text-decoration:none;border-bottom:2px solid {{ $active ? 'var(--terra)' : 'transparent' }};margin-bottom:-1px">
        {{ $label }}
        @if($key && ($statusCounts[$key] ?? 0) > 0)
          <span style="margin-left:6px;font-size:12px;opacity:0.85">({{ $statusCounts[$key] }})</span>
        @endif
      </a>
    @endforeach
  </nav>

  @if($checks->isEmpty())
    <div class="db-empty" style="min-height:50vh">
      <div class="db-empty-icon">🔍</div>
      <h3>No background checks{{ $status ? ' · '.str_replace('_', ' ', $status) : '' }}.</h3>
      <p>Request screening from an application on any property.</p>
      <a href="{{ route('applications.index') }}" class="db-btn db-btn-ghost" style="margin-right:8px">View applications</a>
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
          <th>Check type</th>
          <th>Method</th>
          <th>Status</th>
          <th>Updated</th>
          <th>Completed</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @foreach($checks as $check)
        @php
          $property = $check->property;
          $application = $check->application;
        @endphp
        <tr>
          <td>
            @if($application)
              <div style="font-weight:500;color:var(--text-dark)">{{ $application->fullName() }}</div>
              <div style="font-size:11px;color:var(--text-light)">{{ $application->email }}</div>
            @else
              <span style="color:var(--text-light)">—</span>
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
          <td>{{ $application?->target_unit_label ?: '—' }}</td>
          <td><span class="badge badge-navy">{{ $check->typeLabel() }}</span></td>
          <td style="font-size:13px;color:var(--text-mid)">{{ $check->methodLabel() }}</td>
          <td><span class="badge badge-{{ $check->statusColor() }}">{{ $check->statusLabel() }}</span></td>
          <td>{{ $check->updated_at->format('d M Y') }}</td>
          <td>{{ $check->completed_at?->format('d M Y') ?? '—' }}</td>
          <td>
            <a href="{{ route('properties.index', ['open' => $property->id, 'tab' => 'background', 'application' => $application?->id]) }}" class="db-btn db-btn-ghost" style="font-size:12px;padding:5px 10px;white-space:nowrap">Open property</a>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @if($checks->hasPages())
  <div style="padding:16px 20px;border-top:1px solid var(--cream-dark)">
    {{ $checks->links() }}
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
