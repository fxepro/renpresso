@extends('dashboard.cleaning-portal.layout')
@section('page-title', 'Crew profile')
@section('breadcrumb', 'Operations')
@section('content')
<div class="db-card" style="max-width:720px">
  <div class="db-card-header"><span class="db-card-title">{{ $team->name }}</span></div>
  <div class="db-card-body">
    <form method="POST" action="{{ route('clean.team.update') }}" class="db-form" style="max-width:100%">
      @csrf @method('PUT')
      <div class="db-form-group"><label>Crew / company name</label><input type="text" name="name" class="db-input" value="{{ old('name', $team->name) }}" required></div>
      <div class="db-form-group"><label>Description</label><textarea name="description" class="db-textarea" rows="4">{{ old('description', $team->description) }}</textarea></div>
      <div class="db-form-group"><label>Phone</label><input type="text" name="phone" class="db-input" value="{{ old('phone', $team->phone) }}"></div>
      <div class="db-form-group">
        <label>Services (comma-separated)</label>
        <input type="text" name="services" class="db-input" value="{{ old('services', implode(', ', $team->normalizedServices())) }}" placeholder="Turnover clean, Linen, Deep clean">
      </div>
      <label style="display:flex;align-items:center;gap:8px;font-size:14px">
        <input type="checkbox" name="is_listed" value="1" @checked(old('is_listed', $team->is_listed))> Listed in landlord directory
      </label>
      <p style="font-size:13px;color:var(--text-light);margin:12px 0 0">Primary location: {{ $team->locationLabel() }} — manage all areas under <a href="{{ route('clean.cities.index') }}" class="db-table-link">Operating cities</a>.</p>
      <button type="submit" class="db-form-submit" style="margin-top:16px">Save profile</button>
    </form>
  </div>
</div>
@endsection
