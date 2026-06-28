@php
  $profileSecs = [
    'overview' => ['label' => 'Account details'],
    'security' => ['label' => 'Password & 2FA'],
  ];
  $legalOwnerName = $user->maintenanceLegalOwnerName($team);
  $companyName = $team?->name;
  $ownerHasData = $user->maintenanceHasDirectorLegalNameOnFile($team) && (bool) $user->kyc_address_line1;
  $kycHasData = (bool) $user->kyc_id_document_path;
  $contactHasData = (bool) ($user->first_name && $user->email);
  $idBadge = match ($kycFlow) {
    'verified' => 'green',
    'pending'  => 'gold',
    'rejected' => 'red',
    default    => 'grey',
  };
  $contactEditing = request('edit') === 'contact';
  $ownerEditing = request('edit') === 'owner' || request('edit') === 'kyc';
  $ownerComplete = $ownerHasData && $kycHasData;
  $ownerFormOpen = $kycEditable && ($ownerEditing || ! $ownerComplete);
@endphp

@include('dashboard.maintenance-portal.account.partials.sub-nav', [
  'tab' => 'profile',
  'activeKey' => $profileSec,
  'items' => $profileSecs,
  'ariaLabel' => 'Profile sections',
])

@if($profileSec === 'overview')
  @if($kycFlow === 'rejected' && $user->kyc_rejection_reason)
    <div class="db-alert db-alert-error" style="margin-bottom:16px">{{ $user->kyc_rejection_reason }}</div>
  @endif

  <div class="db-grid-2" style="margin-bottom:18px">
    @component('dashboard.maintenance-portal.account.partials.section-shell', [
      'title' => 'Contact',
      'accountTab' => 'profile',
      'accountSec' => 'overview',
      'editTarget' => 'contact',
      'editing' => $contactEditing,
      'hasData' => true,
      'editLabel' => 'Edit',
    ])
      @slot('view')
        <table class="rm-acc-table">
          <tbody>
            <tr><th>Name</th><td><strong>{{ $legalOwnerName ?: '—' }}</strong></td></tr>
            <tr><th>Account name</th><td>{{ $companyName ?: '—' }}</td></tr>
            <tr><th>Sign-in name</th><td>{{ $user->fullName() }}</td></tr>
            <tr><th>Login email</th><td>{{ $user->email }}</td></tr>
            <tr><th>Phone</th><td>{{ $user->phone ?? '—' }}</td></tr>
          </tbody>
        </table>
      @endslot
      @slot('edit')
        <form method="POST" action="{{ route('maint.account.profile') }}" class="db-form">
          @csrf @method('PUT')
          <input type="hidden" name="redirect_tab" value="profile">
          <input type="hidden" name="redirect_sec" value="overview">
          <div class="db-form-row">
            <div class="db-form-group"><label>First name</label><input type="text" name="first_name" class="db-input" value="{{ old('first_name', $user->first_name) }}" required></div>
            <div class="db-form-group"><label>Last name</label><input type="text" name="last_name" class="db-input" value="{{ old('last_name', $user->last_name) }}" required></div>
          </div>
          <div class="db-form-group"><label>Phone</label><input type="text" name="phone" class="db-input" value="{{ old('phone', $user->phone) }}"></div>
          <div class="db-form-group"><label>Email</label><input type="email" class="db-input" value="{{ $user->email }}" disabled></div>
          <button type="submit" class="db-form-submit">Save contact</button>
        </form>
      @endslot
    @endcomponent

    @component('dashboard.maintenance-portal.account.partials.section-shell', [
      'title' => 'Owner & government ID',
      'accountTab' => 'profile',
      'accountSec' => 'overview',
      'editTarget' => 'owner',
      'editing' => $ownerFormOpen,
      'showEdit' => $kycEditable,
      'hasData' => true,
      'editLabel' => 'Edit',
    ])
      @slot('view')
        <table class="rm-acc-table">
          <tbody>
            <tr><th>Legal name</th><td><strong>{{ $user->maintenanceHasDirectorLegalNameOnFile($team) ? $user->kyc_legal_name : '—' }}</strong></td></tr>
            <tr><th>Status</th><td><span class="badge badge-{{ $idBadge }}">{{ ucfirst($kycFlow) }}</span></td></tr>
            <tr><th>Stored file</th><td>
              @if($user->kyc_id_document_path)
                <a href="{{ route('maint.account.director-id') }}" class="db-btn db-btn-ghost" style="font-size:13px;padding:6px 12px;text-decoration:none;display:inline-block" target="_blank" rel="noopener">View uploaded ID</a>
                @if($user->kyc_submitted_at)<span class="db-form-hint" style="display:block;margin-top:4px">{{ $user->kyc_submitted_at->format('d M Y') }}</span>@endif
              @else
                —
              @endif
            </td></tr>
            <tr><th>Date of birth</th><td>{{ $user->kyc_date_of_birth?->format('d M Y') ?? '—' }}</td></tr>
            <tr><th>Address on ID</th><td>{{ $user->formattedIdAddress() ?: '—' }}</td></tr>
          </tbody>
        </table>
      @endslot
      @slot('edit')
        <form method="POST" action="{{ route('maint.account.director-identity') }}" class="db-form db-form--wide" enctype="multipart/form-data">
          @csrf
          <input type="hidden" name="redirect_tab" value="profile">
          <input type="hidden" name="redirect_sec" value="owner">
          @if($errors->any())
            <div class="db-alert db-alert-error" style="margin-bottom:12px">{{ $errors->first() }}</div>
          @endif
          <div class="db-form-group">
            <label>Legal name (as on ID) <span class="req">*</span></label>
            <input type="text" name="kyc_legal_name" class="db-input" required maxlength="255" value="{{ old('kyc_legal_name', $user->maintenanceLegalOwnerNameForForm($team)) }}">
          </div>
          <div class="db-form-group">
            <label>Date of birth <span class="req">*</span></label>
            <input type="date" name="kyc_date_of_birth" class="db-input" required value="{{ old('kyc_date_of_birth', $user->kyc_date_of_birth?->format('Y-m-d')) }}">
          </div>
          <p style="font-weight:600;margin:12px 0 8px">Address on ID</p>
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
            <label>{{ $kycHasData ? 'Replace government ID' : 'Upload government ID' }} @if(! $kycHasData)<span class="req">*</span>@endif</label>
            <input type="file" name="kyc_id_document" class="db-input" accept=".jpg,.jpeg,.png,.webp,.pdf" {{ $kycHasData ? '' : 'required' }}>
            <span class="db-form-hint">Max 10 MB. JPEG, PNG, WebP, or PDF.@if($kycHasData) Leave empty to keep the current file.@endif</span>
          </div>
          <button type="submit" class="db-form-submit">{{ $kycHasData ? 'Save changes' : 'Save & submit for review' }}</button>
        </form>
      @endslot
    @endcomponent
  </div>

@elseif($profileSec === 'security')
  @php $securityEditing = request('edit') === 'security'; @endphp
  @component('dashboard.maintenance-portal.account.partials.section-shell', [
    'title' => 'Password',
    'accountTab' => 'profile',
    'accountSec' => 'security',
    'editTarget' => 'security',
    'editing' => $securityEditing,
    'hasData' => true,
  ])
    @slot('view')
      <table class="rm-acc-table">
        <tbody>
          <tr><th>Password</th><td><span class="badge badge-green">Set</span></td></tr>
        </tbody>
      </table>
      <p class="db-form-hint" style="margin-top:12px">Use Edit to change your sign-in password.</p>
    @endslot
    @slot('edit')
      <form method="POST" action="{{ route('maint.account.password') }}" class="db-form">
        @csrf @method('PUT')
        <input type="hidden" name="redirect_tab" value="profile">
        <input type="hidden" name="redirect_sec" value="security">
        <div class="db-form-group"><label>Current password</label><input type="password" name="current_password" class="db-input" required autocomplete="current-password"></div>
        <div class="db-form-group"><label>New password</label><input type="password" name="password" class="db-input" required autocomplete="new-password"></div>
        <div class="db-form-group"><label>Confirm new password</label><input type="password" name="password_confirmation" class="db-input" required autocomplete="new-password"></div>
        <button type="submit" class="db-form-submit">Change password</button>
      </form>
    @endslot
  @endcomponent

  @php $twoFaEditing = request('edit') === '2fa'; @endphp
  @component('dashboard.maintenance-portal.account.partials.section-shell', [
    'title' => 'Two-factor authentication (2FA)',
    'accountTab' => 'profile',
    'accountSec' => 'security',
    'editTarget' => '2fa',
    'editing' => $twoFaEditing,
    'showEdit' => false,
    'hasData' => true,
    'class' => 'rm-acc-section-spaced',
  ])
    @slot('view')
      <table class="rm-acc-table">
        <tbody>
          <tr><th>Status</th><td><span class="badge badge-grey">Off</span></td></tr>
        </tbody>
      </table>
    @endslot
    @slot('edit')
    @endslot
  @endcomponent
@endif
