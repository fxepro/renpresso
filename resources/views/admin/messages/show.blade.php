@extends('admin.layout')
@section('title', $emailTemplate->name)
@section('page-title', $emailTemplate->name)
@section('breadcrumb', 'Operations')

@push('styles')
<style>
.log-status { display:inline-flex;font-size:11px;font-weight:600;padding:2px 8px;border-radius:20px; }
.log-status.sent    { background:#e8f5e9;color:#2e7d32; }
.log-status.failed  { background:#fce4ec;color:#b71c1c; }
.log-status.skipped { background:#f5f5f5;color:#9e9e9e; }
.tpl-published { background:#e8f5e9;color:#2e7d32;display:inline-flex;font-size:11px;font-weight:600;padding:2px 9px;border-radius:20px; }
.tpl-draft     { background:#f5f5f5;color:#9e9e9e;display:inline-flex;font-size:11px;font-weight:600;padding:2px 9px;border-radius:20px; }
.preview-box { background:var(--cream);border-radius:8px;padding:20px;font-size:14px;line-height:1.6; }
</style>
@endpush

@section('content')

@if(session('success'))
  <div class="admin-portal-note" style="background:#e8f5e9;border-color:#a5d6a7;color:#2e7d32;margin-bottom:16px">
    ✓ {{ session('success') }}
  </div>
@endif

<div style="margin-bottom:16px;display:flex;gap:10px;align-items:center">
  <a href="{{ route('admin.messages') }}" class="db-btn db-btn-ghost" style="text-decoration:none">← All templates</a>
  <a href="{{ route('admin.messages.edit', $emailTemplate) }}" class="db-btn db-btn-ghost" style="text-decoration:none">Edit</a>
  <form action="{{ route('admin.messages.publish', $emailTemplate) }}" method="POST" style="display:inline;margin-left:auto">
    @csrf @method('PATCH')
    <button type="submit" class="db-btn db-btn-primary">
      {{ $emailTemplate->is_published ? 'Unpublish' : 'Publish' }}
    </button>
  </form>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px">

  {{-- Meta --}}
  <div class="db-card">
    <div class="db-card-header"><span class="db-card-title">Template details</span></div>
    <div class="db-card-body">
      <table style="width:100%;border-collapse:collapse;font-size:13px">
        @foreach([
          ['Slug',          '<code style="background:var(--cream);padding:2px 6px;border-radius:4px">'. $emailTemplate->slug .'</code>'],
          ['Trigger',       $emailTemplate->triggerLabel()],
          ['Status',        $emailTemplate->is_published ? '<span class="tpl-published">Published</span>' : '<span class="tpl-draft">Draft</span>'],
          ['Landlord edit', $emailTemplate->landlord_can_edit ? '✓ Yes' : '✗ No'],
          ['Landlord off',  $emailTemplate->landlord_can_disable ? '✓ Yes' : '✗ No'],
          ['Total sent',    number_format($emailTemplate->sentLogs()->where('status','sent')->count())],
          ['Created',       $emailTemplate->created_at->format('d M Y')],
        ] as [$label,$value])
          <tr style="border-bottom:1px solid var(--cream-dark)">
            <td style="padding:8px 0;color:var(--text-light);width:40%">{{ $label }}</td>
            <td style="padding:8px 0;font-weight:500">{!! $value !!}</td>
          </tr>
        @endforeach
      </table>
    </div>
  </div>

  {{-- Subject preview --}}
  <div class="db-card">
    <div class="db-card-header"><span class="db-card-title">Subject line</span></div>
    <div class="db-card-body">
      <div style="font-size:15px;font-weight:600;color:var(--text-dark);margin-bottom:12px">
        {{ $emailTemplate->subject }}
      </div>
      <div class="db-card-header" style="padding:0;border:none;margin-top:16px"><span class="db-card-title" style="font-size:12px">Body preview</span></div>
      <div class="preview-box" style="margin-top:8px">
        {!! $emailTemplate->body_html !!}
      </div>
    </div>
  </div>

</div>

{{-- Send log --}}
<div class="db-card">
  <div class="db-card-header">
    <span class="db-card-title">Send log</span>
    <span class="db-card-sub">{{ $logs->total() }} entries</span>
  </div>
  <div class="db-card-body" style="padding:0">
    @if($logs->isEmpty())
      <div style="text-align:center;padding:40px;color:var(--text-light)">
        <div style="font-size:30px;margin-bottom:10px">📭</div>
        No emails sent yet for this template.
      </div>
    @else
      <table class="db-table">
        <thead>
          <tr>
            <th>Tenant</th>
            <th>Landlord</th>
            <th>Subject sent</th>
            <th>Trigger date</th>
            <th style="text-align:center">Status</th>
            <th>Sent at</th>
          </tr>
        </thead>
        <tbody>
          @foreach($logs as $log)
            <tr>
              <td style="font-size:13px">
                {{ $log->tenant?->first_name }} {{ $log->tenant?->last_name }}
                <div style="font-size:11px;color:var(--text-light)">{{ $log->to_email }}</div>
              </td>
              <td style="font-size:13px;color:var(--text-mid)">
                {{ $log->landlord?->first_name }} {{ $log->landlord?->last_name }}
              </td>
              <td style="font-size:12px;color:var(--text-mid);max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                {{ $log->subject_sent }}
              </td>
              <td style="font-size:13px;color:var(--text-mid)">
                {{ $log->trigger_date?->format('d M Y') ?? '—' }}
              </td>
              <td style="text-align:center">
                <span class="log-status {{ $log->status }}">{{ ucfirst($log->status) }}</span>
              </td>
              <td style="font-size:12px;color:var(--text-light)">
                {{ $log->sent_at?->format('d M H:i') ?? '—' }}
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
      <div style="padding:14px 20px;border-top:1px solid var(--cream-dark)">
        {{ $logs->links() }}
      </div>
    @endif
  </div>
</div>

@endsection
