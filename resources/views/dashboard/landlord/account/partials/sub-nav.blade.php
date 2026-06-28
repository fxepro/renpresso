@php
  /** @var array<string, string> $items */
  $queryBase = $queryBase ?? ['tab' => $tab];
@endphp
<nav class="rm-pm-tabs" aria-label="{{ $ariaLabel ?? 'Section' }}" style="margin-bottom:18px">
  <div style="display:flex;flex-wrap:wrap;background:var(--cream-dark);border-radius:8px;padding:3px;gap:2px">
    @foreach($items as $key => $label)
      <a href="{{ route('landlord.account', array_merge($queryBase, ['sec' => $key])) }}"
         class="portfolio-tab {{ ($activeSec ?? '') === $key ? 'active' : '' }}">{{ $label }}</a>
    @endforeach
  </div>
</nav>
