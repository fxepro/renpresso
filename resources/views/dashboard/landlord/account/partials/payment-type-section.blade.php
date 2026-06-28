{{-- $type, $title, $methods, $editingMethod, $user --}}
@php
  $isEditing = $editingMethod && $editingMethod->method_type === $type;
  $list = $methods->where('method_type', $type);
  $pmQuery = ['tab' => 'payment', 'pm' => $type];
  $headers = \App\Models\LandlordPaymentMethod::tableHeadersForType($type);
@endphp
<section class="rm-pm-section" id="pm-{{ $type }}">

  @if($list->isNotEmpty())
  <div class="db-card" style="margin-bottom:14px">
    <div class="db-table-wrap">
      <table class="db-table">
        <thead>
          <tr>
            @foreach($headers as $h)
              <th>{{ $h }}</th>
            @endforeach
            <th>Status</th>
            <th>Default</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @foreach($list as $pm)
          <tr>
            @foreach($pm->tableCells() as $cell)
              <td>{{ $cell }}</td>
            @endforeach
            <td><span class="badge badge-{{ $pm->status === 'active' ? 'green' : 'gold' }}">{{ ucfirst($pm->status) }}</span></td>
            <td>{{ $pm->is_default ? 'Yes' : '—' }}</td>
            <td style="white-space:nowrap">
              <a href="{{ route('landlord.account', array_merge($pmQuery, ['edit' => $pm->id])) }}" class="db-table-link">Edit</a>
              @if(! $pm->is_default)
                ·
                <form method="POST" action="{{ route('landlord.account.payment-methods.default', $pm) }}" style="display:inline">
                  @csrf @method('PATCH')
                  <button type="submit" class="db-table-link" style="background:none;border:none;padding:0;cursor:pointer;font:inherit">Default</button>
                </form>
              @endif
              ·
              <form method="POST" action="{{ route('landlord.account.payment-methods.destroy', $pm) }}" style="display:inline" onsubmit="return confirm('Remove this method?');">
                @csrf @method('DELETE')
                <button type="submit" class="db-table-link" style="background:none;border:none;padding:0;cursor:pointer;color:var(--red);font:inherit">Delete</button>
              </form>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
  @endif

  <div class="db-card">
    <div class="db-card-header">
      <span class="db-card-title">{{ $isEditing ? 'Edit' : 'Add' }} {{ strtolower($title) }}</span>
      @if($isEditing)
        <a href="{{ route('landlord.account', $pmQuery) }}" class="db-btn db-btn-ghost" style="font-size:12px;padding:5px 10px;text-decoration:none">Cancel</a>
      @endif
    </div>
    <div class="db-card-body">
      <form method="POST"
        action="{{ $isEditing ? route('landlord.account.payment-methods.update', $editingMethod) : route('landlord.account.payment-methods.store') }}"
        class="db-form db-form--wide">
        @csrf
        @if($isEditing) @method('PUT') @endif
        <input type="hidden" name="method_type" value="{{ $type }}">

        @if($type === 'card')
          @php
            $cardMeta = $isEditing ? ($editingMethod->meta ?? []) : [];
            $expMonth = old('card_exp_month', $cardMeta['card_exp_month'] ?? '');
            $expYear = old('card_exp_year', isset($cardMeta['card_exp_year']) ? (strlen((string)$cardMeta['card_exp_year']) === 4 ? substr($cardMeta['card_exp_year'], -2) : $cardMeta['card_exp_year']) : '');
          @endphp
          <div class="db-form-group">
            <label>Card number <span class="req">*</span></label>
            <input type="text" name="card_number" class="db-input" inputmode="numeric" autocomplete="cc-number"
              maxlength="23" placeholder="4242 4242 4242 4242" {{ $isEditing ? '' : 'required' }}>
            @if($isEditing && $editingMethod->last4)
              <span class="db-form-hint">On file: {{ $editingMethod->maskedCardNumber() }} — leave blank to keep</span>
            @endif
          </div>
          <div class="db-form-row">
            <div class="db-form-group">
              <label>Expiry month <span class="req">*</span></label>
              <select name="card_exp_month" class="db-select" required>
                <option value="">MM</option>
                @for($m = 1; $m <= 12; $m++)
                  @php $mm = str_pad((string)$m, 2, '0', STR_PAD_LEFT); @endphp
                  <option value="{{ $mm }}" {{ (string)$expMonth === $mm || (int)$expMonth === $m ? 'selected' : '' }}>{{ $mm }}</option>
                @endfor
              </select>
            </div>
            <div class="db-form-group">
              <label>Expiry year <span class="req">*</span></label>
              <select name="card_exp_year" class="db-select" required>
                <option value="">YY</option>
                @for($y = (int) date('Y'); $y <= (int) date('Y') + 15; $y++)
                  @php $yy = substr((string)$y, -2); @endphp
                  <option value="{{ $yy }}" {{ (string)$expYear === $yy || (string)$expYear === (string)$y ? 'selected' : '' }}>{{ $yy }}</option>
                @endfor
              </select>
            </div>
            <div class="db-form-group">
              <label>Security code (CVC) <span class="req">*</span></label>
              <input type="password" name="card_cvc" class="db-input" inputmode="numeric" autocomplete="cc-csc"
                maxlength="4" pattern="[0-9]{3,4}" placeholder="{{ $isEditing && ($cardMeta['cvc_on_file'] ?? false) ? '••• on file' : '123' }}"
                {{ $isEditing ? '' : 'required' }}>
              @if($isEditing)
                <span class="db-form-hint">Re-enter to update; leave blank to keep CVC on file.</span>
              @endif
            </div>
          </div>
          <div class="db-form-row">
            <div class="db-form-group">
              <label>Card brand</label>
              <select name="brand" class="db-select">
                <option value="">—</option>
                @foreach(['Visa','Mastercard','Amex','Discover'] as $b)
                  <option value="{{ $b }}" {{ old('brand', $isEditing ? $editingMethod->brand : '') === $b ? 'selected' : '' }}>{{ $b }}</option>
                @endforeach
              </select>
            </div>
            <div class="db-form-group">
              <label>Label</label>
              <input type="text" name="label" class="db-input" maxlength="120" value="{{ old('label', $isEditing ? $editingMethod->label : '') }}" placeholder="Platform subscription">
            </div>
          </div>
          @include('partials.account-card-billing-fields', [
            'user' => $user,
            'isEditing' => $isEditing,
            'editingMethod' => $editingMethod,
          ])
        @elseif($type === 'ach')
          @php $achMeta = $isEditing ? ($editingMethod->meta ?? []) : []; @endphp
          <div class="db-form-group">
            <label>Bank name <span class="req">*</span></label>
            <input type="text" name="ach_bank_name" class="db-input" maxlength="120" required
              value="{{ old('ach_bank_name', $achMeta['ach_bank_name'] ?? '') }}">
          </div>
          <div class="db-form-group">
            <label>Account holder <span class="req">*</span></label>
            <input type="text" name="label" class="db-input" maxlength="120" required
              value="{{ old('label', $isEditing ? $editingMethod->label : '') }}" placeholder="Name on the account">
          </div>
          <div class="db-form-row">
            <div class="db-form-group">
              <label>Routing / sort code / IFSC <span class="req">*</span></label>
              <input type="text" name="ach_routing" class="db-input" maxlength="32"
                placeholder="US: 9 digits · UK: sort code · IN: IFSC" {{ $isEditing ? '' : 'required' }}>
              @if($isEditing && !empty($achMeta['ach_routing_last4']))
                <span class="db-form-hint">On file: {{ $editingMethod->maskedAchRouting() }} — leave blank to keep</span>
              @endif
            </div>
            <div class="db-form-group">
              <label>Account number <span class="req">*</span></label>
              <input type="text" name="ach_account_number" class="db-input" inputmode="numeric" maxlength="17"
                placeholder="Full account number" {{ $isEditing ? '' : 'required' }}>
              @if($isEditing && $editingMethod->last4)
                <span class="db-form-hint">On file: {{ $editingMethod->maskedAchAccount() }} — leave blank to keep</span>
              @endif
            </div>
          </div>
          <div class="db-form-group">
            <label>Account type <span class="req">*</span></label>
            <select name="ach_account_type" class="db-select" required>
              <option value="">Select</option>
              @foreach(['checking' => 'Checking', 'savings' => 'Savings'] as $val => $lbl)
                <option value="{{ $val }}" {{ old('ach_account_type', $achMeta['ach_account_type'] ?? '') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
              @endforeach
            </select>
          </div>
        @elseif($type === 'paypal')
          <div class="db-form-group">
            <label>PayPal email <span class="req">*</span></label>
            <input type="email" name="paypal_email" class="db-input" maxlength="255" required
              value="{{ old('paypal_email', $isEditing ? ($editingMethod->meta['paypal_email'] ?? '') : '') }}">
          </div>
          <div class="db-form-group">
            <label>Label</label>
            <input type="text" name="label" class="db-input" maxlength="120" value="{{ old('label', $isEditing ? $editingMethod->label : '') }}" placeholder="Backup PayPal">
          </div>
        @elseif($type === 'crypto')
          <div class="db-form-row">
            <div class="db-form-group">
              <label>Asset <span class="req">*</span></label>
              <input type="text" name="crypto_asset" class="db-input" maxlength="32" required placeholder="BTC, ETH, USDC"
                value="{{ old('crypto_asset', $isEditing ? ($editingMethod->meta['crypto_asset'] ?? '') : '') }}">
            </div>
            <div class="db-form-group">
              <label>Wallet address @if(! $isEditing)<span class="req">*</span>@endif</label>
              <input type="text" name="crypto_wallet" class="db-input" maxlength="120" {{ $isEditing ? '' : 'required' }}
                value="{{ old('crypto_wallet', '') }}"
                placeholder="{{ $isEditing ? 'Leave blank to keep current wallet' : 'Full wallet address' }}">
              @if($isEditing && !empty($editingMethod->meta['crypto_wallet']))
                <span class="db-form-hint">On file: {{ $editingMethod->meta['crypto_wallet'] }}</span>
              @endif
            </div>
          </div>
          <div class="db-form-group">
            <label>Label</label>
            <input type="text" name="label" class="db-input" maxlength="120" value="{{ old('label', $isEditing ? $editingMethod->label : '') }}">
          </div>
        @else
          <div class="db-form-group">
            <label>Description <span class="req">*</span></label>
            <input type="text" name="label" class="db-input" maxlength="120" required
              value="{{ old('label', $isEditing ? $editingMethod->label : '') }}">
          </div>
        @endif

        <div class="db-form-group" style="margin-top:8px">
          <label style="display:flex;align-items:center;gap:8px;font-weight:400">
            <input type="checkbox" name="is_default" value="1" {{ old('is_default', $isEditing ? $editingMethod->is_default : false) ? 'checked' : '' }}>
            Set as default for platform billing
          </label>
        </div>

        <button type="submit" class="db-form-submit">{{ $isEditing ? 'Update' : 'Save' }} {{ strtolower($title) }}</button>
      </form>
    </div>
  </div>
</section>
