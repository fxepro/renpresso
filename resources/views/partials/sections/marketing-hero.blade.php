@props([
    'label',
    'title',
    'lead',
    'ctas' => [],
])

<div class="page-hero">
  <div class="page-hero-grid"></div>
  <div class="page-hero-glow"></div>
  <div class="page-hero-label">{{ $label }}</div>
  <h1>{!! $title !!}</h1>
  <p class="page-hero-lead">{!! $lead !!}</p>
  @if (! empty($ctas))
    <div class="page-hero-ctas">
      @foreach ($ctas as $cta)
        <a href="{{ $cta['href'] }}" class="{{ $cta['class'] ?? 'rm-btn rm-btn-primary btn-lg' }}">{{ $cta['label'] }}</a>
      @endforeach
    </div>
  @endif
</div>
