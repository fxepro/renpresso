@props([
    'sectionClass' => 'how',
    'eyebrow',
    'title',
    'lead' => null,
    'inverseTitle' => false,
    'steps',
])

<section class="{{ $sectionClass }}">
  <div class="container">
    <div class="reveal section-header">
      <p class="section-label">{{ $eyebrow }}</p>
      <h2 @class(['section-title', 'section-title--inverse' => $inverseTitle])>{!! $title !!}</h2>
      @if ($lead)
        <p class="section-sub">{!! $lead !!}</p>
      @endif
    </div>
    <div class="steps reveal">
      @foreach ($steps as $step)
        <div class="step">
          <div class="step-num">{{ $step['number'] }}</div>
          <div class="step-icon">{{ $step['icon'] }}</div>
          <h3>{{ $step['title'] }}</h3>
          <p>{{ $step['body'] }}</p>
        </div>
      @endforeach
    </div>
  </div>
</section>
