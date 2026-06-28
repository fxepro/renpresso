@extends('dashboard.layout')
@section('title', 'Manage — ' . $emailTemplate->name)
@section('page-title', $emailTemplate->name)
@section('breadcrumb', $emailTemplate->triggerLabel())

@push('styles')
<style>
.form-group { margin-bottom:18px; }
.form-label { display:block;font-size:13px;font-weight:600;color:var(--text-dark);margin-bottom:5px; }
.form-label span { font-weight:400;color:var(--text-light);font-size:12px;margin-left:4px; }
.form-input { width:100%;padding:9px 12px;border:1px solid var(--cream-dark);border-radius:8px;font-size:14px;color:var(--text-dark);background:var(--white);box-sizing:border-box; }
.form-input:focus { outline:none;border-color:var(--terra); }
.form-textarea { min-height:220px;font-family:monospace;font-size:13px;resize:vertical; }
.form-hint { font-size:11px;color:var(--text-light);margin-top:4px; }
.toggle-row { display:flex;align-items:center;justify-content:space-between;padding:14px 0;border-bottom:1px solid var(--cream-dark); }
.toggle-label { font-size:14px;font-weight:600;color:var(--text-dark); }
.toggle-sub   { font-size:12px;color:var(--text-light);margin-top:2px; }
.toggle-switch { position:relative;width:42px;height:24px;flex-shrink:0; }
.toggle-switch input { opacity:0;width:0;height:0; }
.toggle-slider {
  position:absolute;inset:0;border-radius:24px;background:#ccc;cursor:pointer;transition:.2s;
}
.toggle-slider:before {
  content:'';position:absolute;height:18px;width:18px;left:3px;bottom:3px;
  background:#fff;border-radius:50%;transition:.2s;
}
input:checked + .toggle-slider { background:var(--terra); }
input:checked + .toggle-slider:before { transform:translateX(18px); }
.default-preview { background:var(--cream);border-radius:8px;padding:14px;font-size:13px;line-height:1.6;color:var(--text-mid); }
.var-chip { font-size:11px;background:var(--cream);padding:2px 7px;border-radius:4px;cursor:pointer;color:var(--text-dark);border:1px solid var(--cream-dark);display:inline-block;margin:2px; }
.var-chip:hover { background:var(--terra);color:#fff;border-color:var(--terra); }
</style>
@endpush

@section('content')
<p style="margin:0 0 20px">
  <a href="{{ route('landlord.communication.index') }}" class="db-table-link" style="font-size:13px">← Email templates</a>
</p>

<form method="POST" action="{{ route('landlord.communication.update', $emailTemplate) }}">
@csrf @method('PUT')
<div style="display:grid;grid-template-columns:3fr 2fr;gap:20px;align-items:start">

  {{-- Left: content --}}
  <div style="display:flex;flex-direction:column;gap:16px">

    {{-- Enable/disable toggle --}}
    @if($emailTemplate->landlord_can_disable)
      <div class="db-card">
        <div class="db-card-body">
          <div class="toggle-row" style="border:none;padding:0">
            <div>
              <div class="toggle-label">Send this email</div>
              <div class="toggle-sub">When off, tenants will NOT receive this notification</div>
            </div>
            <label class="toggle-switch">
              <input type="checkbox" name="is_enabled" value="1" id="toggle_enabled"
                     {{ (old('is_enabled', $pref->is_enabled ?? true)) ? 'checked' : '' }}>
              <span class="toggle-slider"></span>
            </label>
          </div>
        </div>
      </div>
    @else
      <div class="db-card" style="border-color:#e3f2fd">
        <div class="db-card-body" style="font-size:13px;color:#1565c0">
          ℹ️ This template is mandatory and cannot be disabled.
        </div>
      </div>
    @endif

    {{-- Custom subject --}}
    @if($emailTemplate->landlord_can_edit)
      <div class="db-card">
        <div class="db-card-header"><span class="db-card-title">Customise subject</span></div>
        <div class="db-card-body">
          <div class="form-group" style="margin-bottom:0">
            <label class="form-label">Subject line <span>leave blank to use platform default</span></label>
            <input type="text" name="subject_override" class="form-input"
                   value="{{ old('subject_override', $pref->subject_override ?? '') }}"
                   placeholder="{{ $emailTemplate->subject }}">
          </div>
        </div>
      </div>

      {{-- Custom body --}}
      <div class="db-card">
        <div class="db-card-header">
          <span class="db-card-title">Customise body</span>
          <span class="db-card-sub" style="font-size:11px">Leave blank to use platform default</span>
        </div>
        <div class="db-card-body">
          <div style="display:flex;flex-wrap:wrap;gap:4px;margin-bottom:10px">
            @foreach(['tenant_first_name','tenant_name','landlord_first_name','property_name','rent_amount','currency_code','due_date','days_until_due','days_overdue','late_fee_amount','platform_name'] as $v)
              <span class="var-chip" onclick="insertVar('{{ $v }}')">{{ '{{' . $v . '}}' }}</span>
            @endforeach
          </div>
          <textarea name="body_html_override" id="body_html" class="form-input form-textarea"
                    placeholder="Leave empty to use the platform default below">{{ old('body_html_override', $pref->body_html_override ?? '') }}</textarea>
          <div class="form-hint">Supports basic HTML: &lt;p&gt;, &lt;strong&gt;, &lt;a&gt;, &lt;br&gt;</div>
        </div>
      </div>
    @endif

    <div style="display:flex;gap:10px">
      <button type="submit" class="db-btn db-btn-primary" style="flex:1">Save preferences</button>
      @if($pref->exists && ($pref->subject_override || $pref->body_html_override))
        <form method="POST" action="{{ route('landlord.communication.reset', $emailTemplate) }}" style="display:inline">
          @csrf @method('DELETE')
          <button type="submit" class="db-btn db-btn-ghost" onclick="return confirm('Reset to platform default?')">
            Reset to default
          </button>
        </form>
      @endif
    </div>

  </div>

  {{-- Right: platform default preview --}}
  <div style="display:flex;flex-direction:column;gap:16px">
    <div class="db-card">
      <div class="db-card-header"><span class="db-card-title">Platform default</span></div>
      <div class="db-card-body">
        <div style="font-size:12px;color:var(--text-light);margin-bottom:4px">Subject</div>
        <div style="font-size:13px;font-weight:600;color:var(--text-dark);margin-bottom:14px">
          {{ $emailTemplate->subject }}
        </div>
        <div style="font-size:12px;color:var(--text-light);margin-bottom:6px">Body</div>
        <div class="default-preview">
          {!! $emailTemplate->body_html !!}
        </div>
      </div>
    </div>

    <div class="db-card">
      <div class="db-card-header"><span class="db-card-title">Trigger</span></div>
      <div class="db-card-body" style="font-size:13px;color:var(--text-mid)">
        <strong style="color:var(--text-dark)">{{ $emailTemplate->triggerLabel() }}</strong>
        <div style="margin-top:8px;font-size:12px;color:var(--text-light)">
          This email fires automatically — no manual action needed.
        </div>
      </div>
    </div>
  </div>

</div>
</form>

@push('scripts')
<script>
function insertVar(name) {
  const ta = document.getElementById('body_html');
  const pos = ta.selectionStart;
  ta.value = ta.value.slice(0,pos) + '{{' + name + '}}' + ta.value.slice(pos);
  ta.focus();
}
</script>
@endpush

@endsection
