@php
  $businessReady = $user->businessEntityReadyForLease();
@endphp

@if($errors->any() && old('_section') === 'business')
  <div class="db-alert db-alert-error" style="margin-bottom:16px">{{ $errors->first() }}</div>
@endif

<div class="db-card">
  <div class="db-card-header"><span class="db-card-title">Business entity</span></div>
  <div class="db-card-body">
    <form method="POST" action="{{ route('landlord.account.business') }}" class="db-form db-form--wide">
      @csrf
      <input type="hidden" name="_section" value="business">

      <p class="rm-acc-field-group-label" style="margin-top:0;padding-top:0;border-top:none">Entity</p>
      <div class="db-form-group">
        <label>Legal name <span class="req">*</span></label>
        <input type="text" name="business_legal_name" class="db-input" maxlength="255" required
          value="{{ old('business_legal_name', $user->business_legal_name) }}"
          placeholder="e.g. Maple Street Holdings LLC">
      </div>
      <div class="db-form-row">
        <div class="db-form-group">
          <label>Entity type</label>
          <select name="business_entity_type" class="db-select">
            <option value="">—</option>
            @foreach(['LLC','Corporation','S-Corp','Partnership','Trust','Other'] as $t)
              <option value="{{ $t }}" @selected(old('business_entity_type', $user->business_entity_type) === $t)>{{ $t }}</option>
            @endforeach
          </select>
        </div>
        <div class="db-form-group">
          <label>EIN / tax ID</label>
          <input type="text" name="business_ein" class="db-input" maxlength="32"
            value="{{ old('business_ein', $user->business_ein) }}" placeholder="XX-XXXXXXX">
        </div>
      </div>
      <div class="db-form-group">
        <label>State / country of formation</label>
        <input type="text" name="business_state_of_formation" class="db-input" maxlength="120"
          value="{{ old('business_state_of_formation', $user->business_state_of_formation) }}" placeholder="e.g. Delaware, US">
      </div>

      <p class="rm-acc-field-group-label">Registered address</p>
      <div class="db-form-group">
        <label>Address line 1 <span class="req">*</span></label>
        <input type="text" name="business_address_line1" class="db-input" maxlength="255" required
          value="{{ old('business_address_line1', $user->business_address_line1) }}">
      </div>
      <div class="db-form-group">
        <label>Address line 2</label>
        <input type="text" name="business_address_line2" class="db-input" maxlength="255"
          value="{{ old('business_address_line2', $user->business_address_line2) }}">
      </div>
      <div class="db-form-row">
        <div class="db-form-group">
          <label>City <span class="req">*</span></label>
          <input type="text" name="business_city" class="db-input" maxlength="120" required
            value="{{ old('business_city', $user->business_city) }}">
        </div>
        <div class="db-form-group">
          <label>Region / state</label>
          <input type="text" name="business_region" class="db-input" maxlength="120"
            value="{{ old('business_region', $user->business_region) }}">
        </div>
      </div>
      <div class="db-form-row">
        <div class="db-form-group">
          <label>Postal code</label>
          <input type="text" name="business_postal_code" class="db-input" maxlength="32"
            value="{{ old('business_postal_code', $user->business_postal_code) }}">
        </div>
        <div class="db-form-group">
          <label>Country <span class="req">*</span></label>
          <select name="business_address_country" class="db-select" required>
            <option value="">Select</option>
            @foreach(config('countries') as $code => $c)
              <option value="{{ $code }}" @selected(old('business_address_country', $user->business_address_country ?: $user->home_country) == $code)>{{ $code }}</option>
            @endforeach
          </select>
        </div>
      </div>

      <p class="rm-acc-field-group-label">Leases</p>
      <label class="rm-acc-check-row" @if(! $businessReady) style="opacity:0.55" @endif>
        <input type="hidden" name="use_business_entity_in_lease" value="0">
        <input type="checkbox" name="use_business_entity_in_lease" value="1"
          @checked(old('use_business_entity_in_lease', $user->use_business_entity_in_lease))
          @disabled(! $businessReady)>
        <span class="rm-acc-check-text">
          <span class="rm-acc-check-title">Use business entity in lease</span>
          @if($businessReady)
            <span class="rm-acc-check-meta">Default for new leases — {{ $user->business_legal_name }}</span>
          @else
            <span class="rm-acc-check-meta">Save legal name and address first</span>
          @endif
        </span>
      </label>

      <button type="submit" class="db-form-submit" style="margin-top:16px">Save</button>
    </form>
  </div>
</div>

@if($businessReady)
<div class="db-card" style="margin-top:16px">
  <div class="db-card-header"><span class="db-card-title">On file</span></div>
  <div class="db-card-body">
    <div class="rm-detail-rows">
      <div class="rm-detail-row">
        <span class="rm-detail-label">Lease party</span>
        <span class="rm-detail-value"><strong>{{ $user->leasePartyName() }}</strong></span>
      </div>
      <div class="rm-detail-row">
        <span class="rm-detail-label">Address</span>
        <span class="rm-detail-value">{{ $user->leasePartyAddress() }}</span>
      </div>
      @if($user->business_ein)
      <div class="rm-detail-row">
        <span class="rm-detail-label">EIN / tax ID</span>
        <span class="rm-detail-value">{{ $user->business_ein }}</span>
      </div>
      @endif
    </div>
  </div>
</div>
@endif
