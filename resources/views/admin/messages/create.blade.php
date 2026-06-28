@extends('admin.layout')
@section('title', 'New email template')
@section('page-title', 'New email template')
@section('breadcrumb', 'Operations')

@push('styles')
<style>
.form-group { margin-bottom:18px; }
.form-label { display:block;font-size:13px;font-weight:600;color:var(--text-dark);margin-bottom:5px; }
.form-label span { font-weight:400;color:var(--text-light);margin-left:4px; }
.form-input {
  width:100%;padding:9px 12px;border:1px solid var(--cream-dark);border-radius:8px;
  font-size:14px;color:var(--text-dark);background:var(--white);box-sizing:border-box;
}
.form-input:focus { outline:none;border-color:var(--terra); }
.form-select { appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%23888' stroke-width='1.5' fill='none'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 12px center; }
.form-textarea { min-height:260px;font-family:monospace;font-size:13px;resize:vertical; }
.form-hint { font-size:11px;color:var(--text-light);margin-top:4px; }
.form-row { display:grid;grid-template-columns:1fr 1fr;gap:16px; }
.form-check { display:flex;align-items:center;gap:8px;margin-bottom:8px; }
.form-check input { width:16px;height:16px;accent-color:var(--terra); }
.var-chips { display:flex;flex-wrap:wrap;gap:6px;margin-top:8px; }
.var-chip { font-size:11px;background:var(--cream);padding:2px 8px;border-radius:4px;cursor:pointer;color:var(--text-dark);border:1px solid var(--cream-dark); }
.var-chip:hover { background:var(--terra);color:#fff;border-color:var(--terra); }
</style>
@endpush

@section('content')
<div style="margin-bottom:16px">
  <a href="{{ route('admin.messages') }}" class="db-btn db-btn-ghost" style="text-decoration:none">← All templates</a>
</div>

<form method="POST" action="{{ route('admin.messages.store') }}">
@csrf
<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;align-items:start">

  {{-- Left: content --}}
  <div>
    <div class="db-card">
      <div class="db-card-header"><span class="db-card-title">Template content</span></div>
      <div class="db-card-body">

        <div class="form-group">
          <label class="form-label">Template name <span>*</span></label>
          <input type="text" name="name" class="form-input" value="{{ old('name') }}" placeholder="e.g. 7-day rent reminder" required>
        </div>

        <div class="form-group">
          <label class="form-label">Slug <span>* unique identifier</span></label>
          <input type="text" name="slug" class="form-input" value="{{ old('slug') }}" placeholder="e.g. rent_reminder_7d" required>
          <div class="form-hint">Lowercase letters, numbers, underscores only. Cannot be changed later.</div>
        </div>

        <div class="form-group">
          <label class="form-label">Email subject <span>* supports {{variables}}</span></label>
          <input type="text" name="subject" class="form-input" value="{{ old('subject') }}" placeholder="Your rent is due in {{days_until_due}} days" required>
        </div>

        <div class="form-group">
          <label class="form-label">Body HTML <span>* supports {{variables}} and basic HTML</span></label>
          <div class="var-chips">
            @foreach(['tenant_first_name','tenant_name','landlord_first_name','property_name','rent_amount','currency_code','due_date','days_until_due','days_overdue','late_fee_amount','lease_end_date','platform_name'] as $v)
              <span class="var-chip" onclick="insertVar('{{ $v }}')">{{ '{{' . $v . '}}' }}</span>
            @endforeach
          </div>
          <textarea name="body_html" id="body_html" class="form-input form-textarea" style="margin-top:8px" required>{{ old('body_html') }}</textarea>
          <div class="form-hint">Write clean HTML or plain paragraph tags. Inline styles are supported.</div>
        </div>

      </div>
    </div>
  </div>

  {{-- Right: trigger + settings --}}
  <div style="display:flex;flex-direction:column;gap:16px">
    <div class="db-card">
      <div class="db-card-header"><span class="db-card-title">Trigger</span></div>
      <div class="db-card-body">
        <div class="form-group">
          <label class="form-label">Trigger event <span>*</span></label>
          <select name="trigger_event" class="form-input form-select" onchange="toggleDays(this.value)" required>
            <option value="">— select —</option>
            @foreach($triggerEvents as $val => $label)
              <option value="{{ $val }}" {{ old('trigger_event') === $val ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
          </select>
        </div>

        <div id="days-row" style="display:none">
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Days</label>
              <input type="number" name="trigger_days" class="form-input" value="{{ old('trigger_days') }}" min="0" max="365" placeholder="e.g. 5">
            </div>
            <div class="form-group">
              <label class="form-label">Direction</label>
              <select name="trigger_direction" class="form-input form-select">
                <option value="">— n/a —</option>
                <option value="before" {{ old('trigger_direction') === 'before' ? 'selected' : '' }}>Before</option>
                <option value="after"  {{ old('trigger_direction') === 'after'  ? 'selected' : '' }}>After</option>
                <option value="on"     {{ old('trigger_direction') === 'on'     ? 'selected' : '' }}>On the day</option>
              </select>
            </div>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Sort order</label>
          <input type="number" name="sort_order" class="form-input" value="{{ old('sort_order', 100) }}" min="0" max="255">
          <div class="form-hint">Lower numbers appear first in the list.</div>
        </div>
      </div>
    </div>

    <div class="db-card">
      <div class="db-card-header"><span class="db-card-title">Landlord permissions</span></div>
      <div class="db-card-body">
        <div class="form-check">
          <input type="checkbox" name="landlord_can_edit" id="lce" value="1" {{ old('landlord_can_edit', '1') ? 'checked' : '' }}>
          <label for="lce" style="font-size:13px;color:var(--text-dark)">Landlords can customise this template</label>
        </div>
        <div class="form-check">
          <input type="checkbox" name="landlord_can_disable" id="lcd" value="1" {{ old('landlord_can_disable', '1') ? 'checked' : '' }}>
          <label for="lcd" style="font-size:13px;color:var(--text-dark)">Landlords can disable this template</label>
        </div>
        <div class="form-hint" style="margin-top:6px">If unchecked, the template is mandatory for all landlords.</div>
      </div>
    </div>

    <div style="display:flex;gap:10px">
      <button type="submit" class="db-btn db-btn-primary" style="flex:1">Save as draft</button>
      <a href="{{ route('admin.messages') }}" class="db-btn db-btn-ghost" style="text-decoration:none">Cancel</a>
    </div>
  </div>

</div>
</form>

@push('scripts')
<script>
function toggleDays(val) {
  const needs = ['rent_due_in_days','rent_overdue_days','lease_expiry_days'].includes(val);
  document.getElementById('days-row').style.display = needs ? 'block' : 'none';
}
toggleDays(document.querySelector('[name=trigger_event]').value);

function insertVar(name) {
  const ta = document.getElementById('body_html');
  const pos = ta.selectionStart;
  const val = ta.value;
  ta.value = val.slice(0, pos) + '{{' + name + '}}' + val.slice(pos);
  ta.focus();
  ta.setSelectionRange(pos + name.length + 4, pos + name.length + 4);
}
</script>
@endpush

@endsection
