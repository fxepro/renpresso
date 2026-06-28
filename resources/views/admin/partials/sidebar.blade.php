<nav class="db-nav" aria-label="Admin">
  @foreach(config('admin.nav', []) as $section)
  @php
    $collapsible = !empty($section['collapsible']);
    $sectionKey = 'admin-'.strtolower(preg_replace('/[^a-z0-9]+/i', '-', $section['label']));
    $hasActiveChild = false;
    foreach ($section['items'] as $navItem) {
        if (isset($navItem['page'])) {
            if (request()->routeIs('admin.page') && request()->route('page') === $navItem['page']) {
                $hasActiveChild = true;
                break;
            }
        } elseif (str_starts_with($navItem['route'] ?? '', 'admin.settings.')) {
            if (request()->routeIs($navItem['route']) || request()->routeIs($navItem['route'].'.*')) {
                $hasActiveChild = true;
                break;
            }
        } elseif (request()->routeIs($navItem['route']) || request()->routeIs(($navItem['route'] ?? '').'.*')) {
            $hasActiveChild = true;
            break;
        }
    }
    if ($collapsible && !empty($section['header_route']) && request()->routeIs($section['header_route'])) {
        $hasActiveChild = true;
    }
    $startOpen = $hasActiveChild;
  @endphp
  <div class="db-nav-section">
    @if($collapsible)
      <div class="db-nav-collapse-head">
        <button type="button" class="db-nav-collapse-toggle" data-nav-section="{{ $sectionKey }}" aria-expanded="{{ $startOpen ? 'true' : 'false' }}" aria-controls="{{ $sectionKey }}-panel" title="Expand or collapse">
          <span class="db-nav-collapse-icon" aria-hidden="true">{{ $startOpen ? '−' : '+' }}</span>
        </button>
        @if(!empty($section['header_route']))
          <a href="{{ route($section['header_route']) }}" class="db-nav-collapse-label {{ $hasActiveChild ? 'is-active' : '' }}">{{ $section['label'] }}</a>
        @else
          <span class="db-nav-collapse-label">{{ $section['label'] }}</span>
        @endif
      </div>
      <div id="{{ $sectionKey }}-panel" class="db-nav-collapse-panel {{ $startOpen ? '' : 'is-collapsed' }}" data-nav-panel="{{ $sectionKey }}">
        @foreach($section['items'] as $item)
          @include('admin.partials.nav-item', ['item' => $item])
        @endforeach
      </div>
    @else
      <span class="db-nav-label">{{ $section['label'] }}</span>
      @foreach($section['items'] as $item)
        @include('admin.partials.nav-item', ['item' => $item])
      @endforeach
    @endif
  </div>
  @endforeach
</nav>
