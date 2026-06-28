{{-- Billing on card — $user, $isEditing, optional $editingMethod --}}
@php
  $cardMeta = $isEditing ? ($editingMethod->meta ?? []) : [];
  $sameAsId = (bool) old('billing_same_as_id', $cardMeta['billing_same_as_id'] ?? false);
  $idPreview = $user->formattedIdAddress();
  $hasIdAddress = $idPreview !== '—';
@endphp
<div class="rm-card-billing">
  <p class="rm-acc-field-group-label">Billing address</p>

  <label class="rm-acc-check-row">
    <input type="hidden" name="billing_same_as_id" value="0">
    <input type="checkbox" name="billing_same_as_id" value="1" class="card-billing-same-id" @checked($sameAsId)>
    <span class="rm-acc-check-text">
      <span class="rm-acc-check-title">Same as ID address</span>
      @if($hasIdAddress)
        <span class="rm-acc-check-meta">{{ $idPreview }}</span>
      @endif
    </span>
  </label>

  <div class="card-billing-custom-fields rm-card-billing-fields" @if($sameAsId) hidden @endif>
    <div class="db-form-group">
      <label>Address line 1 <span class="req">*</span></label>
      <input type="text" name="billing_line1" class="db-input" maxlength="255"
        value="{{ old('billing_line1', $cardMeta['billing_line1'] ?? '') }}">
    </div>
    <div class="db-form-group">
      <label>Address line 2</label>
      <input type="text" name="billing_line2" class="db-input" maxlength="255"
        value="{{ old('billing_line2', $cardMeta['billing_line2'] ?? '') }}">
    </div>
    <div class="db-form-row">
      <div class="db-form-group">
        <label>City <span class="req">*</span></label>
        <input type="text" name="billing_city" class="db-input" maxlength="120"
          value="{{ old('billing_city', $cardMeta['billing_city'] ?? '') }}">
      </div>
      <div class="db-form-group">
        <label>Region / state</label>
        <input type="text" name="billing_region" class="db-input" maxlength="120"
          value="{{ old('billing_region', $cardMeta['billing_region'] ?? '') }}">
      </div>
    </div>
    <div class="db-form-row">
      <div class="db-form-group">
        <label>Postal code</label>
        <input type="text" name="billing_postal_code" class="db-input" maxlength="32"
          value="{{ old('billing_postal_code', $cardMeta['billing_postal_code'] ?? '') }}">
      </div>
      <div class="db-form-group">
        <label>Country <span class="req">*</span></label>
        <select name="billing_country" class="db-select">
          <option value="">Select</option>
          @foreach(config('countries') as $code => $c)
            <option value="{{ $code }}" @selected(old('billing_country', $cardMeta['billing_country'] ?? '') == $code)>{{ $code }}</option>
          @endforeach
        </select>
      </div>
    </div>
  </div>
</div>

@once
@push('scripts')
<script>
document.querySelectorAll('.card-billing-same-id').forEach(function (cb) {
  function sync() {
    var block = cb.closest('.rm-card-billing')?.querySelector('.card-billing-custom-fields');
    if (!block) return;
    if (cb.checked) {
      block.setAttribute('hidden', '');
    } else {
      block.removeAttribute('hidden');
    }
    block.querySelectorAll('input, select').forEach(function (el) {
      if (cb.checked) {
        el.removeAttribute('required');
      } else if (['billing_line1', 'billing_city', 'billing_country'].includes(el.name)) {
        el.setAttribute('required', 'required');
      }
    });
  }
  cb.addEventListener('change', sync);
  sync();
});
</script>
@endpush
@endonce
