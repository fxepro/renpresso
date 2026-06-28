@php
  $companySecs = [
    'overview' => ['label' => 'Company profile'],
    'address'  => ['label' => 'Address & operation', 'count' => ($documentsByType['proof_of_registered_address'] ?? null)?->file_path ? 1 : 0],
    'legal'    => ['label' => 'Legal documents', 'count' => $docCounts['business'] ?? 0],
  ];
  $businessSection = $complianceSections['business'] ?? null;
  $addressSection = $complianceSections['address'] ?? null;
@endphp
<p class="db-form-hint" style="margin:0 0 16px;max-width:52rem;line-height:1.55">Your maintenance company on {{ config('app.name') }} — public description, registered address, and KYB documents.</p>

@include('dashboard.maintenance-portal.account.partials.sub-nav', [
  'tab' => 'company',
  'activeKey' => $companySec,
  'items' => $companySecs,
  'ariaLabel' => 'Company sections',
])

@if($companySec === 'overview')
  @if(! $team)
    <div class="db-alert" style="background:var(--gold-pale);color:var(--gold);border:1px solid rgba(201,150,58,0.25)">No team record found for this account. Contact support.</div>
  @else
    @component('dashboard.maintenance-portal.account.partials.section-shell', [
      'title' => 'Company profile',
      'accountTab' => 'company',
      'accountSec' => 'overview',
      'editTarget' => 'overview',
      'hasData' => (bool) $team->name,
    ])
      @slot('view')
        <table class="rm-acc-table">
          <tbody>
            <tr><th>Company name</th><td><strong>{{ $team->name }}</strong></td></tr>
            <tr><th>Description</th><td>{{ $team->description ?: '—' }}</td></tr>
            <tr><th>Phone</th><td>{{ $team->phone ?? '—' }}</td></tr>
            <tr><th>Directory listing</th><td><span class="badge badge-{{ $team->is_listed ? 'green' : 'grey' }}">{{ $team->is_listed ? 'Listed' : 'Not listed' }}</span></td></tr>
          </tbody>
        </table>
      @endslot
      @slot('edit')
        <form method="POST" action="{{ route('maint.account.team') }}" class="db-form db-form--wide">
          @csrf @method('PUT')
          <input type="hidden" name="redirect_tab" value="company">
          <input type="hidden" name="redirect_sec" value="overview">
          <div class="db-form-group"><label>Company name <span class="req">*</span></label><input type="text" name="name" class="db-input" value="{{ old('name', $team->name) }}" required></div>
          <div class="db-form-group"><label>Company description</label><textarea name="description" class="db-textarea" rows="4" placeholder="What you do, years in business, specialties…">{{ old('description', $team->description) }}</textarea></div>
          <div class="db-form-group"><label>Company phone</label><input type="text" name="phone" class="db-input" value="{{ old('phone', $team->phone) }}"></div>
          <label style="display:flex;align-items:center;gap:8px;font-size:14px;margin-top:4px">
            <input type="checkbox" name="is_listed" value="1" @checked(old('is_listed', $team->is_listed))> Listed in landlord directory (discoverable by city)
          </label>
          <button type="submit" class="db-form-submit" style="margin-top:16px">Save company profile</button>
        </form>
      @endslot
    @endcomponent
  @endif
@endif

@if($companySec === 'address')
  @if(! $team)
    <div class="db-alert" style="background:var(--gold-pale);color:var(--gold)">Set up your company profile first.</div>
  @else
    @if($addressSection)
      <p class="db-form-hint" style="margin:0 0 12px">{{ $addressSection['lead'] }}</p>
      @foreach($addressSection['documents'] as $docType => $docDef)
        @include('dashboard.maintenance-portal.account.partials.document-card', [
          'docType' => $docType,
          'docDef' => $docDef,
          'documentsByType' => $documentsByType,
          'accountTab' => 'company',
          'accountSec' => 'address',
        ])
      @endforeach
    @endif

    @component('dashboard.maintenance-portal.account.partials.section-shell', [
      'title' => 'Operating cities',
      'accountTab' => 'company',
      'accountSec' => 'address',
      'editTarget' => 'cities',
      'showEdit' => true,
      'canEdit' => false,
      'hasData' => $operatingCities->isNotEmpty(),
    ])
      @slot('view')
        <table class="rm-acc-table" style="margin-bottom:16px">
          <tbody>
            <tr><th>Primary location</th><td><strong>{{ $team->locationLabel() }}</strong></td></tr>
          </tbody>
        </table>
        @forelse($operatingCities as $city)
          <p style="margin:0 0 8px;font-size:15px;color:var(--text-mid)">
            <strong>{{ $city->city }}</strong>, {{ strtoupper($city->country_code) }}
            @if($city->region)<span style="color:var(--text-light)"> · {{ $city->region }}</span>@endif
            @if($city->is_primary)<span style="font-size:12px;font-weight:600;color:var(--terra);margin-left:6px">Primary</span>@endif
          </p>
        @empty
          <p style="color:var(--text-light);margin:0">No operating cities yet.</p>
        @endforelse
        <p style="margin-top:14px"><a href="{{ route('maint.cities.index') }}" class="db-btn db-btn-primary" style="text-decoration:none;display:inline-block">Manage operating cities</a></p>
      @endslot
      @slot('edit')
        <p class="db-form-hint" style="margin:0">Add or change cities on the <a href="{{ route('maint.cities.index') }}" class="db-table-link">Operating cities</a> page.</p>
      @endslot
    @endcomponent
  @endif
@endif

@if($companySec === 'legal')
  @if(! $team)
    <div class="db-alert" style="background:var(--gold-pale);color:var(--gold)">Set up your company profile first.</div>
  @elseif($businessSection)
    <p class="db-form-hint" style="margin-bottom:12px">{{ $businessSection['lead'] }}</p>
    @foreach($businessSection['documents'] as $docType => $docDef)
      @include('dashboard.maintenance-portal.account.partials.document-card', [
        'docType' => $docType,
        'docDef' => $docDef,
        'documentsByType' => $documentsByType,
        'accountTab' => 'company',
        'accountSec' => 'legal',
      ])
    @endforeach
  @endif
@endif
