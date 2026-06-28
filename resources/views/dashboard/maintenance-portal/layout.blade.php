<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'Maintenance') — {{ config('app.name') }}</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,300;0,9..144,500;0,9..144,700&family=Outfit:wght@300;400;500;600&display=swap" rel="stylesheet">
@vite(['resources/css/dashboard.css', 'resources/css/dashboard-portal.css'])
@stack('styles')
</head>
<body class="db-body db-theme-maintenance db-portal-layout">
@include('partials.rm-toast')
@php
  $maintTeam = auth()->user()->ownedMaintenanceTeam;
  $maintOpenJobs = 0;
  $maintAccountAttention = false;
  if ($maintTeam) {
    $maintOpenJobs = \App\Models\MaintenanceRequest::where('maintenance_team_id', $maintTeam->id)
      ->whereIn('status', ['submitted', 'acknowledged', 'in_progress'])->count();
    $maintTeam->loadMissing('documents');
    $maintAccountAttention = ! $maintTeam->complianceSummary()['complete'];
  }
  $user = auth()->user();
  if (! $user->maintenanceDirectorIdentityVerified() && $user->maintenanceDirectorIdentityStatus() !== 'pending') {
    $maintAccountAttention = true;
  }
@endphp
<aside class="db-sidebar" id="dbSidebar">
  <div class="db-logo">
    <a href="{{ route('maint.dashboard') }}" class="db-logo-link">Ren<span>presso</span></a>
    <div class="db-logo-sub">Maintenance</div>
  </div>
  <nav class="db-nav">
    <div class="db-nav-section">
      <span class="db-nav-label">Overview</span>
      <a href="{{ route('maint.dashboard') }}" class="db-nav-item {{ request()->routeIs('maint.dashboard') ? 'active' : '' }}">
        <span class="ni">📊</span><span class="db-nav-txt">Dashboard</span>
      </a>
    </div>
    <div class="db-nav-section">
      <span class="db-nav-label">Maintenance</span>
      <a href="{{ route('maintenance.index') }}" class="db-nav-item {{ request()->routeIs('maintenance.*') ? 'active' : '' }}">
        <span class="ni">🔧</span><span class="db-nav-txt">Requests &amp; status</span>
        @if($maintOpenJobs > 0)<span class="db-nav-badge">{{ $maintOpenJobs }}</span>@endif
      </a>
    </div>
    <div class="db-nav-section db-nav-section--tight">
      <span class="db-nav-label">Payments</span>
      <a href="{{ route('maint.payments.invoices') }}" class="db-nav-item db-nav-item--sub {{ request()->routeIs('maint.payments.invoices*') ? 'active' : '' }}">
        <span class="ni">📄</span><span class="db-nav-txt">Invoices</span>
      </a>
      <a href="{{ route('maint.payments') }}" class="db-nav-item db-nav-item--sub {{ request()->routeIs('maint.payments') || request()->routeIs('maint.payments.destroy') ? 'active' : '' }}">
        <span class="ni">💰</span><span class="db-nav-txt">Payments</span>
      </a>
    </div>
  </nav>
  <div class="db-sidebar-footer">
    <a href="{{ route('maint.account') }}" class="db-user db-user-link {{ request()->routeIs('maint.account*') ? 'active' : '' }}" aria-label="Account — profile, director identity, and compliance documents">
      <div class="db-avatar">{{ strtoupper(substr(auth()->user()->first_name ?? 'U', 0, 1)) }}</div>
      <div class="db-user-text" style="flex:1;min-width:0">
        <div class="db-user-name">{{ auth()->user()->fullName() }}</div>
        <div class="db-user-role">Account</div>
      </div>
      <div class="db-user-badges">
        @if($maintAccountAttention)
          <span class="db-nav-badge" title="Finish account documents">!</span>
        @elseif(auth()->user()->maintenanceDirectorIdentityVerified())
          <span class="db-nav-badge green" title="Director identity verified">✓</span>
        @endif
      </div>
    </a>
    <form method="POST" action="{{ route('auth.logout') }}">@csrf
      <button type="submit" class="db-logout"><span>↩</span> Sign out</button>
    </form>
  </div>
</aside>
<div class="db-main">
  @include('partials.sections.app-page-header', ['showContext' => true])
  <div class="db-content {{ request()->routeIs('maint.account*') ? 'db-content--account' : '' }}">
    @if(session('success'))<div class="db-alert db-alert-success">✓ {{ session('success') }}</div>@endif
    @if(session('error'))<div class="db-alert db-alert-error">{{ session('error') }}</div>@endif
    @if($errors->any())<div class="db-alert db-alert-error">{{ $errors->first() }}</div>@endif
    @yield('content')
  </div>
</div>
@stack('scripts')
</body>
</html>
