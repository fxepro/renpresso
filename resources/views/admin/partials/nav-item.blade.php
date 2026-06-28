@php
  $href = isset($item['page'])
      ? route($item['route'], $item['page'])
      : route($item['route']);
  if (isset($item['page'])) {
      $active = request()->routeIs('admin.page') && request()->route('page') === $item['page'];
  } elseif (str_starts_with($item['route'] ?? '', 'admin.settings.')) {
      $active = request()->routeIs($item['route']) || request()->routeIs($item['route'].'.*');
  } else {
      $active = request()->routeIs($item['route']) || request()->routeIs($item['route'].'.*');
  }
  $classes = 'db-nav-item'.($active ? ' active' : '').(!empty($item['soon']) ? ' db-nav-item--soon' : '');
@endphp
<a href="{{ $href }}" class="{{ $classes }}">
  <span class="ni">{{ $item['icon'] }}</span>
  <span class="db-nav-txt">{{ $item['label'] }}</span>
  @if(!empty($item['soon']))<span class="db-nav-soon">Soon</span>@endif
</a>
