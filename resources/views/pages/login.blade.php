@extends('layouts.auth', ['page' => 'login'])

@section('title', 'Sign in')
@section('meta_description', 'Sign in to your Renpresso account to manage your properties, collect rent, and view your dashboard.')

@section('content')

<!-- ── LEFT ── -->
<div class="login-left">
  <div class="login-left-grid"></div>
  <div class="login-left-glow"></div>

  <a href="{{ url('/') }}" class="login-logo">{{ config('site.logo.prefix') }}<span>{{ config('site.logo.accent') }}</span></a>

  <div class="login-left-content">
    <h2>Your portfolio.<br><em>All of it.</em></h2>
    <p>Every property, every tenant — in one dashboard. Welcome back.</p>

    <div class="login-preview">
      <div class="lp-header">
        <span class="lp-title">Portfolio overview</span>
        <span class="lp-period">May 2025</span>
      </div>
      <div class="lp-total">$6,900</div>
      <div class="lp-sub">Collected this month · 3 properties</div>
      <div class="lp-row">
        <div class="lp-row-left">
          <span class="lp-flag">🇺🇸</span>
          <div><div class="lp-name">Oak Street, Austin TX</div><div class="lp-method">ACH AutoPay</div></div>
        </div>
        <div class="u-text-right login-field-actions">
          <div class="lp-amount">$ 2,400</div>
          <span class="lp-status s-paid">Paid</span>
        </div>
      </div>
      <div class="lp-row">
        <div class="lp-row-left">
          <span class="lp-flag">🇺🇸</span>
          <div><div class="lp-name">Pine Ave, Denver CO</div><div class="lp-method">ACH AutoPay</div></div>
        </div>
        <div class="u-text-right login-field-actions">
          <div class="lp-amount">$ 1,850</div>
          <span class="lp-status s-paid">Paid</span>
        </div>
      </div>
      <div class="lp-row">
        <div class="lp-row-left">
          <span class="lp-flag">🇺🇸</span>
          <div><div class="lp-name">Maple Dr, Phoenix AZ</div><div class="lp-method">Card</div></div>
        </div>
        <div class="u-text-right login-field-actions">
          <div class="lp-amount">$ 1,650</div>
          <span class="lp-status s-due">Due 1 Jun</span>
        </div>
      </div>
    </div>
  </div>

  <div class="login-left-footer">
    <a href="{{ url('/privacy') }}">Privacy</a> · <a href="{{ url('/terms') }}">Terms</a> · <a href="{{ url('/cookies') }}">Cookies</a>
  </div>
</div>

<!-- ── RIGHT ── -->
<div class="login-right">
  <div class="login-card">

    <div id="signinView">
      @if (session('status'))
        <p class="login-flash login-flash--success">{{ session('status') }}</p>
      @endif
      @auth
        <p class="login-flash">
          Signed in as <strong>{{ auth()->user()->email }}</strong>.
          <a href="{{ route('dashboard') }}">Go to dashboard</a>
          ·
          <form method="POST" action="{{ route('auth.logout') }}" class="login-inline-logout">
            @csrf
            <button type="submit">Sign out</button>
          </form>
        </p>
      @endauth
      <div class="login-card-header">
        <h1>Welcome back.</h1>
        <p>Don't have an account? <a href="{{ route('register') }}">Sign up →</a></p>
      </div>

      <div class="sso-buttons">
        <button class="sso-btn" type="button" onclick="event.preventDefault(); document.getElementById('sso-note').classList.remove('is-hidden')">
          <span class="sso-icon">🔵</span> Continue with Google
        </button>
        <button class="sso-btn" type="button" onclick="event.preventDefault(); document.getElementById('sso-note').classList.remove('is-hidden')">
          <span class="sso-icon">🍎</span> Continue with Apple
        </button>
      </div>
      <p id="sso-note" class="sso-note is-hidden">SSO available at launch. Use email for now.</p>

      <div class="divider">
        <div class="divider-line"></div>
        <span class="divider-text">or sign in with email</span>
        <div class="divider-line"></div>
      </div>

      <form class="login-form" id="loginForm" method="POST" action="{{ route('auth.login') }}">
        @csrf
        <div class="form-group">
          <label for="email">Email address</label>
          <input type="email" class="form-input" id="email" name="email" placeholder="you@example.com" autocomplete="email" value="{{ old('email') }}" required>
          @error('email') <span class="form-error form-error--block">{{ $message }}</span> @enderror
        </div>

        <div class="form-group">
          <label for="password">
            Password
            <a href="#" onclick="showForgot(event)">Forgot password?</a>
          </label>
          <div class="password-wrap">
            <input type="password" class="form-input" id="password" name="password" placeholder="Your password" autocomplete="current-password" required>
            <button type="button" class="password-toggle" onclick="togglePassword()" aria-label="Show password" id="pwToggle">👁</button>
          </div>
        </div>

        <button type="submit" class="form-submit" id="loginBtn">Sign in →</button>
      </form>

      <div class="login-bottom">
        <p><a href="{{ route('register') }}">Create an account</a> · <a href="{{ route('waitlist') }}">Join the waitlist</a></p>
        <p>Maintenance staff? <a href="{{ route('register.maintenance') }}">Create a maintenance account</a></p>
        <div class="login-legal">
          <a href="{{ url('/privacy') }}">Privacy policy</a>
          <a href="{{ url('/terms') }}">Terms of service</a>
          <a href="{{ url('/cookies') }}">Cookies</a>
        </div>
      </div>
    </div>

    <div id="forgotView" class="is-hidden">
      <button class="back-btn" type="button" onclick="showSignin()">← Back to sign in</button>
      <div class="login-card-header">
        <h1>Reset password.</h1>
        <p>Enter your email and we'll send a reset link within a few minutes.</p>
      </div>
      <form class="login-form" onsubmit="handleReset(event)" novalidate>
        <div class="form-group">
          <label for="resetEmail">Email address</label>
          <input type="email" class="form-input" id="resetEmail" placeholder="you@example.com" required>
        </div>
        <button type="submit" class="form-submit" id="resetBtn">Send reset link →</button>
      </form>
    </div>

    <div id="resetSentView" class="reset-sent-view is-hidden">
      <div class="reset-sent-icon">✉️</div>
      <h2 class="reset-sent-title">Check your inbox.</h2>
      <p class="reset-sent-body">We've sent a password reset link to <strong id="resetEmailSent"></strong>. It expires in 30 minutes.</p>
      <button class="back-btn back-btn--center" type="button" onclick="showSignin()">← Back to sign in</button>
    </div>

  </div>
</div>

@endsection

@push('scripts')
<script>
function togglePassword() {
  const input  = document.getElementById('password');
  const toggle = document.getElementById('pwToggle');
  if (input.type === 'password') { input.type = 'text'; toggle.textContent = '🙈'; }
  else { input.type = 'password'; toggle.textContent = '👁'; }
}
function hideAuthViews() {
  ['signinView', 'forgotView', 'resetSentView'].forEach(function (id) {
    var el = document.getElementById(id);
    if (el) el.classList.add('is-hidden');
  });
}
function showForgot(e) {
  e.preventDefault();
  hideAuthViews();
  document.getElementById('forgotView').classList.remove('is-hidden');
}
function showSignin() {
  hideAuthViews();
  document.getElementById('signinView').classList.remove('is-hidden');
}
</script>
@endpush
