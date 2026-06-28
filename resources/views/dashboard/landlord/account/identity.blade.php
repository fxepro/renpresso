@php

  $flow = $user->kycWorkflowStatus();

  $editable = $user->landlordKycEditable();

  $displayName = $user->formattedKycLegalName();

@endphp



@if($flow === 'verified')

  <div class="db-alert db-alert-success" style="margin-bottom:16px">Verified.</div>

  <div class="db-card">

    <div class="db-card-header"><span class="db-card-title">On file</span></div>

    <div class="db-card-body">

      <div class="rm-detail-rows">

        <div class="rm-detail-row">

          <span class="rm-detail-label">Name</span>

          <span class="rm-detail-value"><strong>{{ $displayName }}</strong></span>

        </div>

        <div class="rm-detail-row">

          <span class="rm-detail-label">Residential address</span>

          <span class="rm-detail-value">{{ $user->kyc_address_line1 }}@if($user->kyc_address_line2), {{ $user->kyc_address_line2 }}@endif, {{ $user->kyc_city }}@if($user->kyc_region), {{ $user->kyc_region }}@endif @if($user->kyc_postal_code){{ $user->kyc_postal_code }} @endif{{ $user->kyc_address_country }}</span>

        </div>

        @if($user->kyc_submitted_at)

        <div class="rm-detail-row">

          <span class="rm-detail-label">Submitted</span>

          <span class="rm-detail-value">{{ $user->kyc_submitted_at->timezone(config('app.timezone'))->format('M j, Y g:i A') }}</span>

        </div>

        @endif

      </div>

    </div>

  </div>

@elseif($flow === 'pending')

  <div class="db-alert" style="margin-bottom:16px;background:var(--gold-pale);color:var(--gold);border:1px solid rgba(201,150,58,0.25)">

    Under review.

  </div>

  <div class="db-card">

    <div class="db-card-header"><span class="db-card-title">Submitted details</span></div>

    <div class="db-card-body">

      <div class="rm-detail-rows">

        <div class="rm-detail-row"><span class="rm-detail-label">Name</span><span class="rm-detail-value">{{ $displayName }}</span></div>

        <div class="rm-detail-row"><span class="rm-detail-label">Date of birth</span><span class="rm-detail-value">{{ $user->kyc_date_of_birth?->format('M j, Y') }}</span></div>

        <div class="rm-detail-row"><span class="rm-detail-label">Address</span><span class="rm-detail-value">{{ $user->kyc_address_line1 }}@if($user->kyc_address_line2), {{ $user->kyc_address_line2 }}@endif, {{ $user->kyc_city }}@if($user->kyc_region), {{ $user->kyc_region }}@endif @if($user->kyc_postal_code){{ $user->kyc_postal_code }} @endif{{ $user->kyc_address_country }}</span></div>

      </div>


    </div>

  </div>

@else

  @if($flow === 'rejected' && $user->kyc_rejection_reason)

    <div class="db-alert db-alert-error" style="margin-bottom:16px"><strong>Could not approve last submission.</strong> {{ $user->kyc_rejection_reason }}</div>

  @endif

  <div class="db-card">

    <div class="db-card-header"><span class="db-card-title">Identity</span></div>

    <div class="db-card-body">

      <form method="POST" action="{{ route('landlord.account.identity') }}" class="db-form db-form--wide" enctype="multipart/form-data">

        @csrf

        <input type="hidden" name="_section" value="identity">

        @if($errors->any() && old('_section') === 'identity')

          <div class="db-alert db-alert-error">{{ $errors->first() }}</div>

        @endif

        @include('dashboard.landlord.account.partials.identity-name-fields', ['user' => $user])

        <div class="db-form-group">

          <label>Date of birth <span class="req">*</span></label>

          <input type="date" name="kyc_date_of_birth" class="db-input" required value="{{ old('kyc_date_of_birth', $user->kyc_date_of_birth?->format('Y-m-d')) }}">

        </div>

        <p class="rm-acc-field-group-label">Residential address</p>

        <div class="db-form-group">

          <label>Address line 1 <span class="req">*</span></label>

          <input type="text" name="kyc_address_line1" class="db-input" required maxlength="255" value="{{ old('kyc_address_line1', $user->kyc_address_line1) }}">

        </div>

        <div class="db-form-group">

          <label>Address line 2</label>

          <input type="text" name="kyc_address_line2" class="db-input" maxlength="255" value="{{ old('kyc_address_line2', $user->kyc_address_line2) }}">

        </div>

        <div class="db-form-row">

          <div class="db-form-group">

            <label>City <span class="req">*</span></label>

            <input type="text" name="kyc_city" class="db-input" required maxlength="120" value="{{ old('kyc_city', $user->kyc_city) }}">

          </div>

          <div class="db-form-group">

            <label>Region / state</label>

            <input type="text" name="kyc_region" class="db-input" maxlength="120" value="{{ old('kyc_region', $user->kyc_region) }}">

          </div>

        </div>

        <div class="db-form-row">

          <div class="db-form-group">

            <label>Postal code</label>

            <input type="text" name="kyc_postal_code" class="db-input" maxlength="32" value="{{ old('kyc_postal_code', $user->kyc_postal_code) }}">

          </div>

          <div class="db-form-group">

            <label>Country <span class="req">*</span></label>

            <select name="kyc_address_country" class="db-select" required>

              <option value="">Select</option>

              @foreach(config('countries') as $code => $c)

                <option value="{{ $code }}" {{ old('kyc_address_country', $user->kyc_address_country ?: $user->home_country) == $code ? 'selected' : '' }}>{{ $code }}</option>

              @endforeach

            </select>

          </div>

        </div>

        <div class="db-form-group">

          <label>Photo ID <span class="req">*</span></label>

          <input type="file" name="kyc_id_document" class="db-input" accept=".jpg,.jpeg,.png,.webp,.pdf,image/jpeg,image/png,image/webp,application/pdf" {{ $user->kyc_id_document_path ? '' : 'required' }}>

          @if($user->kyc_id_document_path)

            <span class="db-form-hint">Leave empty to keep current file.</span>

          @endif

        </div>

        <button type="submit" class="db-form-submit">Submit for review</button>

      </form>

    </div>

  </div>

@endif

