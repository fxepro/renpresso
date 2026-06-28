@php
  $nameParts = $user->kycNamePartsForForm();
@endphp
<p class="rm-acc-field-group-label" style="margin-top:0;padding-top:0;border-top:none">Legal name</p>
<div class="db-form-row">
  <div class="db-form-group">
    <label>First <span class="req">*</span></label>
    <input type="text" name="kyc_first_name" class="db-input" required maxlength="120"
      value="{{ old('kyc_first_name', $nameParts['first']) }}">
  </div>
  <div class="db-form-group">
    <label>Middle</label>
    <input type="text" name="kyc_middle_name" class="db-input" maxlength="120"
      value="{{ old('kyc_middle_name', $nameParts['middle']) }}">
  </div>
</div>
<div class="db-form-row">
  <div class="db-form-group">
    <label>Last <span class="req">*</span></label>
    <input type="text" name="kyc_last_name" class="db-input" required maxlength="120"
      value="{{ old('kyc_last_name', $nameParts['last']) }}">
  </div>
  <div class="db-form-group">
    <label>Suffix</label>
    <select name="kyc_name_suffix" class="db-select">
      <option value="">—</option>
      @foreach(\App\Support\KycLegalName::SUFFIXES as $s)
        <option value="{{ $s }}" @selected(old('kyc_name_suffix', $nameParts['suffix']) === $s)>{{ $s }}</option>
      @endforeach
    </select>
  </div>
</div>
