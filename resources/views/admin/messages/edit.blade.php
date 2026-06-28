@extends('admin.layout')
@section('title', 'Edit — ' . $emailTemplate->name)
@section('page-title', 'Edit template')
@section('breadcrumb', 'Operations')

@push('styles')
<style>
.form-group { margin-bottom:18px; }
.form-label { display:block;font-size:13px;font-weight:600;color:var(--text-dark);margin-bottom:5px; }
.form-label span { font-weight:400;color:var(--text-light);margin-left:4px; }
.form-input { width:100%;padding:9px 12px;border:1px solid var(--cream-dark);border-radius:8px;font-size:14px;color:var(--text-dark);background:var(--white);box-sizing:border-box; }
.form-input:focus { outline:none;border-color:var(--terra); }
.form-select { appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%23888' stroke-width='1.5' fill='none'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 12px center; }
.form-textarea { min-height:260px;font-family:monospace;font-size:13px;resize:vertical; }
.form-hint { font-size:11px;color:var(--text-light);margin-top:4px; }
.form-row { display:grid;grid-template-columns:1fr 1fr;gap:16px; }
.form-check { display:flex;align-items:center;gap:8px;margin-bottom:8px; }
.form-check input { width:16px;height:16px;accent-color:var(--terra); }
.var-chip { font-size:11px;background:var(--cream);padding:2px 8px;border-radius:4px;cursor:pointer;color:var(--text-dark);border:1px solid var(--cream-dark); }
.var-chip:hover { background:var(--terra);color:#fff;border-color:var(--terra); }
</style>
@endpush

@section('content')
<div style="margin-bottom:16px;display:flex;gap:10px;align-items:center">
  <a href="{{ route('admin.messages.show', $emailTemplate) }}" class="db-btn db-btn-ghost" style="text-decoration:none">← Back</a>
  <span style="font-size:13px;color:var(--text-light)">{{ $emailTemplate->slug }}</span>
  <span style="margin-left:auto;font-size:13px;color:var(--text-light)">
    Status: <strong style="color:{{ $emailTemplate->is_published ? '#2e7d32' : '#888' }}">
      {{ $emailTemplate->is_published ? 'Published' : 'Draft' }}
    </strong>
  </span>
</div>

<form method="POST" action="{{ route('admin.messages.update', $emailTemplate) }}">
@csrf @method('PUT')
<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;align-items:start">

  <div>
    <div class="db-card">
      <div class="db-card-header"><span class="db-card-title">Template content</span></div>
      <div class="db-card-body">

        <div class="form-group">
          <label class="form-label">Template name</label>
          <input type="text" name="name" class="form-input" value="{{ old('name', $emailTemplate->name) }}" required>
        </div>

        <div class="form-group">
          <label class="form-label">Slug <span>(read-only)</span></label>
          <input type="text" class="form-input" value="{{ $emailTemplate->slug }}" disabled style="background:var(--cream)">
        </div>

        <div class="form-group">
          <label class="form-label">Email subject</label>
          <input type="text" name="subject" class="form-input" value="{{ old('subject', $emailTemplate->subject) }}" required>
        </div>

        <div class="form-group">
          <label class="form-label">Body HTML</label>
          <div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:8px">
            @foreach(['tenant_first_name','tenant_name','landlord_first_name','property_name','rent_amount','currency_code','due_date','days_until_due','days_overdue','late_fee_amount','lease_end_date','platform_name'] as $v)
              <span class="var-chip" onclick="insertVar('{{ $v }}')">{{ '{{' . $v . '}}' }}</span>
            @endforeach
          </div>
          <textarea name="body_html" id="body_html" class="form-input form-textarea">{{ old('body_html', $emailTemplate->body_html) }}</textarea>
        </div>

      </div>
    </div>
  </div>

  <div style="display:flex;flex-direction:column;gap:16px">
    <div class="db-card">
      <div class="db-card-header"><span class="db-card-title">Trigger</span></div>
      <div class="db-card-body">

        <div class="form-group">
          <label class="form-label">Trigger event</label>
          <select name="trigger_event" class="form-input form-select" onchange="toggleDays(this.value)" required>
            @foreach($triggerEvents as $val => $label)
              <option value="{{ $val }}" {{ old('trigger_event', $emailTemplate->trigger_event) === $val ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
          </select>
        </div>

        <div id="days-row">
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Days</label>
              <input type="number" name="trigger_days" class="form-input" value="{{ old('trigger_days', $emailTemplate->trigger_days) }}" min="0" max="365">
            </div>
            <div class="form-group">
              <label class="form-label">Direction</label>
              <select name="trigger_direction" class="form-input form-select">
                <option value="">— n/a —</option>
                @foreach(['before','after','on'] as $dir)
                  <option value="{{ $dir }}" {{ old('trigger_direction', $emailTemplate->trigger_direction) === $dir ? 'selected' : '' }}>{{ ucfirst($dir) }}</option>
                @endforeach
              </select>
            </div>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Sort order</label>
          <input type="number" name="sort_order" class="form-input" value="{{ old('sort_order', $emailTemplate->sort_order) }}" min="0" max="255">
        </div>

      </div>
    </div>

    <div class="db-card">
      <div class="db-card-header"><span class="db-card-title">Landlord permissions</span></div>
      <div class="db-card-body">
        <div class="form-check">
          <input type="checkbox" name="landlord_can_edit" id="lce" value="1" {{ old('landlord_can_edit', $emailTemplate->landlord_can_edit) ? 'checked' : '' }}>
          <label for="lce" style="font-size:13px">Landlords can customise</label>
        </div>
        <div class="form-check">
          <input type="checkbox" name="landlord_can_disable" id="lcd" value="1" {{ old('landlord_can_disable', $emailTemplate->landlord_can_disable) ? 'checked' : '' }}>
          <label for="lcd" style="font-size:13px">Landlords can disable</label>
        </div>
      </div>
    </div>

    <div style="display:flex;gap:10px">
      <button type="submit" class="db-btn db-btn-primary" style="flex:1">Save changes</button>
    </div>

    {{-- Danger zone --}}
    <div class="db-card" style="border-color:#fce4ec">
      <div class="db-card-header" style="border-color:#fce4ec"><span class="db-card-title" style="color:#b71c1c">Danger zone</span></div>
      <div class="db-card-body">
        <form method="POST" action="{{ route('admin.messages.destroy', $emailTemplate) }}"
              onsubmit="return confirm('Delete this template permanently?')">
          @csrf @method('DELETE')
          <button type="submit" class="db-btn" style="background:#b71c1c;color:#fff;border-color:#b71c1c;width:100%">
            Delete template
          </button>
        </form>
      </div>
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
  ta.value = ta.value.slice(0,pos) + '{{' + name + '}}' + ta.value.slice(pos);
  ta.focus();
}
</script>
@endpush

@endsection
