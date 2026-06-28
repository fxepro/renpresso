<header class="site-header">
  <nav class="rm-nav" id="rmNav" aria-label="Primary">
    <a href="{{ url('/') }}" class="rm-nav-logo">
      {{ config('site.logo.prefix') }}<span>{{ config('site.logo.accent') }}</span>
    </a>

    <ul class="rm-nav-links">
      @foreach (config('navigation.primary') as $item)
        <li>
          <a href="{{ url($item['href']) }}"
             @class(['active' => ($page ?? '') === ($item['page'] ?? '')])>
            {{ $item['label'] }}
          </a>
        </li>
      @endforeach
    </ul>

    <div class="rm-nav-cta">
      @foreach (config('navigation.cta') as $cta)
        <a href="{{ url($cta['href']) }}" class="rm-btn {{ $cta['class'] ?? 'rm-btn-ghost' }}">{{ $cta['label'] }}</a>
      @endforeach
    </div>

    <button class="rm-hamburger" id="rmBurger" type="button" aria-label="Menu" aria-expanded="false" aria-controls="rmDrawer">
      <span></span><span></span><span></span>
    </button>
  </nav>

  <div class="rm-drawer" id="rmDrawer" aria-label="Mobile navigation">
    @foreach (config('navigation.primary') as $item)
      <a href="{{ url($item['href']) }}"
         @class(['active' => ($page ?? '') === ($item['page'] ?? '')])>
        {{ $item['label'] }}
      </a>
    @endforeach
    <div class="rm-drawer-cta">
      @foreach (config('navigation.cta') as $cta)
        <a href="{{ url($cta['href']) }}" class="rm-btn {{ $cta['class'] ?? 'rm-btn-ghost' }}">{{ $cta['label'] }}</a>
      @endforeach
    </div>
  </div>
</header>
