@props([
    'eyebrow' => null,
    'title',
    'meta' => null,
    'tone' => 'dark',
])

<header @class(['page-header', 'page-header--' . $tone])>
  <div class="page-header__inner">
    @if ($eyebrow)
      <p class="page-header__eyebrow">{{ $eyebrow }}</p>
    @endif
    <h1 class="page-header__title">{{ $title }}</h1>
    @if ($meta)
      <p class="page-header__meta">{!! $meta !!}</p>
    @endif
  </div>
</header>
