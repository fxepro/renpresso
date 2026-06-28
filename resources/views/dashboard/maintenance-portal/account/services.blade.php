@php
  $selected = collect(old('services', $team?->normalizedServices() ?? []))->map(fn ($s) => trim((string) $s))->filter()->values()->all();
  $serviceOptions = config('maintenance_services', []);
  $servicesHasData = count($selected) > 0;
@endphp
<p class="db-form-hint" style="margin:0 0 16px;max-width:52rem;line-height:1.55">Select the trades and services your company offers. Landlords use this when searching for maintenance teams.</p>

@if(! $team)
  <div class="db-alert" style="background:var(--gold-pale);color:var(--gold);border:1px solid rgba(201,150,58,0.25)">
    Complete your <a href="{{ route('maint.account', ['tab' => 'company', 'sec' => 'overview']) }}" class="db-table-link" style="color:inherit;font-weight:600">company profile</a> first.
  </div>
@else
  @component('dashboard.maintenance-portal.account.partials.section-shell', [
    'title' => 'Services offered',
    'accountTab' => 'services',
    'accountSec' => null,
    'editTarget' => 'services',
    'hasData' => $servicesHasData,
    'addLabel' => 'Add services',
  ])
    @slot('view')
      @if($servicesHasData)
        <div style="display:flex;flex-wrap:wrap;gap:8px">
          @foreach($selected as $service)
            <span class="badge badge-navy">{{ $service }}</span>
          @endforeach
        </div>
      @else
        <p class="db-form-hint" style="margin:0">No services selected yet.</p>
      @endif
    @endslot
    @slot('edit')
      <form method="POST" action="{{ route('maint.account.team') }}" class="db-form db-form--wide">
        @csrf @method('PUT')
        <input type="hidden" name="redirect_tab" value="services">
        <div class="rm-check-grid">
          @foreach($serviceOptions as $option)
            @php $checked = in_array($option, $selected, true); @endphp
            <label class="rm-check-option">
              <input type="checkbox" name="services[]" value="{{ $option }}" {{ $checked ? 'checked' : '' }}>
              <span><strong>{{ $option }}</strong></span>
            </label>
          @endforeach
        </div>
        <div class="db-form-group" style="margin-top:16px">
          <label>Additional services (comma-separated)</label>
          <input type="text" name="services_extra" class="db-input" placeholder="e.g. Pool maintenance, Smart home"
                 value="{{ old('services_extra', collect($selected)->diff($serviceOptions)->implode(', ')) }}">
          <span class="db-form-hint">Anything not in the list above.</span>
        </div>
        <button type="submit" class="db-form-submit">Save services</button>
      </form>
    @endslot
  @endcomponent
@endif
