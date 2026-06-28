@extends('dashboard.layout')
@section('title', 'Email templates')
@section('page-title', 'Email templates')

@push('styles')
<style>
.tpl-card {
  background:var(--white);
  border:1px solid var(--cream-dark);
  border-radius:10px;
  padding:18px 20px;
  display:flex;
  align-items:flex-start;
  gap:16px;
  transition:border-color .15s;
}
.tpl-card.disabled { opacity:.6; }
.tpl-card:hover { border-color:var(--terra); }
.tpl-icon { font-size:22px;margin-top:2px;flex-shrink:0; }
.tpl-name { font-size:14px;font-weight:700;color:var(--text-dark); }
.tpl-trigger { font-size:12px;color:var(--text-light);margin-top:2px; }
.tpl-subject { font-size:12px;color:var(--text-mid);margin-top:6px;font-style:italic; }
.tpl-actions { margin-left:auto;display:flex;flex-direction:column;align-items:flex-end;gap:8px;flex-shrink:0; }
.pill-on  { display:inline-flex;font-size:11px;font-weight:600;padding:2px 10px;border-radius:20px;background:#e8f5e9;color:#2e7d32; }
.pill-off { display:inline-flex;font-size:11px;font-weight:600;padding:2px 10px;border-radius:20px;background:#fce4ec;color:#b71c1c; }
.pill-custom { display:inline-flex;font-size:10px;font-weight:600;padding:1px 8px;border-radius:20px;background:#e3f2fd;color:#1565c0; }
.log-row { display:flex;align-items:center;gap:12px;padding:8px 0;border-bottom:1px solid var(--cream-dark); }
.log-row:last-child { border-bottom:none; }
.log-status { display:inline-flex;font-size:10px;font-weight:600;padding:1px 7px;border-radius:20px; }
.log-status.sent    { background:#e8f5e9;color:#2e7d32; }
.log-status.failed  { background:#fce4ec;color:#b71c1c; }
.log-status.skipped { background:#f5f5f5;color:#9e9e9e; }
</style>
@endpush

@section('content')
<p class="db-page-sub">Automated emails sent to your tenants based on rent events. Enable, disable, or customise each one.</p>

@if(session('success'))
  <div class="db-alert db-alert-success" style="margin-bottom:16px">{{ session('success') }}</div>
@endif

@if($templates->isEmpty())
  <div class="db-card">
    <div class="db-card-body" style="text-align:center;padding:60px 20px;color:var(--text-light)">
      <div style="font-size:40px;margin-bottom:12px">📭</div>
      <div style="font-weight:600;color:var(--text-dark)">No email templates published yet</div>
      <div style="font-size:13px;margin-top:6px">Your admin will publish templates for you to manage here.</div>
    </div>
  </div>
@else

  <div style="display:flex;flex-direction:column;gap:12px;margin-bottom:24px">
    @foreach($templates as $tpl)
      @php
        $pref      = $prefs->get($tpl->id);
        $isEnabled = $pref ? $pref->is_enabled : true;
        $hasCustom = $pref && ($pref->subject_override || $pref->body_html_override);
        $subject   = $pref?->subject_override ?: $tpl->subject;
      @endphp
      <div class="tpl-card {{ $isEnabled ? '' : 'disabled' }}">
        <div class="tpl-icon">
          @php
            $icon = match(true) {
              str_contains($tpl->trigger_event, 'overdue') => '⚠️',
              str_contains($tpl->trigger_event, 'success') => '✅',
              str_contains($tpl->trigger_event, 'failed')  => '❌',
              str_contains($tpl->trigger_event, 'expiry')  => '📅',
              str_contains($tpl->trigger_event, 'late_fee')=> '💸',
              default => '📧',
            };
          @endphp
          {{ $icon }}
        </div>
        <div style="flex:1;min-width:0">
          <div class="tpl-name">{{ $tpl->name }}</div>
          <div class="tpl-trigger">{{ $tpl->triggerLabel() }}</div>
          <div class="tpl-subject">Subject: {{ $subject }}</div>
          @if($hasCustom)
            <span class="pill-custom" style="margin-top:6px">Customised</span>
          @endif
        </div>
        <div class="tpl-actions">
          <span class="{{ $isEnabled ? 'pill-on' : 'pill-off' }}">
            {{ $isEnabled ? 'On' : 'Off' }}
          </span>
          @if($tpl->landlord_can_edit || $tpl->landlord_can_disable)
            <a href="{{ route('landlord.communication.edit', $tpl) }}" class="db-table-link" style="font-size:12px">
              Manage →
            </a>
          @else
            <span style="font-size:11px;color:var(--text-light)">Mandatory</span>
          @endif
        </div>
      </div>
    @endforeach
  </div>

@endif

{{-- Recent send log --}}
@if($recentLogs->isNotEmpty())
  <div class="db-card">
    <div class="db-card-header">
      <span class="db-card-title">Recent emails sent</span>
      <span class="db-card-sub">Last 20</span>
    </div>
    <div class="db-card-body">
      @foreach($recentLogs as $log)
        <div class="log-row">
          <div style="flex:1;min-width:0">
            <div style="font-size:13px;font-weight:600;color:var(--text-dark)">{{ $log->subject_sent }}</div>
            <div style="font-size:11px;color:var(--text-light)">{{ $log->to_email }} · {{ $log->template?->name }}</div>
          </div>
          <span class="log-status {{ $log->status }}">{{ ucfirst($log->status) }}</span>
          <div style="font-size:11px;color:var(--text-light);white-space:nowrap">
            {{ $log->sent_at?->diffForHumans() ?? '—' }}
          </div>
        </div>
      @endforeach
    </div>
  </div>
@endif

@endsection
