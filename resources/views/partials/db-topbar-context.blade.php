@php
  $user = auth()->user();
  if (! $user) {
    return;
  }

  $topbarPersonName = null;
  $topbarContextLine = null;
  $topbarAccountUrl = null;

  if ($user->isMaintenance()) {
    $topbarTeam = $user->relationLoaded('ownedMaintenanceTeam')
      ? $user->ownedMaintenanceTeam
      : $user->ownedMaintenanceTeam()->first();
    $topbarPersonName = $user->maintenanceLegalOwnerName($topbarTeam);
    $topbarContextLine = $topbarTeam?->name;
    $topbarAccountUrl = route('maint.account');
  } elseif ($user->isCleaning()) {
    $topbarTeam = $user->relationLoaded('ownedCleaningTeam')
      ? $user->ownedCleaningTeam
      : $user->ownedCleaningTeam()->first();
    $topbarPersonName = $user->fullName();
    $topbarContextLine = $topbarTeam?->name;
    $topbarAccountUrl = route('clean.account');
  } elseif ($user->isLandlord()) {
    $topbarContextLine = trim((string) ($user->billing_company_name ?? '')) ?: null;
    $topbarAccountUrl = route('landlord.account');
  } elseif ($user->isTenant()) {
    $topbarLease = $user->primaryActiveLease();
    if ($topbarLease?->property) {
      $topbarContextLine = $topbarLease->property->name;
      if ($topbarLease->unit_label) {
        $topbarContextLine .= ' · '.$topbarLease->displayUnit();
      }
    }
    $topbarAccountUrl = route('tenant.account');
    $topbarPersonName = trim($user->formattedKycLegalName());
  }

  if ($topbarPersonName === null) {
    $topbarPersonName = trim($user->formattedKycLegalName());
  }

  $topbarInitial = strtoupper(substr($user->first_name ?: $user->email ?: 'U', 0, 1));
@endphp
@if($topbarPersonName !== '')
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
@endif
