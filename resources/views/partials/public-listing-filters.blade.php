{{-- Search form for public listing pages (GET). Expects: $action, optional $country, $q --}}
@props([
    'action',
    'country' => '',
    'q' => '',
])
<form method="GET" action="{{ $action }}" class="listing-search-form" style="margin-bottom:32px;padding:20px;background:var(--cream-dark);border-radius:var(--radius);display:flex;flex-wrap:wrap;gap:14px;align-items:flex-end">
  <div>
    <label for="listing-country" style="display:block;font-size:12px;font-weight:600;color:var(--text-mid);margin-bottom:6px">Country</label>
    <select id="listing-country" name="country" style="min-width:200px;padding:10px 12px;border-radius:8px;border:1px solid var(--cream-dark);font-family:'Outfit',sans-serif;font-size:15px;background:var(--white)">
      <option value="">Any country</option>
      @foreach(config('countries', []) as $code => $c)
        <option value="{{ $code }}" @selected(strtoupper((string) $country) === $code)>{{ $code }} — {{ $c['currency'] ?? '' }}</option>
      @endforeach
    </select>
  </div>
  <div style="flex:1;min-width:200px">
    <label for="listing-q" style="display:block;font-size:12px;font-weight:600;color:var(--text-mid);margin-bottom:6px">Search</label>
    <input id="listing-q" type="search" name="q" value="{{ $q }}" placeholder="City or property name…" style="width:100%;padding:10px 12px;border-radius:8px;border:1px solid var(--cream-dark);font-family:'Outfit',sans-serif;font-size:15px">
  </div>
  <button type="submit" class="rm-btn rm-btn-primary" style="margin-bottom:1px">Search</button>
  @if(request()->filled('country') || request()->filled('q'))
    <a href="{{ $action }}" class="rm-btn rm-btn-ghost" style="margin-bottom:1px">Clear</a>
  @endif
</form>
