@props([
    'eyebrow',
    'title',
    'items',
    'containerClass' => 'container-sm',
])

<section class="faq">
  <div class="{{ $containerClass }}">
    <div class="reveal section-header section-header--center u-text-center">
      <p class="section-label u-text-center">{{ $eyebrow }}</p>
      <h2 class="section-title u-text-center">{!! $title !!}</h2>
    </div>
    <div class="faq-list reveal">
      @foreach ($items as $item)
        <div class="faq-item">
          <button class="faq-q">{!! $item['question'] !!}<span class="faq-arrow">+</span></button>
          <div class="faq-a">{!! $item['answer'] !!}</div>
        </div>
      @endforeach
    </div>
  </div>
</section>
