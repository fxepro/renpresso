<div class="db-card">
  <div class="db-card-header"><span class="db-card-title">Add bank account</span></div>
  <div class="db-card-body">
    <form method="POST" action="{{ route('landlord.account.payout-accounts.store') }}" class="db-form db-form--wide">
      @csrf
      <input type="hidden" name="_section" value="payouts">
      @if($errors->any() && old('_section') === 'payouts')
        <div class="db-alert db-alert-error">{{ $errors->first() }}</div>
      @endif
      <div class="db-form-row">
        <div class="db-form-group">
          <label>Account purpose <span class="req">*</span></label>
          <select name="payout_purpose" class="db-select" required>
            <option value="collection" {{ old('payout_purpose', 'collection') === 'collection' ? 'selected' : '' }}>Local collection (property country)</option>
            <option value="repatriation" {{ old('payout_purpose') === 'repatriation' ? 'selected' : '' }}>Home repatriation ({{ strtoupper($user->home_country ?? 'US') }})</option>
          </select>
          <span class="db-form-hint">Collection = where rent lands locally. Repatriation = your home bank when you move funds yourself.</span>
        </div>
        <div class="db-form-group">
          <label>Country (account location) <span class="req">*</span></label>
          <select name="payout_country" class="db-select" required>
            <option value="">Select</option>
            @foreach(config('countries') as $code => $c)
              <option value="{{ $code }}" {{ old('payout_country') == $code ? 'selected' : '' }}>{{ $code }} — {{ $c['currency'] ?? '' }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="db-form-group">
        <label>Label <span class="req">*</span></label>
        <input type="text" name="payout_label" class="db-input" required maxlength="120" value="{{ old('payout_label') }}" placeholder="e.g. France rental income">
      </div>
      <div class="db-form-row">
        <div class="db-form-group">
          <label>Account holder name <span class="req">*</span></label>
          <input type="text" name="payout_holder_name" class="db-input" required maxlength="255" value="{{ old('payout_holder_name', $user->fullName()) }}">
        </div>
        <div class="db-form-group">
          <label>Bank name</label>
          <input type="text" name="payout_bank_name" class="db-input" maxlength="255" value="{{ old('payout_bank_name') }}">
        </div>
      </div>
      <div class="db-form-group">
        <label>IBAN (if applicable)</label>
        <input type="text" name="payout_iban" class="db-input" maxlength="64" value="{{ old('payout_iban') }}" placeholder="EU / SEPA and many other regions">
      </div>
      <div class="db-form-row">
        <div class="db-form-group">
          <label>Local account number</label>
          <input type="text" name="payout_local_account" class="db-input" maxlength="64" value="{{ old('payout_local_account') }}" placeholder="If not using IBAN">
        </div>
        <div class="db-form-group">
          <label>Routing / sort / IFSC / BSB</label>
          <input type="text" name="payout_local_routing" class="db-input" maxlength="64" value="{{ old('payout_local_routing') }}" placeholder="When required in your country">
        </div>
      </div>
      <span class="db-form-hint">Provide <strong>either</strong> an IBAN <strong>or</strong> a local account number (with routing when needed).</span>
      <div style="display:flex;flex-wrap:wrap;gap:10px;margin-top:16px">
        <button type="submit" class="db-btn db-btn-primary">Save account</button>
        <a href="{{ route('landlord.account', ['tab' => 'banks', 'sec' => 'accounts']) }}" class="db-btn db-btn-ghost" style="text-decoration:none">Cancel</a>
      </div>
    </form>
  </div>
</div>
