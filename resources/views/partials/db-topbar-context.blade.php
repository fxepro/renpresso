@php
  $user = auth()->user();
  if (! $user) {
    return;
  }

  $topbarPersonName = null;
  $topbarContextLine = null;
  $topbarAccountUrl = null;

  if ($user->isAdmin()) {
    $topbarPersonName = trim($user->first_name.' '.$user->last_name) ?: $user->email;
    $topbarContextLine = 'Platform admin';
  } elseif ($user->isMaintenance()) {
    $topbarTeam = $user->relationLoaded('ownedMaintenanceTeam')
      ? $user->ownedMaintenanceTeam
      : $user->ownedMaintenanceTeam()->first();
    $topbarPersonName = $user->maintenanceLegalOwnerName($topbarTeam);
    $topbarContextLine = 'Maintenance';
    $topbarAccountUrl = route('maint.account');
  } elseif ($user->isCleaning()) {
    $topbarTeam = $user->relationLoaded('ownedCleaningTeam')
      ? $user->ownedCleaningTeam
      : $user->ownedCleaningTeam()->first();
    $topbarPersonName = $user->fullName();
    $topbarContextLine = 'Cleaning crew';
    $topbarAccountUrl = route('clean.account');
  } elseif ($user->isLandlord()) {
    $topbarContextLine = 'Landlord';
    $topbarAccountUrl = route('landlord.account');
  } elseif ($user->isTenant()) {
    $topbarContextLine = 'Tenant';
    $topbarAccountUrl = route('tenant.account');
    $topbarPersonName = trim($user->formattedKycLegalName());
  }

  if ($topbarPersonName === null) {
    $topbarPersonName = trim($user->formattedKycLegalName());
  }

  if ($topbarPersonName === '') {
    $topbarPersonName = trim($user->first_name.' '.$user->last_name) ?: $user->email;
  }

  $topbarInitial = strtoupper(substr($user->first_name ?: $user->email ?: 'U', 0, 1));
@endphp
@if($topbarPersonName !== '')
  <div class="db-topbar-user">
    @if($topbarAccountUrl)
      <a href="{{ $topbarAccountUrl }}" class="db-topbar-context" aria-label="Account">
    @else
      <div class="db-topbar-context">
    @endif
      <div class="db-topbar-context-text">
        <div class="db-topbar-context-name">{{ $topbarPersonName }}</div>
        @if($topbarContextLine)
          <div class="db-topbar-context-sub">{{ $topbarContextLine }}</div>
        @endif
      </div>
      <span class="db-topbar-context-avatar" aria-hidden="true">{{ $topbarInitial }}</span>
    @if($topbarAccountUrl)
      </a>
    @else
      </div>
    @endif
    <form method="POST" action="{{ route('auth.logout') }}" class="db-topbar-logout-form">
      @csrf
      <button type="submit" class="db-topbar-logout" aria-label="Sign out">
        <svg class="db-topbar-logout-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
          <polyline points="16 17 21 12 16 7"/>
          <line x1="21" y1="12" x2="9" y2="12"/>
        </svg>
      </button>
    </form>
  </div>
@endif
