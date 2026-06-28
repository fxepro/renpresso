@extends('dashboard.layout')
@section('page-title', 'Add Property')
@section('breadcrumb', '← Properties')
@section('content')
<div style="max-width:640px">
  <div class="db-card">
    <div class="db-card-header"><span class="db-card-title">Property details</span></div>
    <div class="db-card-body">
      <form method="POST" action="{{ route('properties.store') }}" class="db-form">
        @csrf
        @if($errors->any())
          <div class="db-alert db-alert-error">{{ $errors->first() }}</div>
        @endif
        <div class="db-form-row">
          <div class="db-form-group">
            <label>Property name <span class="req">*</span></label>
            <input type="text" name="name" class="db-input" placeholder="e.g. Oak Street Duplex" value="{{ old('name') }}" required>
          </div>
          <div class="db-form-group">
            <label>Country <span class="req">*</span></label>
            <select name="country_code" class="db-select" required>
              <option value="">Select country</option>
              @foreach(config('countries') as $code => $c)
                <option value="{{ $code }}" {{ old('country_code')==$code?'selected':'' }}>
                  {{ $code }} — {{ $c['currency'] }}
                </option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="db-form-group">
          <label>Address <span class="req">*</span></label>
          <input type="text" name="address_line1" class="db-input" placeholder="Street address" value="{{ old('address_line1') }}" required>
        </div>
        <div class="db-form-row">
          <div class="db-form-group">
            <label>City <span class="req">*</span></label>
            <input type="text" name="city" class="db-input" placeholder="City" value="{{ old('city') }}" required>
          </div>
          <div class="db-form-group">
            <label>Type</label>
            <select name="type" class="db-select">
              <option value="apartment" {{ old('type')=='apartment'?'selected':'' }}>Apartment</option>
              <option value="house" {{ old('type')=='house'?'selected':'' }}>House</option>
              <option value="commercial" {{ old('type')=='commercial'?'selected':'' }}>Commercial</option>
              <option value="other" {{ old('type')=='other'?'selected':'' }}>Other</option>
            </select>
          </div>
        </div>
        <div class="db-form-row">
          <div class="db-form-group">
            <label>Occupancy <span class="req">*</span></label>
            <select name="occupancy_mode" class="db-select" id="occupancyMode" required>
              <option value="single" {{ old('occupancy_mode', 'single') === 'single' ? 'selected' : '' }}>Single unit — one lease (house, single flat)</option>
              <option value="multi" {{ old('occupancy_mode') === 'multi' ? 'selected' : '' }}>Multi-unit — building with many leases (apartment complex)</option>
            </select>
            <span class="db-form-hint">Multi-unit: add a lease per unit; send building notices to all tenants at once.</span>
          </div>
          <div class="db-form-group" id="unitCapacityGroup" style="{{ old('occupancy_mode') === 'multi' ? '' : 'display:none' }}">
            <label>Licensed unit slots <span class="req">*</span></label>
            <input type="number" name="unit_capacity" class="db-input" placeholder="e.g. 24" min="1" max="999" value="{{ old('unit_capacity', $defaultMultiUnitCapacity ?? '') }}">
            <span class="db-form-hint">Maximum active leases for this building — ties to your plan / billing. You can raise it as you grow.</span>
          </div>
        </div>
        <div class="db-form-row">
          <div class="db-form-group">
            <label>Bedrooms</label>
            <input type="number" name="bedrooms" class="db-input" placeholder="e.g. 2" min="0" max="99" value="{{ old('bedrooms') }}">
          </div>
          <div class="db-form-group">
            <label>Postal code</label>
            <input type="text" name="postal_code" class="db-input" placeholder="Optional" value="{{ old('postal_code') }}">
          </div>
        </div>
        <div class="db-card" style="margin-top:8px;margin-bottom:20px;border:1px solid var(--cream-dark)">
          <div class="db-card-header"><span class="db-card-title">Sublet (long-term)</span></div>
          <div class="db-card-body">
            <p class="db-form-hint" style="margin:0 0 14px;max-width:40rem">Allow the primary tenant to sublet up to the bedroom count. Background check is always required for sub-letters.</p>
            <label style="display:flex;align-items:center;gap:8px;margin-bottom:12px;font-size:14px;cursor:pointer">
              <input type="checkbox" name="sublet_allowed" value="1" {{ old('sublet_allowed') ? 'checked' : '' }}>
              Sublet allowed on this property
            </label>
            <input type="hidden" name="sublet_bg_check_required" value="1">
            <label style="display:flex;align-items:center;gap:8px;font-size:14px;cursor:pointer">
              <input type="checkbox" name="sublet_landlord_approval_required" value="1" {{ old('sublet_landlord_approval_required', '1') ? 'checked' : '' }}>
              Landlord must approve each sub-letter before sub-lease is active
            </label>
          </div>
        </div>
        <div class="db-form-row">
          <div class="db-form-group">
            <label>Rental length <span class="req">*</span></label>
            <select name="rental_mode" class="db-select" required>
              <option value="long_term" {{ old('rental_mode', 'long_term') === 'long_term' ? 'selected' : '' }}>Long-term (monthly lease)</option>
              <option value="short_term" {{ old('rental_mode') === 'short_term' ? 'selected' : '' }}>Short-term (nightly / flexible)</option>
            </select>
          </div>
          <div class="db-form-group">
            <label>Directory visibility <span class="req">*</span></label>
            <select name="listing_visibility" class="db-select" required>
              <option value="private" {{ old('listing_visibility', 'private') === 'private' ? 'selected' : '' }}>Private — not shown publicly (payments &amp; dashboard only)</option>
              <option value="public" {{ old('listing_visibility') === 'public' ? 'selected' : '' }}>Public — may appear in our rental directories</option>
            </select>
          </div>
        </div>
        <button type="submit" class="db-form-submit">Save property →</button>
      </form>
    </div>
  </div>
</div>
@push('scripts')
<script>
(function(){
  const defCap = @json($defaultMultiUnitCapacity ?? null);
  document.getElementById('occupancyMode')?.addEventListener('change', function () {
    const g = document.getElementById('unitCapacityGroup');
    if (!g) return;
    if (this.value === 'multi') {
      g.style.display = '';
      const inp = document.querySelector('#unitCapacityGroup input[name="unit_capacity"]');
      if (inp && !inp.value && defCap) inp.value = String(defCap);
    } else {
      g.style.display = 'none';
    }
  });
})();
</script>
@endpush
@endsection
