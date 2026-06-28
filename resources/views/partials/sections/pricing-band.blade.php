@props([
    'sectionClass' => 'pricing',
    'eyebrow' => null,
    'title' => null,
    'lead' => null,
    'inverseTitle' => false,
    'plans',
    'footnote' => null,
    'cardVariant' => 'home',
])

<section class="{{ $sectionClass }}">
  <div class="container">
    @if ($eyebrow || $title || $lead)
      <div class="reveal">
        @if ($eyebrow)
          <p class="section-label">{{ $eyebrow }}</p>
        @endif
        @if ($title)
          <h2 @class(['section-title', 'section-title--inverse' => $inverseTitle])>{!! $title !!}</h2>
        @endif
        @if ($lead)
          <p class="section-sub">{!! $lead !!}</p>
        @endif
      </div>
    @endif

    <div class="pricing-grid reveal">
      @foreach ($plans as $plan)
        <div @class(['price-card', 'featured' => $plan['featured'] ?? false])>
          @if (! empty($plan['popular']))
            <div class="price-popular">{{ $plan['popular'] }}</div>
          @endif
          <p class="{{ $cardVariant === 'pricing' ? 'price-tier' : 'price-tag' }}">{{ $plan['name'] }}</p>
          <div @class(['price-amount', 'price-amount--talk' => $cardVariant === 'home' && ! empty($plan['talk']), 'price-amount--agency' => $cardVariant === 'pricing' && ! empty($plan['talk'])])>
            @if (! empty($plan['talk']))
              <span class="{{ $cardVariant === 'pricing' ? 'number' : 'price-number' }} price-number--talk">{!! $plan['amount'] !!}</span>
            @else
              <span class="{{ $cardVariant === 'pricing' ? 'currency' : 'price-currency' }}">{{ $plan['currency'] ?? '$' }}</span>
              <span class="{{ $cardVariant === 'pricing' ? 'number' : 'price-number' }}">{{ $plan['amount'] }}</span>
              <span class="{{ $cardVariant === 'pricing' ? 'period' : 'price-period' }}">{{ $plan['period'] }}</span>
            @endif
          </div>
          <p class="price-desc">{{ $plan['description'] }}</p>
          <ul class="{{ $cardVariant === 'pricing' ? 'price-features' : 'price-feats' }}">
            @foreach ($plan['features'] as $feature)
              <li>
                @if ($cardVariant === 'pricing')
                  <span class="{{ ($feature['available'] ?? true) ? 'pf-check' : 'pf-dash' }}">{{ ($feature['available'] ?? true) ? '✓' : '–' }}</span>
                  {{ $feature['label'] }}
                @else
                  {{ is_array($feature) ? $feature['label'] : $feature }}
                @endif
              </li>
            @endforeach
          </ul>
          <a href="{{ $plan['cta']['href'] }}" class="{{ $plan['cta']['class'] ?? ($cardVariant === 'pricing' ? 'price-cta price-cta-outline' : 'price-btn') }}">{{ $plan['cta']['label'] }}</a>
        </div>
      @endforeach
    </div>

    @if ($footnote)
      <p class="{{ $cardVariant === 'pricing' ? 'footnote-light' : 'footnote-muted' }}">{!! $footnote !!}</p>
    @endif
  </div>
</section>
