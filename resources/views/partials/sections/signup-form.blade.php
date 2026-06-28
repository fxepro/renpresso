@php
  $success = $signupSuccess ?? false;
@endphp

<div class="waitlist-page">
  @include('partials.sections.onboarding-info')

  <div class="wl-right">
    <a href="{{ route('login') }}" class="wl-back-link">← Back to sign in</a>

    @if ($success)
      <div class="wl-success wl-success--visible">
        <div class="success-icon">✓</div>
        <h2>Account created.</h2>
        <p>Your landlord account is ready. Sign in with the email and password you just set.</p>
        <div class="success-links">
          <a href="{{ route('login') }}" class="success-link primary">Sign in →</a>
          <a href="{{ url('/how-it-works') }}" class="success-link">See how it works</a>
        </div>
      </div>
    @else
      <div id="signupFormWrap">
        <div class="wl-form-header">
          <h2>Create your account</h2>
          <p>Tell us about your portfolio and set a password. Not ready for an account yet? <a href="{{ route('waitlist') }}">Join the waitlist</a> instead.</p>
        </div>

        @if ($errors->any())
          <div class="wl-form-errors" role="alert">
            <strong>Please fix the following:</strong>
            <ul>
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        <form class="wl-form" id="signupForm" method="POST" action="{{ route('auth.register') }}">
          @csrf
          @include('partials.sections.application-fields')

          <div class="form-divider"></div>
          <p class="wl-form-note">Set a password for your landlord account (first month free in launch markets).</p>
          <div class="form-row">
            <div class="form-group">
              <label for="ob_password">Password <span class="req">*</span></label>
              <input type="password" class="form-input" id="ob_password" name="password" autocomplete="new-password" minlength="8" required>
              @error('password') <span class="form-error form-error--block">{{ $message }}</span> @enderror
            </div>
            <div class="form-group">
              <label for="ob_password_confirmation">Confirm password <span class="req">*</span></label>
              <input type="password" class="form-input" id="ob_password_confirmation" name="password_confirmation" autocomplete="new-password" required>
            </div>
          </div>

          <button type="submit" class="form-submit">Create account →</button>

          <p class="form-legal">
            Already have an account? <a href="{{ route('login') }}">Sign in</a>.
            Just exploring? <a href="{{ route('waitlist') }}">Join the waitlist</a>.
          </p>
          <p class="form-legal">By continuing you agree to our <a href="{{ url('/privacy') }}">Privacy Policy</a> and <a href="{{ url('/terms') }}">Terms of Service</a>.</p>
        </form>
      </div>
    @endif
  </div>
</div>

@include('partials.sections.onboarding-social-proof')
