@extends('admin.layout')
@section('title', 'Email templates')
@section('page-title', 'Email templates')
@section('breadcrumb', 'Operations')

@push('styles')
<style>
.tpl-status { display:inline-flex;font-size:11px;font-weight:600;padding:2px 9px;border-radius:20px;white-space:nowrap; }
.tpl-status.published { background:#e8f5e9;color:#2e7d32; }
.tpl-status.draft     { background:#f5f5f5;color:#9e9e9e; }
.tpl-trigger { font-size:12px;color:var(--text-light);margin-top:2px; }
.t-name { font-weight:600;color:var(--text-dark);text-decoration:none; }
.t-name:hover { color:var(--terra); }
.db-table tbody tr { cursor:pointer; }
.db-table tbody tr:hover td { background:var(--cream); }
.toggle-form { display:inline; }
</style>
@endpush

@section('content')

@if(session('success'))
  <div class="admin-portal-note" style="background:#e8f5e9;border-color:#a5d6a7;color:#2e7d32;margin-bottom:16px">
    ✓ {{ session('success') }}
  </div>
@endif

<p class="admin-portal-note">
  Platform-wide email templates automatically sent to tenants based on rent events. Landlords can
  enable/disable and optionally customise each published template. Unpublished templates are never
  sent.
</p>

{{-- Stats --}}
<div class="db-stats">
  <div class="db-stat terra">
    <div class="db-stat-label">Total templates</div>
    <div class="db-stat-value">{{ $stats['total'] }}</div>
    <div class="db-stat-sub">Across all triggers</div>
  </div>
  <div class="db-stat green">
    <div class="db-stat-label">Published</div>
    <div class="db-stat-value">{{ $stats['published'] }}</div>
    <div class="db-stat-sub">Active · being sent</div>
  </div>
  <div class="db-stat">
    <div class="db-stat-label">Draft</div>
    <div class="db-stat-value">{{ $stats['draft'] }}</div>
    <div class="db-stat-sub">Not yet live</div>
  </div>
  <div class="db-stat green">
    <div class="db-stat-label">Emails sent</div>
    <div class="db-stat-value">{{ number_format($stats['sent_total']) }}</div>
    <div class="db-stat-sub">All time</div>
  </div>
</div>

{{-- Table --}}
<div class="db-card">
  <div class="db-card-header">
    <span class="db-card-title">All templates</span>
    <a href="{{ route('admin.messages.create') }}" class="db-btn db-btn-primary" style="text-decoration:none;padding:6px 14px;font-size:13px">
      + New template
    </a>
  </div>
  <div class="db-card-body" style="padding:0">
    <table class="db-table">
      <thead>
        <tr>
          <th>Template</th>
          <th>Trigger</th>
          <th style="text-align:center">Landlord editable</th>
          <th style="text-align:center">Landlord disable</th>
          <th style="text-align:right">Sent</th>
          <th style="text-align:center">Status</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @forelse($templates as $tpl)
          @php $detailUrl = route('admin.messages.show', $tpl); @endphp
          <tr onclick="window.location='{{ $detailUrl }}'">
            <td>
              <a href="{{ $detailUrl }}" class="t-name" onclick="event.stopPropagation()">{{ $tpl->name }}</a>
              <div class="tpl-trigger" style="font-family:monospace;font-size:11px">{{ $tpl->slug }}</div>
            </td>
            <td>
              <div style="font-size:13px;color:var(--text-mid)">{{ $tpl->triggerLabel() }}</div>
            </td>
            <td style="text-align:center">
              @if($tpl->landlord_can_edit)
                <span style="color:#2e7d32;font-size:16px">✓</span>
              @else
                <span style="color:#bbb;font-size:14px">—</span>
              @endif
            </td>
            <td style="text-align:center">
              @if($tpl->landlord_can_disable)
                <span style="color:#2e7d32;font-size:16px">✓</span>
              @else
                <span style="color:#bbb;font-size:14px">—</span>
              @endif
            </td>
            <td style="text-align:right;color:var(--text-mid)">
              {{ number_format($tpl->sent_logs_count) }}
            </td>
            <td style="text-align:center" onclick="event.stopPropagation()">
              <form action="{{ route('admin.messages.publish', $tpl) }}" method="POST" class="toggle-form">
                @csrf @method('PATCH')
                <button type="submit" class="tpl-status {{ $tpl->is_published ? 'published' : 'draft' }}"
                        style="border:none;cursor:pointer;background:none;padding:0">
                  {{ $tpl->is_published ? 'Published' : 'Draft' }}
                </button>
              </form>
            </td>
            <td style="text-align:right" onclick="event.stopPropagation()">
              <a href="{{ route('admin.messages.edit', $tpl) }}" class="db-table-link" style="font-size:12px">Edit →</a>
            </td>
          </tr>
        @empty
          <tr><td colspan="7" style="text-align:center;padding:40px;color:var(--text-light)">
            No templates yet. <a href="{{ route('admin.messages.create') }}" class="db-table-link">Create one →</a>
          </td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

{{-- Variable reference --}}
<div class="db-card" style="margin-top:16px">
  <div class="db-card-header"><span class="db-card-title">Available template variables</span></div>
  <div class="db-card-body">
    <div style="display:flex;flex-wrap:wrap;gap:8px">
      @foreach(['tenant_first_name','tenant_name','landlord_first_name','landlord_name','property_name','property_address','rent_amount','currency_code','due_date','due_day','days_until_due','days_overdue','late_fee_amount','lease_end_date','days_until_expiry','platform_name'] as $var)
        <code style="background:var(--cream);padding:3px 8px;border-radius:5px;font-size:12px;color:var(--text-dark)">{{ '{{' . $var . '}}' }}</code>
      @endforeach
    </div>
    <p style="margin:10px 0 0;font-size:12px;color:var(--text-light)">
      Use these placeholders in subject and body. They are replaced with real values at send time.
    </p>
  </div>
</div>

@endsection
