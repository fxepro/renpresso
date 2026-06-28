@php
  $waitlist = config('ctas.waitlist');
  $logo = config('site.logo');
  $hideBanner = in_array($page ?? '', ['waitlist', 'login'], true);
@endphp

@if (! $hideBanner)
<section class="rm-cta-banner" aria-label="Join waitlist">
  <div class="container-sm">
    <h2>Ready to manage your<br><em>{{ $waitlist['headline_em'] }}</em> portfolio?</h2>
    <p>{{ $waitlist['subline'] }}</p>
    <form class="rm-waitlist-form" method="POST" action="{{ route('waitlist.store') }}">
      @csrf
      <input type="email" name="email" class="rm-waitlist-input" placeholder="{{ $waitlist['placeholder'] }}" required autocomplete="email">
      <button type="submit" class="rm-waitlist-btn">{{ $waitlist['button'] }}</button>
    </form>
    <p class="rm-waitlist-note">{{ $waitlist['note'] }}</p>
  </div>
</section>
@endif

<footer class="rm-footer">
  <div class="rm-footer-inner">
    <div class="rm-footer-top">
      <div class="rm-footer-brand">
        <a href="{{ url('/') }}" class="rm-footer-logo">{{ $logo['prefix'] }}<span>{{ $logo['accent'] }}</span></a>
        <p>{{ config('site.description') }}</p>
        <div class="rm-footer-socials">
          @foreach (config('site.social') as $social)
            <a href="{{ $social['href'] }}" class="rm-social" aria-label="{{ $social['label'] }}">{{ $social['icon'] }}</a>
          @endforeach
        </div>
      </div>

      @foreach (config('footer.columns') as $column)
        <div class="rm-footer-col">
          <h5>{{ $column['title'] }}</h5>
          <ul class="rm-footer-links">
            @foreach ($column['links'] as $link)
              <li>
                <a href="{{ url($link['href']) }}">
                  {{ $link['label'] }}
                  @if (!empty($link['badge']))
                    <span class="rm-badge">{{ $link['badge'] }}</span>
                  @endif
                </a>
              </li>
            @endforeach
          </ul>
        </div>
      @endforeach
    </div>

    <div class="rm-footer-compliance">
      @foreach (config('footer.compliance') as $item)
        <span class="rm-compliance-item">🔒 {{ $item }}</span>
      @endforeach
    </div>

    <div class="rm-footer-bottom">
      <p class="rm-footer-copy">&copy; {{ config('site.name') }} {{ date('Y') }} &mdash; All rights reserved.</p>
      <select class="rm-region-select" aria-label="Select region">
        @foreach (config('site.regions') as $region)
          <option>{{ $region }}</option>
        @endforeach
      </select>
      <nav class="rm-footer-legal" aria-label="Legal">
        @foreach (config('footer.legal') as $i => $link)
          @if ($i > 0)<span class="rm-footer-legal-sep">&middot;</span>@endif
          <a href="{{ url($link['href']) }}"
             @class(['active' => ($page ?? '') === ($link['page'] ?? '')])>
            {{ $link['label'] }}
          </a>
        @endforeach
      </nav>
    </div>
  </div>
</footer>
