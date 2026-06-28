@php
  $flags = ['FR'=>'🇫🇷','GB'=>'🇬🇧','US'=>'🇺🇸','IN'=>'🇮🇳','DE'=>'🇩🇪','AU'=>'🇦🇺','CA'=>'🇨🇦','NG'=>'🇳🇬','ID'=>'🇮🇩','PH'=>'🇵🇭','BR'=>'🇧🇷','MX'=>'🇲🇽','ZA'=>'🇿🇦','KE'=>'🇰🇪','SG'=>'🇸🇬','JP'=>'🇯🇵','ES'=>'🇪🇸','IT'=>'🇮🇹','NL'=>'🇳🇱','PT'=>'🇵🇹','BE'=>'🇧🇪','SE'=>'🇸🇪','NO'=>'🇳🇴','DK'=>'🇩🇰','PL'=>'🇵🇱','CH'=>'🇨🇭','MY'=>'🇲🇾','TH'=>'🇹🇭','VN'=>'🇻🇳','HK'=>'🇭🇰','NZ'=>'🇳🇿'];
  $flag = $flags[$property->country_code] ?? '🏠';
  $method = config('countries.'.$property->country_code.'.method', '—');
  $photos = $property->getMedia('photos');
  $videos = $property->getMedia('videos');
  $heroSub = $property->city.', '.$property->country_code.' · '.ucfirst($property->type);
  if ($property->bedrooms !== null) {
      $heroSub .= ' · '.$property->bedrooms.' bedrooms';
  }
@endphp
@extends('layouts.marketing', ['page' => 'listings'])

@section('title', $property->name . ' — Long-term')
@section('meta_description', 'Long-term rental listing: ' . $property->name . ' in ' . $property->city . ', ' . $property->country_code . '.')

@section('content')

@include('partials.sections.marketing-hero', [
  'label' => 'Long-term rental',
  'title' => $flag . ' ' . e($property->name),
  'lead' => e($heroSub),
  'ctas' => [
    ['href' => route('listings.long-term'), 'label' => '← All long-term listings', 'class' => 'btn-outline-light'],
    ['href' => route('waitlist'), 'label' => 'Join waitlist', 'class' => 'rm-btn rm-btn-primary btn-lg'],
  ],
])

<section class="listing-main">
  <div class="container-sm">

    @if ($photos->isNotEmpty())
      <div class="listing-detail-block">
        <h2>Photos</h2>
        <div class="lc-photo-grid">
          @foreach ($photos as $m)
            <a href="{{ $m->getUrl() }}" target="_blank" rel="noopener noreferrer">
              <img src="{{ $m->getUrl() }}" alt="" loading="lazy">
            </a>
          @endforeach
        </div>
      </div>
    @endif

    @if ($videos->isNotEmpty())
      <div class="listing-detail-block">
        <h2>Videos</h2>
        <div class="lc-video-stack">
          @foreach ($videos as $m)
            <div class="lc-video-box">
              <video src="{{ $m->getUrl() }}" controls playsinline preload="metadata"></video>
            </div>
          @endforeach
        </div>
      </div>
    @endif

    <div class="info-panel">
      <p>This is a <strong>public directory</strong> listing. Rent and availability are managed by the landlord inside {{ config('app.name') }}.
        To enquire or apply, create an account or join the waitlist — guest booking flows can be enabled as the product matures.</p>
      <dl class="info-dl">
        <div class="info-row">
          <dt class="info-dt">Local payment method</dt>
          <dd class="info-dd">{{ $method }}</dd>
        </div>
        <div class="info-row">
          <dt class="info-dt">Currency</dt>
          <dd class="info-dd">{{ $property->currency_code }}</dd>
        </div>
        <div class="info-row">
          <dt class="info-dt">Listing type</dt>
          <dd class="info-dd">Long-term · Public</dd>
        </div>
      </dl>
    </div>
    <p class="listing-disclaimer">Map and exact address are not shown on public listings.</p>

  </div>
</section>

@endsection
