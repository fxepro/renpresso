@php
  /** @var array<string, array{label: string, count?: int|null}> $items */
  $queryBase = $queryBase ?? [];
@endphp
<nav class="rm-pm-tabs" aria-label="{{ $ariaLabel ?? 'Section' }}" style="margin-bottom:18px">
  <div style="display:flex;flex-wrap:wrap;background:var(--cream-dark);border-radius:8px;padding:3px;gap:2px">
    @foreach($items as $key => $item)
      @php
        $active = ($activeKey ?? '') === $key;
        $href = route('maint.account', array_merge($queryBase, ['tab' => $tab, 'sec' => $key]));
        $count = $item['count'] ?? null;
      @endphp
      <a href="{{ $href }}" class="portfolio-tab {{ $active ? 'active' : '' }}">
        {{ $item['label'] }}@if($count !== null && $count > 0)<span class="rm-pm-tab-count">{{ $count }}</span>@endif
      </a>
    @endforeach
  </div>
</nav>
