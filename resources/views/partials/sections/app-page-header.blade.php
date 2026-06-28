@props([
    'kicker' => null,
    'showContext' => false,
    'showMarketingLink' => false,
])

<header class="app-page-header db-topbar" role="banner">
  <div class="db-topbar-left">
    @if ($kicker)
      <span class="admin-topbar-kicker">{{ $kicker }}</span>
    @endif
    <span class="db-page-title">@yield('page-title', 'Dashboard')</span>
    @hasSection('breadcrumb')
      <span class="db-breadcrumb">@yield('breadcrumb')</span>
    @endif
  </div>
  <div class="db-topbar-right">
    @yield('topbar-actions')
    @if ($showMarketingLink)
      <a href="{{ route('home') }}" class="db-btn db-btn-ghost db-topbar-link">Marketing site</a>
    @endif
    @if ($showContext)
      @include('partials.db-topbar-context')
    @endif
  </div>
</header>
