@extends('dashboard.layout')
@section('page-title', 'Cleaning crews')

@section('topbar-actions')
  <button type="button" class="db-btn db-btn-ghost" onclick="document.getElementById('invitePanel').classList.toggle('open')">Invite crew</button>
@endsection

@push('styles')
<style>
.mt-invite-panel { display:none; position:fixed; inset:0; z-index:400; background:rgba(13,31,53,0.35); align-items:flex-start; justify-content:flex-end; padding:24px; }
.mt-invite-panel.open { display:flex; }
.mt-invite-sheet { background:var(--white); border-radius:var(--radius); border:1px solid var(--cream-dark); width:min(420px,100%); padding:24px; box-shadow:0 8px 32px rgba(0,0,0,0.12); }
.mt-invite-sheet h3 { font-size:var(--fs-title); margin-bottom:8px; }
.mt-tabs { display:flex; gap:4px; padding:0 24px; border-bottom:1px solid var(--cream-dark); background:var(--cream); }
.mt-tab { padding:14px 18px; font-family:'Outfit',sans-serif; font-size:var(--fs-body); font-weight:500; color:var(--text-light); text-decoration:none; border-bottom:2px solid transparent; margin-bottom:-1px; transition:color 0.15s, border-color 0.15s; }
.mt-tab:hover { color:var(--text-dark); }
.mt-tab.active { color:var(--terra); border-bottom-color:var(--terra); }
.mt-tab-count { font-size:var(--fs-step); font-weight:600; margin-left:6px; opacity:0.7; }
.mt-tab-panel { display:none; }
.mt-tab-panel.active { display:block; }
.mt-search { display:flex; gap:10px; flex-wrap:wrap; margin-bottom:18px; }
.mt-search .db-input { flex:1; min-width:180px; }
.mt-teams-table .mt-th-services,
.mt-teams-table .mt-td-services { min-width:220px; max-width:320px; background:rgba(250,248,243,0.6); border-left:1px solid var(--cream-dark); border-right:1px solid var(--cream-dark); }
.mt-teams-table thead .mt-th-services { background:var(--cream); }
.mt-services-cell { display:flex; flex-wrap:wrap; gap:6px; padding:2px 0; }
.mt-services-empty { color:var(--text-light); }
.mt-td-rating { white-space:nowrap; }
.mt-td-actions { white-space:nowrap; text-align:right; }
.mt-row-actions { display:flex; flex-wrap:wrap; gap:6px; justify-content:flex-end; align-items:center; }
.mt-stars { display:inline-flex; align-items:center; gap:2px; }
.mt-star { color:var(--cream-dark); font-size:14px; line-height:1; }
.mt-star.filled { color:var(--gold); }
.mt-stars-sm .mt-star { font-size:13px; }
.mt-rating-num { margin-left:4px; font-size:var(--fs-step); font-weight:600; color:var(--text-dark); }
.mt-review-count { font-size:var(--fs-step); color:var(--text-light); }
</style>
@endpush

@section('content')

<div id="invitePanel" class="mt-invite-panel {{ session('show_invite_panel') || $errors->has('email') ? 'open' : '' }}" onclick="if(event.target===this)this.classList.remove('open')">
  <div class="mt-invite-sheet" onclick="event.stopPropagation()">
    <h3>Invite a cleaning crew</h3>
    <p class="db-form-hint" style="margin-bottom:16px">For crews you already work with on short-term turnovers. They register with this email and join your roster automatically.</p>
    <form method="POST" action="{{ route('landlord.cleaning-team.invite') }}" class="db-form" style="max-width:none">
      @csrf
      <div class="db-form-group">
        <label for="invite_email">Email</label>
        <input type="email" class="db-input" id="invite_email" name="email" value="{{ old('email') }}" required placeholder="crew@example.com">
        @error('email')<span class="db-form-error">{{ $message }}</span>@enderror
      </div>
      <button type="submit" class="db-form-submit">Create invite link</button>
    </form>
    @if(session('invite_created_url'))
      <div class="db-alert db-alert-success" style="margin-top:16px;flex-direction:column;align-items:stretch;text-align:left">
        <strong>Share this link</strong>
        <code style="display:block;margin-top:8px;word-break:break-all;font-size:13px;background:var(--cream);padding:10px;border-radius:8px">{{ session('invite_created_url') }}</code>
      </div>
    @endif
    <button type="button" class="db-btn db-btn-ghost" style="margin-top:14px;width:100%" onclick="document.getElementById('invitePanel').classList.remove('open')">Close</button>
  </div>
</div>

<div class="db-card">
  <nav class="mt-tabs" aria-label="Cleaning crews">
    <a href="{{ route('landlord.cleaning-team.index', ['tab' => 'roster']) }}"
       class="mt-tab {{ $activeTab === 'roster' ? 'active' : '' }}">
      Your crews
      @if($myTeams->isNotEmpty())<span class="mt-tab-count">{{ $myTeams->count() }}</span>@endif
    </a>
    <a href="{{ route('landlord.cleaning-team.index', ['tab' => 'discover']) }}"
       class="mt-tab {{ $activeTab === 'discover' ? 'active' : '' }}">
      Search &amp; add
      @if($browseTeams->isNotEmpty())<span class="mt-tab-count">{{ $browseTeams->count() }}</span>@endif
    </a>
  </nav>

  <div class="mt-tab-panel {{ $activeTab === 'roster' ? 'active' : '' }}" id="tab-roster">
    <div class="db-card-body">
      <p class="db-form-hint" style="margin-bottom:16px">Cleaning crews for short-term and turnover work. Booking jobs from the calendar is coming soon — for now, use your roster to find and contact trusted crews in your property cities.</p>
      @if($myTeams->isEmpty())
        <div class="db-empty" style="padding:32px 0">
          <div class="db-empty-icon">🧹</div>
          <h3>No crews on your roster yet</h3>
          <p>Switch to <strong>Search &amp; add</strong> to find crews in your property cities.</p>
          <a href="{{ route('landlord.cleaning-team.index', ['tab' => 'discover']) }}" class="db-btn db-btn-primary" style="margin-top:12px">Search &amp; add</a>
        </div>
      @else
        @include('dashboard.cleaning-team.partials.teams-table', [
          'teams' => $myTeams,
          'showRemove' => true,
          'showEngage' => false,
        ])
      @endif
    </div>
  </div>

  <div class="mt-tab-panel {{ $activeTab === 'discover' ? 'active' : '' }}" id="tab-discover">
    <div class="db-card-body">
      @if($propertyLocations->isEmpty())
        <div class="db-empty" style="padding:32px 0">
          <div class="db-empty-icon">🏠</div>
          <h3>Add a property first</h3>
          <p>We list cleaning crews in the same city and country as your properties.</p>
          <a href="{{ route('properties.index') }}" class="db-btn db-btn-primary" style="margin-top:8px">Go to properties</a>
        </div>
      @else
        <p class="db-form-hint" style="margin-bottom:14px">
          Cities from your portfolio:
          @foreach($propertyLocations as $loc)
            <strong>{{ $loc['city'] }}, {{ $loc['country_code'] }}</strong>@if(!$loop->last), @endif
          @endforeach
        </p>

        <form method="GET" action="{{ route('landlord.cleaning-team.index') }}" class="mt-search">
          <input type="hidden" name="tab" value="discover">
          <input type="search" name="q" class="db-input" placeholder="Search by name or city…" value="{{ $search }}">
          <select name="city" class="db-select" style="width:auto;min-width:140px">
            <option value="">All cities</option>
            @foreach($cityOptions as $city)
              <option value="{{ $city }}" {{ $cityFilter === $city ? 'selected' : '' }}>{{ $city }}</option>
            @endforeach
          </select>
          <button type="submit" class="db-btn db-btn-primary">Search</button>
          @if($search || $cityFilter)
            <a href="{{ route('landlord.cleaning-team.index', ['tab' => 'discover']) }}" class="db-btn db-btn-ghost">Clear</a>
          @endif
        </form>

        @if($browseTeams->isEmpty())
          <p class="db-form-hint" style="margin:24px 0 0">No crews match — you may already have them on your roster. Try clearing filters or use <strong>Invite crew</strong>.</p>
        @else
          @include('dashboard.cleaning-team.partials.teams-table', [
            'teams' => $browseTeams,
            'showRemove' => false,
            'showEngage' => true,
          ])
        @endif
      @endif
    </div>
  </div>
</div>
@endsection
