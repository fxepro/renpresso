<div class="form-row">
  <div class="form-group">
    <label for="ob_first_name">First name <span class="req">*</span></label>
    <input type="text" class="form-input" id="ob_first_name" name="first_name" value="{{ old('first_name') }}" placeholder="Alex" required autocomplete="given-name">
    @error('first_name') <span class="form-error form-error--block">{{ $message }}</span> @enderror
  </div>
  <div class="form-group">
    <label for="ob_last_name">Last name <span class="req">*</span></label>
    <input type="text" class="form-input" id="ob_last_name" name="last_name" value="{{ old('last_name') }}" placeholder="Johnson" required autocomplete="family-name">
    @error('last_name') <span class="form-error form-error--block">{{ $message }}</span> @enderror
  </div>
</div>

<div class="form-group">
  <label for="ob_email">Email address <span class="req">*</span></label>
  <input type="email" class="form-input" id="ob_email" name="email" value="{{ old('email') }}" placeholder="alex@example.com" required autocomplete="email">
  @error('email') <span class="form-error form-error--block">{{ $message }}</span> @enderror
</div>

<div class="form-row">
  <div class="form-group">
    <label for="home_country">Where do you live? <span class="req">*</span></label>
    <select class="form-select" id="home_country" name="home_country" required>
      <option value="" disabled @selected(! old('home_country'))>Select country</option>
      @foreach (config('world_countries') as $code => $name)
        <option value="{{ $code }}" @selected(old('home_country') === $code)>{{ $name }}</option>
      @endforeach
    </select>
    @error('home_country') <span class="form-error form-error--block">{{ $message }}</span> @enderror
  </div>
  <div class="form-group">
    <label for="portfolio_size">How many properties?</label>
    <select class="form-select" id="portfolio_size" name="portfolio_size">
      <option value="" @selected(! old('portfolio_size'))>Select range</option>
      @foreach (['1 property', '2–5 properties', '6–10 properties', '10+ properties'] as $size)
        <option value="{{ $size }}" @selected(old('portfolio_size') === $size)>{{ $size }}</option>
      @endforeach
    </select>
  </div>
</div>

<div class="form-group">
  <label for="property_countries">Where are your properties?</label>
  <input type="text" class="form-input" id="property_countries" name="property_countries" value="{{ old('property_countries') }}" placeholder="e.g. Texas, Colorado, Arizona">
  @error('property_countries') <span class="form-error form-error--block">{{ $message }}</span> @enderror
</div>

<div class="form-divider"></div>

<div class="form-group">
  <label for="pain_point">What's your biggest pain point right now?</label>
  <select class="form-select" id="pain_point" name="pain_point">
    <option value="" @selected(! old('pain_point'))>Select one (optional)</option>
    @foreach ([
      'Chasing rent across time zones',
      'No single view of all my properties',
      'Currency conversion and FX tracking',
      'Tax reporting across multiple countries',
      'Tenant communication is scattered',
      "Existing apps don't support my countries",
      'Other',
    ] as $pain)
      <option value="{{ $pain }}" @selected(old('pain_point') === $pain)>{{ $pain }}</option>
    @endforeach
  </select>
</div>
