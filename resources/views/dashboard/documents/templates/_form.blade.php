@php
  $tpl = $leaseTemplate ?? null;
@endphp
<div class="db-form-group">
  <label>Template name <span class="req">*</span></label>
  <input type="text" name="name" class="db-input" value="{{ old('name', $tpl?->name) }}" required maxlength="255" placeholder="e.g. Standard master lease — Indonesia">
</div>
<div class="db-form-group">
  <label>Lease type <span class="req">*</span></label>
  <select name="lease_type" class="db-select" required>
    @foreach(\App\Models\LeaseTemplate::LEASE_TYPES as $key => $label)
      <option value="{{ $key }}" {{ old('lease_type', $tpl?->lease_type ?? 'master') === $key ? 'selected' : '' }}>{{ $label }}</option>
    @endforeach
  </select>
</div>
<div class="db-form-group">
  <label>Short description</label>
  <input type="text" name="description" class="db-input" value="{{ old('description', $tpl?->description) }}" maxlength="2000" placeholder="When to use this template">
</div>
<div class="db-form-group">
  <label>Clause text / notes</label>
  <textarea name="body" class="db-input" rows="10" placeholder="Optional standard terms, merge fields, or instructions for your team">{{ old('body', $tpl?->body) }}</textarea>
  <span class="db-form-hint">Plain text for now; applying to new leases at create time is planned next.</span>
</div>
<div class="db-form-group">
  <label>Template file (PDF, DOC, DOCX, TXT)</label>
  <input type="file" name="file" class="db-input" accept=".pdf,.doc,.docx,.txt">
  @if($tpl?->hasFile())
    <div style="margin-top:10px;font-size:13px;color:var(--text-mid)">
      Current: <a href="{{ route('lease-templates.file', $tpl) }}" class="db-table-link">{{ $tpl->original_filename }}</a>
      @if($tpl->formattedSize()) ({{ $tpl->formattedSize() }}) @endif
    </div>
    <label style="display:flex;align-items:center;gap:8px;margin-top:10px;font-size:13px;cursor:pointer">
      <input type="checkbox" name="remove_file" value="1">
      Remove attached file
    </label>
  @endif
  <span class="db-form-hint">Stored in your configured disk (local/S3). Max 20 MB.</span>
</div>
