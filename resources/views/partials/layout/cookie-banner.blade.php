<div
  id="rmCookieBanner"
  class="cookie-banner is-hidden"
  role="dialog"
  aria-live="polite"
  aria-label="Cookie consent"
  data-cookies-url="{{ url('/cookies') }}"
>
  <div class="cookie-banner__inner">
    <p class="cookie-banner__text">
      We use essential cookies to run {{ config('site.name') }} and optional analytics cookies to improve the site.
      <a href="{{ url('/cookies') }}" class="cookie-banner__link">Cookie policy</a>
    </p>
    <div class="cookie-banner__actions">
      <button type="button" class="cookie-banner__btn cookie-banner__btn--ghost" data-cookie-manage>
        Manage
      </button>
      <button type="button" class="cookie-banner__btn cookie-banner__btn--primary" data-cookie-accept>
        Accept
      </button>
    </div>
  </div>
</div>
