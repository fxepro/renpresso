@php
  /** @var \Illuminate\Contracts\Pagination\LengthAwarePaginator $properties */
  $flags = ['FR'=>'🇫🇷','GB'=>'🇬🇧','US'=>'🇺🇸','IN'=>'🇮🇳','DE'=>'🇩🇪','AU'=>'🇦🇺','CA'=>'🇨🇦','NG'=>'🇳🇬','ID'=>'🇮🇩','PH'=>'🇵🇭','BR'=>'🇧🇷','MX'=>'🇲🇽','ZA'=>'🇿🇦','KE'=>'🇰🇪','SG'=>'🇸🇬','JP'=>'🇯🇵','ES'=>'🇪🇸','IT'=>'🇮🇹','NL'=>'🇳🇱','PT'=>'🇵🇹','BE'=>'🇧🇪','SE'=>'🇸🇪','NO'=>'🇳🇴','DK'=>'🇩🇰','PL'=>'🇵🇱','CH'=>'🇨🇭','MY'=>'🇲🇾','TH'=>'🇹🇭','VN'=>'🇻🇳','HK'=>'🇭🇰','NZ'=>'🇳🇿'];
  $listingIndexUrl = route('listings.short-term');
@endphp
@extends('layouts.marketing', ['page' => 'listings'])

@section('title', 'Short-term stays')
@section('meta_description', 'Browse short-term stay listings from landlords on Renpresso.')

@section('content')

@include('partials.sections.marketing-hero', [
  'label' => 'Directory',
  'title' => 'Short-term <em>stays</em>',
  'lead' => 'Public listings for nightly or flexible stays — similar to how you might list on other travel sites, with ' . config('app.name') . ' handling local payments where supported.',
  'ctas' => [
    ['href' => route('listings.long-term'), 'label' => 'Long-term rentals →', 'class' => 'btn-outline-light'],
    ['href' => route('waitlist'), 'label' => 'Join waitlist', 'class' => 'rm-btn rm-btn-primary btn-lg'],
  ],
])

<section class="listing-main">
  <div class="container">

    <form method="get" action="{{ $listingIndexUrl }}" class="listing-search-form">
      <div class="listing-field">
        <label for="listing-country">Country</label>
        <select id="listing-country" name="country">
          <option value="">Any country</option>
          @foreach (config('countries', []) as $code => $c)
            @php
              $isCountry = strtoupper((string) request('country', '')) === $code;
            @endphp
            <option value="{{ $code }}" @if ($isCountry) selected @endif>{{ $code }} — {{ $c['currency'] ?? '' }}</option>
          @endforeach
        </select>
      </div>
      <div class="listing-field listing-field-grow">
        <label for="listing-q">Search</label>
        <input id="listing-q" type="search" name="q" value="{{ request('q', '') }}" placeholder="City or property name…">
      </div>
      <button type="submit" class="rm-btn rm-btn-primary lc-search-btn">Search</button>
      @if (request()->filled('country') || request()->filled('q'))
        <a href="{{ $listingIndexUrl }}" class="rm-btn rm-btn-ghost lc-search-btn">Clear</a>
      @endif
    </form>

    @if ($properties->isEmpty())
      <div class="lc-empty">
        <div class="lc-empty-icon">✈️</div>
        <p class="lc-empty-title">No public short-term listings match your filters yet.</p>
        <p class="lc-empty-sub">Landlords can set a property to <strong>public</strong> and <strong>short-term</strong> in the dashboard to appear here.</p>
      </div>
    @else
      <div class="lc-grid">
        @foreach ($properties as $prop)
          @php
            $flag = $flags[$prop->country_code] ?? '🏠';
            $method = config('countries.'.$prop->country_code.'.method', '—');
            $coverUrl = $prop->getFirstMediaUrl('photos');
            $coverHtml = $coverUrl !== '' && $coverUrl !== '0'
              ? '<div class="lc-cover"><img src="'.e($coverUrl).'" alt="" loading="lazy" width="560" height="210"></div>'
              : '';
            $bdHtml = $prop->bedrooms !== null
              ? '<span>·</span><span>'.e((string) $prop->bedrooms).' bd</span>'
              : '';
          @endphp
          <a class="prop-card" href="{{ route('listings.short-term.show', $prop) }}">
            {!! $coverHtml !!}
            <div class="prop-card-top">
              <span class="prop-card-flag">{{ $flag }}</span>
              <span class="lc-badge lc-badge-short">Short-term</span>
            </div>
            <div class="prop-card-name">{{ $prop->name }}</div>
            <div class="prop-card-addr">{{ $prop->city }}, {{ $prop->country_code }}</div>
            <div class="prop-card-divider"></div>
            <div class="prop-card-meta">
              <span>{{ ucfirst($prop->type) }}</span>
              {!! $bdHtml !!}
              <span>·</span>
              <span>{{ $method }}</span>
            </div>
          </a>
        @endforeach
      </div>
      <div class="pagination-wrap">
        {{ $properties->withQueryString()->links() }}
      </div>
    @endif

  </div>
</section>

@endsection
