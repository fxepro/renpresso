@extends('dashboard.maintenance-portal.layout')
@section('page-title', 'Operating cities')
@section('breadcrumb', 'Where you want to operate')
@section('content')
@php $countries = config('countries', []); @endphp
<p style="font-size:15px;color:var(--text-mid);line-height:1.6;max-width:720px;margin-bottom:20px">List every city and country where your team takes jobs. Landlords find you by property location — add all areas you cover, and mark one as <strong>primary</strong> for your public profile.</p>
<div class="maint-grid-2" style="align-items:start">
  <div class="db-card">
  @if($editingCity)
    <div class="db-card-header" style="flex-wrap:wrap;gap:8px">
      <span class="db-card-title">Edit operating city</span>
      <a href="{{ route('maint.cities.index') }}" class="db-btn db-btn-ghost" style="font-size:13px;text-decoration:none">Cancel</a>
    </div>
    <div class="db-card-body">
      <form method="POST" action="{{ route('maint.cities.update', $editingCity) }}" class="db-form">
        @csrf @method('PUT')
        <div class="db-form-row">
          <div class="db-form-group"><label>City</label><input type="text" name="city" class="db-input" value="{{ old('city', $editingCity->city) }}" required></div>
          <div class="db-form-group">
            <label>Country</label>
            <select name="country_code" class="db-select" required>
              @foreach($countries as $code => $meta)
                <option value="{{ $code }}" @selected(old('country_code', $editingCity->country_code) === $code)>{{ $code }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="db-form-group"><label>Region (optional)</label><input type="text" name="region" class="db-input" value="{{ old('region', $editingCity->region) }}"></div>
        <label style="display:flex;align-items:center;gap:8px;font-size:14px;margin-bottom:12px"><input type="checkbox" name="is_primary" value="1" @checked(old('is_primary', $editingCity->is_primary))> Set as primary</label>
        <button type="submit" class="db-form-submit">Save changes</button>
      </form>
    </div>
  @else
    <div class="db-card-header"><span class="db-card-title">Add operating city</span></div>
    <div class="db-card-body">
      <form method="POST" action="{{ route('maint.cities.store') }}" class="db-form">
        @csrf
        <div class="db-form-row">
          <div class="db-form-group"><label>City</label><input type="text" name="city" class="db-input" value="{{ old('city') }}" required></div>
          <div class="db-form-group">
            <label>Country</label>
            <select name="country_code" class="db-select" required>
              @foreach($countries as $code => $meta)
                <option value="{{ $code }}" @selected(old('country_code', $team->country_code) === $code)>{{ $code }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="db-form-group"><label>Region (optional)</label><input type="text" name="region" class="db-input" value="{{ old('region') }}"></div>
        <label style="display:flex;align-items:center;gap:8px;font-size:14px;margin-bottom:12px"><input type="checkbox" name="is_primary" value="1" @checked(old('is_primary'))> Set as primary</label>
        <button type="submit" class="db-form-submit">Add city</button>
      </form>
    </div>
  @endif
  </div>
  <div class="db-card">
    <div class="db-card-header"><span class="db-card-title">Coverage ({{ $cities->count() }})</span></div>
    <div class="db-card-body" style="padding:0">
      <div class="db-table-wrap">
        <table class="db-table">
          <thead>
            <tr>
              <th>City</th>
              <th>Country</th>
              <th>Region</th>
              <th>Primary</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            @forelse($cities as $city)
              <tr @if($editingCity && $editingCity->id === $city->id) style="background:var(--terra-pale)" @endif>
                <td><strong>{{ $city->city }}</strong></td>
                <td>{{ strtoupper($city->country_code) }}</td>
                <td>{{ $city->region ?: '—' }}</td>
                <td>
                  @if($city->is_primary)
                    <span class="badge badge-gold">Primary</span>
                  @else
                    <span style="color:var(--text-light)">—</span>
                  @endif
                </td>
                <td style="white-space:nowrap">
                  <a href="{{ route('maint.cities.index', ['edit' => $city->id]) }}" class="db-table-link">Edit</a>
                  @if($cities->count() > 1)
                    <form method="POST" action="{{ route('maint.cities.destroy', $city) }}" style="display:inline;margin-left:10px" onsubmit="return confirm('Remove {{ $city->city }} from coverage?')">
                      @csrf @method('DELETE')
                      <button type="submit" class="db-table-link" style="border:none;background:none;padding:0;cursor:pointer;color:var(--red)">Remove</button>
                    </form>
                  @endif
                </td>
              </tr>
            @empty
              <tr><td colspan="5" style="padding:20px;color:var(--text-light)">No operating cities yet — add one on the left.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection
