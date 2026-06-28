@php
  /** @var \Illuminate\Contracts\Pagination\LengthAwarePaginator $properties */
  /** @var string $tab */
  $flags = ['FR'=>'🇫🇷','GB'=>'🇬🇧','US'=>'🇺🇸','IN'=>'🇮🇳','DE'=>'🇩🇪','AU'=>'🇦🇺','CA'=>'🇨🇦','NG'=>'🇳🇬','ID'=>'🇮🇩','PH'=>'🇵🇭','BR'=>'🇧🇷','MX'=>'🇲🇽','ZA'=>'🇿🇦','KE'=>'🇰🇪','SG'=>'🇸🇬','JP'=>'🇯🇵','ES'=>'🇪🇸','IT'=>'🇮🇹','NL'=>'🇳🇱','PT'=>'🇵🇹','BE'=>'🇧🇪','SE'=>'🇸🇪','NO'=>'🇳🇴','DK'=>'🇩🇰','PL'=>'🇵🇱','CH'=>'🇨🇭','MY'=>'🇲🇾','TH'=>'🇹🇭','VN'=>'🇻🇳','HK'=>'🇭🇰','NZ'=>'🇳🇿'];

  $tabs = [
    'long-term'  => ['label' => 'Long-term',  'badge' => 'Long-term',  'badgeClass' => 'lc-badge-long',      'emptyIcon' => '🏠'],
    'short-term' => ['label' => 'Short-term', 'badge' => 'Short-term', 'badgeClass' => 'lc-badge-short',     'emptyIcon' => '🌴'],
    'sublets'    => ['label' => 'Sublets',    'badge' => 'Sublet',     'badgeClass' => 'lc-badge-sublet',    'emptyIcon' => '🔑'],
    'roommates'  => ['label' => 'Roommates',  'badge' => 'Roommate',   'badgeClass' => 'lc-badge-roommate',  'emptyIcon' => '🛏️'],
  ];

  $activeTab = $tabs[$tab] ?? $tabs['long-term'];
  $emptyType = match ($tab) {
    'short-term' => 'short-term stays',
    'sublets'    => 'sublet listings',
    'roommates'  => 'roommate listings',
    default      => 'long-term listings',
  };
  $emptyHint = match ($tab) {
    'short-term' => 'Set the property to <strong>public</strong> and <strong>short-term</strong> in the dashboard.',
    'sublets'    => 'Enable <strong>sublets</strong> on a public long-term property to list an entire-unit sublet.',
    'roommates'  => 'Enable <strong>sublets</strong> on a public property with 2+ bedrooms to list individual rooms.',
    default      => 'Set the property to <strong>public</strong> and <strong>long-term</strong> in the dashboard.',
  };
@endphp
@extends('layouts.marketing', ['page' => 'listings'])

@section('title', 'Listings')
@section('meta_description', 'Browse long-term rentals, short-term stays, sublets, and roommate listings on Renpresso. Filter by country or search by city.')

@section('content')

@include('partials.sections.marketing-hero', [
  'label' => 'Directory',
  'title' => 'Property <em>listings</em>',
  'lead' => 'Browse rentals from landlords using ' . config('app.name') . ' across ' . count(config('countries', [])) . '+ countries.',
  'ctas' => [
    ['href' => route('waitlist'), 'label' => 'List your property →', 'class' => 'rm-btn rm-btn-primary btn-lg'],
  ],
])

<section class="listing-main">
  <div class="container">

    <div class="listings-tabs">
      @foreach ($tabs as $key => $meta)
        <a href="{{ route('listings.index', ['tab' => $key]) }}"
           class="listings-tab {{ $tab === $key ? 'active' : '' }}">
          {{ $meta['label'] }}
          @if ($tab === $key)
            <span class="listings-tab-count">{{ $properties->total() }}</span>
          @endif
        </a>
      @endforeach
    </div>

    <form method="get" action="{{ route('listings.index') }}" class="listing-search-form">
      <input type="hidden" name="tab" value="{{ $tab }}">
      <div class="listing-field">
        <label for="listing-country">Country</label>
        <select id="listing-country" name="country">
          <option value="">Any country</option>
          @foreach (config('countries', []) as $code => $c)
            <option value="{{ $code }}" @selected(strtoupper((string) request('country', '')) === $code)>
              {{ $code }} — {{ $c['currency'] ?? '' }}
            </option>
          @endforeach
        </select>
      </div>
      <div class="listing-field listing-field-grow">
        <label for="listing-q">Search</label>
        <input id="listing-q" type="search" name="q" value="{{ request('q', '') }}" placeholder="City or property name…">
      </div>
      <button type="submit" class="rm-btn rm-btn-primary lc-search-btn">Search</button>
      @if (request()->filled('country') || request()->filled('q'))
        <a href="{{ route('listings.index', ['tab' => $tab]) }}" class="rm-btn rm-btn-ghost lc-search-btn">Clear</a>
      @endif
    </form>

    @if ($properties->isEmpty())
      <div class="lc-empty">
        <div class="lc-empty-icon">{{ $activeTab['emptyIcon'] }}</div>
        <p class="lc-empty-title">No public {{ $emptyType }} match your filters yet.</p>
        <p class="lc-empty-sub">{!! $emptyHint !!}</p>
      </div>
    @else
      <div class="lc-grid">
        @foreach ($properties as $prop)
          @php
            $flag     = $flags[$prop->country_code] ?? '🏠';
            $method   = config('countries.'.$prop->country_code.'.method', '—');
            $coverUrl = $prop->getFirstMediaUrl('photos');
            $coverHtml = $coverUrl !== '' && $coverUrl !== '0'
              ? '<div class="lc-cover"><img src="'.e($coverUrl).'" alt="" loading="lazy" width="560" height="210"></div>'
              : '';
            $bdHtml = $prop->bedrooms !== null
              ? '<span>·</span><span>'.e((string) $prop->bedrooms).' bd</span>'
              : '';
            $scopeHtml = match ($tab) {
              'sublets'   => '<span>·</span><span>Entire unit</span>',
              'roommates' => '<span>·</span><span>Per room</span>',
              default     => '',
            };
            $showRoute = $tab === 'short-term'
              ? route('listings.short-term.show', $prop)
              : route('listings.long-term.show', $prop);
          @endphp
          <a class="prop-card" href="{{ $showRoute }}">
            {!! $coverHtml !!}
            <div class="prop-card-top">
              <span class="prop-card-flag">{{ $flag }}</span>
              <span class="lc-badge {{ $activeTab['badgeClass'] }}">{{ $activeTab['badge'] }}</span>
            </div>
            <div class="prop-card-name">{{ $prop->name }}</div>
            <div class="prop-card-addr">{{ $prop->city }}, {{ $prop->country_code }}</div>
            <div class="prop-card-divider"></div>
            <div class="prop-card-meta">
              <span>{{ ucfirst($prop->type) }}</span>
              {!! $bdHtml !!}
              {!! $scopeHtml !!}
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
