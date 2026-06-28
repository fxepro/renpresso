@extends('layouts.utility', ['page' => 'cookies'])

@section('title', 'Cookie Policy')
@section('meta_description', config('site.name') . ' cookie policy. What cookies we use, why, and how to manage your preferences.')
@section('heading', 'Cookie Policy')
@section('meta')
  Last updated: <span>1 May 2025</span>
@endsection

@section('utility')

<div class="legal-highlight">
  <p>We keep cookies simple. We use only what we need to run the platform securely and understand how it's being used. We do not use advertising cookies or sell data to advertisers.</p>
</div>

<section class="legal-section" id="what-are-cookies">
  <h2>What are cookies?</h2>
  <p>Cookies are small text files stored on your device when you visit a website or use a web application. They help the platform remember your session, preferences, and usage patterns. Some cookies are essential — without them the platform cannot function. Others are optional and can be declined.</p>
</section>

<section class="legal-section" id="cookies-we-use">
  <h2>Cookies we use</h2>
  <div class="cookie-table-wrap">
    <table class="cookie-table">
      <thead>
        <tr>
          <th>Cookie name</th>
          <th>Type</th>
          <th>Purpose</th>
          <th>Duration</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>rmx_session</td>
          <td>Essential</td>
          <td>Maintains your login session. Required for the platform to work.</td>
          <td>Session</td>
        </tr>
        <tr>
          <td>rmx_csrf</td>
          <td>Essential</td>
          <td>Prevents cross-site request forgery attacks. Required for security.</td>
          <td>Session</td>
        </tr>
        <tr>
          <td>rmx_locale</td>
          <td>Preference</td>
          <td>Remembers your preferred language and region settings.</td>
          <td>1 year</td>
        </tr>
        <tr>
          <td>rmx_consent</td>
          <td>Preference</td>
          <td>Stores your cookie consent preferences so we don't ask every time.</td>
          <td>1 year</td>
        </tr>
        <tr>
          <td>rmx_analytics</td>
          <td>Analytics</td>
          <td>Helps us understand which features are used and where users encounter issues. No personal data is shared with third parties.</td>
          <td>90 days</td>
        </tr>
      </tbody>
    </table>
  </div>
  <p>We do not use advertising cookies, tracking pixels, social media cookies, or any cookies that share your data with advertisers or data brokers.</p>
</section>

<section class="legal-section" id="your-preferences">
  <h2>Your preferences</h2>
  <p>Essential and preference cookies cannot be disabled as they are required for the platform to function. Analytics cookies are optional.</p>

  <div class="cookie-controls">
    <div class="cookie-toggle-row">
      <div class="cookie-toggle-info">
        <h4>Essential cookies</h4>
        <p>Session management and security. Required — cannot be disabled.</p>
      </div>
      <div class="cookie-toggle">
        <label><input type="checkbox" checked disabled><span class="toggle-slider"></span></label>
      </div>
    </div>
    <div class="cookie-toggle-row">
      <div class="cookie-toggle-info">
        <h4>Preference cookies</h4>
        <p>Remember your language and region settings. Disabling these means re-selecting your preferences on each visit.</p>
      </div>
      <div class="cookie-toggle">
        <label><input type="checkbox" checked id="prefCookie"><span class="toggle-slider"></span></label>
      </div>
    </div>
    <div class="cookie-toggle-row">
      <div class="cookie-toggle-info">
        <h4>Analytics cookies</h4>
        <p>Help us improve the platform by understanding feature usage. No personal data shared externally.</p>
      </div>
      <div class="cookie-toggle">
        <label><input type="checkbox" checked id="analyticsCookie"><span class="toggle-slider"></span></label>
      </div>
    </div>
  </div>

  <button class="cookie-save-btn" type="button" onclick="savePrefs()">Save preferences</button>
  <p class="cookie-saved" id="savedMsg">✓ Preferences saved</p>
</section>

<section class="legal-section" id="browser-settings">
  <h2>Managing cookies in your browser</h2>
  <p>You can also manage or delete cookies directly through your browser settings. Note that disabling cookies through your browser may affect the functionality of the {{ config('app.name') }} platform.</p>
  <ul>
    <li><strong>Chrome:</strong> Settings → Privacy and Security → Cookies and other site data</li>
    <li><strong>Firefox:</strong> Settings → Privacy &amp; Security → Cookies and Site Data</li>
    <li><strong>Safari:</strong> Preferences → Privacy → Manage Website Data</li>
    <li><strong>Edge:</strong> Settings → Cookies and site permissions</li>
  </ul>
</section>

<section class="legal-section" id="changes">
  <h2>Changes to this policy</h2>
  <p>We may update this cookie policy when we change the cookies we use. The date at the top of this page reflects the last update. For material changes, we will notify users via the platform or email.</p>
  <p>Questions about our use of cookies? <a href="{{ url('/contact') }}">Contact us</a>.</p>
</section>

@endsection

@push('scripts')
<script>
function savePrefs() {
  const saved = document.getElementById('savedMsg');
  saved.style.display = 'block';
  setTimeout(() => { saved.style.display = 'none'; }, 3000);
}
</script>
@endpush
