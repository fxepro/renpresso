@php
  $rating = $rating ?? 0;
  $size = $size ?? 'md';
  $showValue = $showValue ?? true;
@endphp
<span class="mt-stars {{ $size === 'sm' ? 'mt-stars-sm' : '' }}" aria-label="{{ $rating }} out of 5 stars">
  @for($i = 1; $i <= 5; $i++)
    <span class="mt-star {{ $i <= round($rating) ? 'filled' : '' }}">★</span>
  @endfor
  @if($showValue && $rating)
    <span class="mt-rating-num">{{ number_format($rating, 1) }}</span>
  @endif
</span>
