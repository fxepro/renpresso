@php
  $activation = $activation ?? app(\App\Services\LandlordAccountActivation::class);
  $isActive = $user->isLandlordAccountActive();
  $feePerUnit = $activation->signupFeeMinorPerUnit($user);
  $currency = $activation->billingCurrency($user);
  $feeDisplay = number_format($feePerUnit / 100, 2);
  $defaultPayment = $activation->defaultPaymentMethod($user);
  $hasDefaultPayment = $defaultPayment !== null;
  $paymentTabLink = ['tab' => 'payment'];
  if ($defaultPayment) {
    $paymentTabLink['pm'] = $defaultPayment->method_type;
  }
@endphp

@if($isActive)
  @php
    $activatedAt = $user->portfolio_activation_paid_at
      ? $user->portfolio_activation_paid_at->timezone(config('app.timezone'))->format('M j, Y')
      : '—';
  @endphp
  <div class="db-card" style="margin-bottom:16px">
    <div class="db-card-header">
      <span class="db-card-title">Account status</span>
      <span class="badge badge-green">Active</span>
    </div>
    <div class="db-card-body">
      <div class="rm-detail-rows">
        <div class="rm-detail-row">
          <span class="rm-detail-label">Activated</span>
          <span class="rm-detail-value">{{ $activatedAt }}</span>
        </div>
        <div class="rm-detail-row">
          <span class="rm-detail-label">Licensed units</span>
          <span class="rm-detail-value"><strong>{{ (int) $user->portfolio_activation_units }}</strong></span>
        </div>
        @if($user->portfolio_activation_fee_minor)
        <div class="rm-detail-row">
          <span class="rm-detail-label">Fee paid</span>
          <span class="rm-detail-value">{{ $activation->formatFee((int) $user->portfolio_activation_fee_minor, $currency) }}</span>
        </div>
        @endif
        <div class="rm-detail-row">
          <span class="rm-detail-label">Properties</span>
          <span class="rm-detail-value">{{ $propertyCount }}</span>
        </div>
      </div>
    </div>
  </div>

  <div class="db-card">
    <div class="db-card-header"><span class="db-card-title">Defaults for new properties</span></div>
    <div class="db-card-body">
      <form method="POST" action="{{ route('landlord.account.portfolio-defaults') }}" class="db-form db-form--wide">
        @csrf
        @if($errors->has('default_multi_unit_capacity'))
          <div class="db-alert db-alert-error" style="margin-bottom:14px">{{ $errors->first('default_multi_unit_capacity') }}</div>
        @endif
        <div class="db-form-group">
          <label>Default licensed units (multi-unit)</label>
          <input type="number" name="default_multi_unit_capacity" class="db-input" min="1" max="999"
            value="{{ old('default_multi_unit_capacity', $user->default_multi_unit_capacity) }}">
        </div>
        <button type="submit" class="db-btn db-btn-primary">Save</button>
      </form>
    </div>
  </div>
@else
  @php
    $unitsDefault = old('portfolio_units', $user->portfolio_committed_units ?? 1);
    $totalMinor = $activation->activationFeeMinor($user, (int) $unitsDefault);
  @endphp

  <div class="db-card">
    <div class="db-card-header">
      <span class="db-card-title">Activate account</span>
      <span class="badge badge-gold">Pending activation</span>
    </div>
    <div class="db-card-body">
      <p class="rm-activate-intro">Complete both steps below. Then you can add properties and leases.</p>

      <ol class="rm-activate-steps">
        <li class="rm-activate-step {{ $hasDefaultPayment ? 'rm-activate-step--done' : 'rm-activate-step--current' }}">
          <div class="rm-activate-step-head">
            <span class="rm-activate-step-num">1</span>
            <span class="rm-activate-step-title">Default payment method</span>
            @if($hasDefaultPayment)
              <span class="badge badge-green" style="margin-left:auto">Done</span>
            @endif
          </div>
          <div class="rm-activate-step-body">
            @if($hasDefaultPayment)
              <p class="rm-activate-step-meta">{{ ucfirst($defaultPayment->method_type) }} — {{ $defaultPayment->displaySummary() }}</p>
            @else
              <p class="rm-activate-step-meta">On Payment, save any method and check <strong>Set as default for platform billing</strong> (one default for the whole account).</p>
              <a href="{{ route('landlord.account', $paymentTabLink) }}" class="db-btn db-btn-primary" style="margin-top:10px;text-decoration:none;display:inline-block">Open Payment</a>
            @endif
          </div>
        </li>

        <li class="rm-activate-step {{ $hasDefaultPayment ? 'rm-activate-step--current' : 'rm-activate-step--locked' }}">
          <div class="rm-activate-step-head">
            <span class="rm-activate-step-num">2</span>
            <span class="rm-activate-step-title">Portfolio fee</span>
          </div>
          <div class="rm-activate-step-body">
            <form method="POST" action="{{ route('landlord.account.portfolio-activate') }}" class="db-form db-form--wide">
              @csrf
              <div class="db-form-group">
                <label>Licensed units <span class="req">*</span></label>
                <input type="number" name="portfolio_units" id="portfolioUnitsInput" class="db-input" min="1" max="9999" required
                  value="{{ $unitsDefault }}" @disabled(! $hasDefaultPayment)>
                <span class="db-form-hint">1 = one single-unit building. Multi-unit = total licensed slots (e.g. 12).</span>
              </div>
              <div class="rm-activate-pricing">
                <div class="rm-activate-pricing-row">
                  <span class="rm-detail-label">Per unit</span>
                  <span>{{ $currency }} {{ $feeDisplay }}</span>
                </div>
                <div class="rm-activate-pricing-row rm-activate-pricing-row--total">
                  <span class="rm-detail-label">Total</span>
                  <span id="portfolioActivationTotal"><strong>{{ $currency }} {{ number_format($totalMinor / 100, 2) }}</strong></span>
                </div>
              </div>
              <button type="submit" class="db-form-submit" @disabled(! $hasDefaultPayment)>Pay activation fee</button>
            </form>
          </div>
        </li>
      </ol>
    </div>
  </div>

  @push('scripts')
  <script>
  (function () {
    var input = document.getElementById('portfolioUnitsInput');
    var total = document.getElementById('portfolioActivationTotal');
    var feePerUnit = {{ $feePerUnit }};
    var currency = @json($currency);
    if (!input || !total) return;
    function sync() {
      var units = Math.max(1, parseInt(input.value, 10) || 1);
      var amt = currency + ' ' + (units * feePerUnit / 100).toFixed(2);
      total.innerHTML = '<strong>' + amt + '</strong>';
    }
    input.addEventListener('input', sync);
  })();
  </script>
  @endpush
@endif
