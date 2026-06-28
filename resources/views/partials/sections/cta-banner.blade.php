@props([
    'title',
    'body',
    'href',
    'label',
    'innerClass' => null,
])

<div class="rm-cta-banner">
  <div @class([$innerClass])>
    <h2>{!! $title !!}</h2>
    <p>{!! $body !!}</p>
    <a href="{{ $href }}" class="rm-cta-btn">{{ $label }}</a>
  </div>
</div>
