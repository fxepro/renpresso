@php
  $idFlow = $user->kycWorkflowStatus();
  $displayName = $user->kyc_legal_name ?: $user->fullName();
@endphp

@if($idFlow === 'pending')
  <div class="db-alert" style="margin-bottom:16px;background:var(--gold-pale);color:var(--gold);border:1px solid rgba(201,150,58,0.25)">
    Your government ID is <strong>under review</strong>. You cannot change ID details until review completes.
  </div>
@elseif($idFlow === 'verified')
  <div class="db-alert db-alert-success" style="margin-bottom:16px">Your ID on file is <strong>verified</strong>.</div>
@elseif($idFlow === 'rejected' && $user->kyc_rejection_reason)
  <div class="db-alert db-alert-error" style="margin-bottom:16px"><strong>ID not approved:</strong> {{ $user->kyc_rejection_reason }}</div>
@endif

<div class="db-grid-2" style="margin-bottom:18px">
  <div class="db-card">
    <div class="db-card-header"><span class="db-card-title">Contact</span></div>
    <div class="db-card-body">
      <div class="rm-detail-rows" style="margin-bottom:20px">
        <div class="rm-detail-row">
          <span class="rm-detail-label">Name</span>
          <span class="rm-detail-value"><strong>{{ $displayName }}</strong></span>
        </div>
        <div class="rm-detail-row">
          <span class="rm-detail-label">On lease</span>
          <span class="rm-detail-value">{{ $user->fullName() }}</span>
        </div>
        @if($lease)
        <div class="rm-detail-row">
          <span class="rm-detail-label">Property</span>
          <span class="rm-detail-value">{{ $lease->property->name ?? '—' }}@if($lease->unit_label) · {{ $lease->displayUnit() }}@endif</span>
        </div>
        @endif
        <div class="rm-detail-row">
          <span class="rm-detail-label">Login email</span>
          <span class="rm-detail-value">{{ $user->email }}</span>
        </div>
      </div>
      <p class="db-form-hint" style="margin-bottom:16px">Name is taken from your lease and government ID. Ask your landlord to update the name on your lease if it is wrong.</p>

      @if($user->tenantProfileEditable())
      <form method="POST" action="{{ route('tenant.account.profile') }}" class="db-form" style="max-width:420px">
        @csrf
        <input type="hidden" name="section" value="contact">
        @if($errors->any() && old('section') === 'contact')
          <div class="db-alert db-alert-error">{{ $errors->first() }}</div>
        @endif
        <div class="db-form-group">
          <label>Phone</label>
          <input type="text" name="phone" class="db-input" maxlength="30" value="{{ old('phone', $user->phone) }}">
        </div>
        <button type="submit" class="db-form-submit">Save phone</button>
      </form>
      @else
        <div class="rm-detail-rows">
          <div class="rm-detail-row">
            <span class="rm-detail-label">Phone</span>
            <span class="rm-detail-value">{{ $user->phone ?? '—' }}</span>
          </div>
        </div>
      @endif
    </div>
  </div>

  <div class="db-card">
    <div class="db-card-header"><span class="db-card-title">Government ID</span></div>
    <div class="db-card-body">
      <div class="rm-detail-rows" style="margin-bottom:20px">
        <div class="rm-detail-row">
          <span class="rm-detail-label">Legal name</span>
          <span class="rm-detail-value"><strong>{{ $user->kyc_legal_name ?: $user->fullName() }}</strong></span>
        </div>
        <div class="rm-detail-row">
          <span class="rm-detail-label">Status</span>
          <span class="rm-detail-value"><span class="badge badge-{{ $idFlow === 'verified' ? 'green' : ($idFlow === 'pending' ? 'gold' : 'navy') }}">{{ ucfirst($idFlow) }}</span></span>
        </div>
        @if($user->kyc_id_document_path)
        <div class="rm-detail-row">
          <span class="rm-detail-label">Stored file</span>
          <span class="rm-detail-value">
            <a href="{{ route('tenant.account.id-document') }}" class="db-btn db-btn-ghost" style="font-size:13px;padding:6px 12px;text-decoration:none" target="_blank" rel="noopener">View uploaded ID</a>
            @if($user->kyc_submitted_at)
              <span class="db-form-hint" style="display:block;margin-top:6px">Uploaded {{ $user->kyc_submitted_at->format('d M Y') }}</span>
            @endif
          </span>
        </div>
        @else
        <div class="rm-detail-row">
          <span class="rm-detail-label">Stored file</span>
          <span class="rm-detail-value" style="color:var(--text-light)">None — upload below</span>
        </div>
        @endif
      </div>

      @if($user->tenantProfileEditable())
      <form method="POST" action="{{ route('tenant.account.profile') }}" class="db-form" enctype="multipart/form-data" style="max-width:none">
        @csrf
        <input type="hidden" name="section" value="identity">
        @if($errors->any() && old('section', 'identity') === 'identity')
          <div class="db-alert db-alert-error">{{ $errors->first() }}</div>
        @endif

        <p style="font-weight:600;margin-bottom:12px">Address on ID</p>
        <div class="db-form-group">
          <label>Date of birth <span class="req">*</span></label>
          <input type="date" name="kyc_date_of_birth" class="db-input" required value="{{ old('kyc_date_of_birth', $user->kyc_date_of_birth?->format('Y-m-d')) }}">
        </div>
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
                <option value="{{ $code }}" {{ old('kyc_address_country', $user->kyc_address_country) == $code ? 'selected' : '' }}>{{ $code }}</option>
              @endforeach
            </select>
          </div>
        </div>

        <div class="db-form-group" style="margin-top:8px">
          <label>{{ $user->kyc_id_document_path ? 'Replace stored ID' : 'Upload government ID' }} <span class="req">*</span></label>
          <input type="file" name="kyc_id_document" class="db-input" accept=".jpg,.jpeg,.png,.webp,.pdf" {{ $user->kyc_id_document_path ? '' : 'required' }}>
          <span class="db-form-hint">Encrypted storage on this account. Max 10 MB.@if($user->kyc_id_document_path) Leave empty to keep the file above.@endif</span>
        </div>

        <button type="submit" class="db-form-submit">Save ID details</button>
      </form>
      @elseif(! $user->kyc_id_document_path)
      <div class="db-empty" style="padding:20px"><p>ID locked while under review.</p></div>
      @else
      <div class="rm-detail-rows">
        <div class="rm-detail-row">
          <span class="rm-detail-label">Date of birth</span>
          <span class="rm-detail-value">{{ $user->kyc_date_of_birth?->format('d M Y') ?? '—' }}</span>
        </div>
        <div class="rm-detail-row">
          <span class="rm-detail-label">Address on ID</span>
          <span class="rm-detail-value">{{ $user->formattedIdAddress() }}</span>
        </div>
      </div>
      @endif
    </div>
  </div>
</div>

<div class="db-card">
  <div class="db-card-header"><span class="db-card-title">Security</span></div>
  <div class="db-card-body">
    <div class="db-grid-2">
      <div>
        <p style="font-weight:600;margin-bottom:8px">Password</p>
        <p style="font-size:var(--fs-step);color:var(--text-mid);margin-bottom:12px">Change your sign-in password by email reset.</p>
        <p style="margin-bottom:12px"><span class="badge badge-green">Set</span></p>
        <a href="{{ route('password.request') }}" class="db-btn db-btn-primary" style="text-decoration:none">Change password</a>
      </div>
      <div>
        <p style="font-weight:600;margin-bottom:8px">2-step verification</p>
        <p style="font-size:var(--fs-step);color:var(--text-mid);margin-bottom:12px">Require a code from your phone when signing in.</p>
        <p style="margin-bottom:12px"><span class="badge badge-grey">Off</span></p>
        <button type="button" class="db-btn db-btn-ghost" disabled title="Coming soon">Enable 2FA (coming soon)</button>
      </div>
    </div>
  </div>
</div>
