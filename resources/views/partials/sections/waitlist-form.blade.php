@php
  $confirmed = ($waitlistSuccess ?? false) ? 'waitlist' : null;
@endphp

<div class="waitlist-page">
  @include('partials.sections.onboarding-info')

  <div class="wl-right">
    @if ($confirmed === 'waitlist')
      <div class="wl-success wl-success--visible">
        <div class="success-icon">✉️</div>
        <h2>Check your inbox.</h2>
        <p>We saved your details and sent a confirmation to your email. We'll reach out when {{ config('app.name') }} launches in your market.</p>
        <div class="success-links">
          <a href="{{ url('/') }}" class="success-link">← Back to home</a>
          <a href="{{ route('register') }}" class="success-link">Create an account</a>
          <a href="{{ url('/how-it-works') }}" class="success-link primary">See how it works →</a>
        </div>
      </div>
    @else
      <div id="waitlistFormWrap">
        <div class="wl-form-header">
          <h2>Join the waitlist</h2>
          <p>Tell us about your portfolio. No password needed — we'll email you when we launch in your market.</p>
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

        <form class="wl-form" id="waitlistForm" method="POST" action="{{ route('waitlist.store') }}">
          @csrf
          @include('partials.sections.application-fields')

          <button type="submit" class="form-submit">Join the waitlist →</button>

          <p class="form-legal">
            Ready for a landlord account now? <a href="{{ route('register') }}">Sign up instead</a>.
            Already registered? <a href="{{ route('login') }}">Sign in</a>.
          </p>
          <p class="form-legal">By continuing you agree to our <a href="{{ url('/privacy') }}">Privacy Policy</a> and <a href="{{ url('/terms') }}">Terms of Service</a>.</p>
        </form>
      </div>
    @endif
  </div>
</div>

@include('partials.sections.onboarding-social-proof')
