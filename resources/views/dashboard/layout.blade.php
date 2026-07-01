<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect fill='%23C4622D' width='32' height='32' rx='6'/%3E%3Ctext x='16' y='22' text-anchor='middle' fill='white' font-size='16' font-family='system-ui'%3ER%3C/text%3E%3C/svg%3E">
<title>@yield('title', 'Dashboard') — Renpresso</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,300;0,9..144,500;0,9..144,700&family=Outfit:wght@300;400;500;600&display=swap" rel="stylesheet">
@vite(['resources/css/dashboard.css', 'resources/js/dashboard.js'])
@stack('styles')
</head>
@php
  $dbTheme = auth()->user()->isTenant() ? 'tenant' : (auth()->user()->isMaintenance() ? 'maintenance' : 'landlord');
@endphp
<body class="db-body db-theme-{{ $dbTheme }}" data-sidebar-key="rm_db_sidebar_collapsed">

@include('partials.rm-toast')

<!-- Sidebar -->
<aside class="db-sidebar" id="dbSidebar">
  <div class="db-logo">
    <div class="db-logo-inner">
      <a class="db-logo-link" href="{{ url('/') }}">Ren<span>presso</span></a>
      <button type="button" class="db-sidebar-toggle" id="dbSidebarToggle" aria-expanded="true" aria-controls="dbSidebar" title="Collapse sidebar">
        <span class="sr-only">Toggle sidebar</span>
        <svg class="icon-panel-close" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
          <rect x="3" y="4" width="14" height="16" rx="2"/>
          <path d="M21 8v8"/>
        </svg>
        <svg class="icon-panel-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
          <rect x="7" y="4" width="14" height="16" rx="2"/>
          <path d="M3 8v8"/>
        </svg>
      </button>
    </div>
  </div>
  <nav class="db-nav">
    @if(auth()->user()->isLandlord())
    <div class="db-nav-section">
      <span class="db-nav-label">Overview</span>
      <a href="{{ route('dashboard') }}" class="db-nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
        <span class="ni">📊</span><span class="db-nav-txt">Dashboard</span>
      </a>
    </div>
    <div class="db-nav-section">
      <span class="db-nav-label">Portfolio</span>
      <a href="{{ route('properties.index') }}" class="db-nav-item {{ request()->routeIs('properties.*') ? 'active' : '' }}">
        <span class="ni">🏠</span><span class="db-nav-txt">Properties</span>
      </a>
      <a href="{{ route('leases.index') }}" class="db-nav-item {{ request()->routeIs('leases.*') ? 'active' : '' }}">
        <span class="ni">📋</span><span class="db-nav-txt">Leases</span>
      </a>
      <a href="{{ route('tenants.index') }}" class="db-nav-item {{ request()->routeIs('tenants.*') ? 'active' : '' }}">
        <span class="ni">👥</span><span class="db-nav-txt">Tenants</span>
      </a>
      @php
        $pendingApplications = \App\Models\Application::query()
          ->whereHas('property', fn ($q) => $q->where('landlord_id', auth()->id()))
          ->whereIn('status', ['pending', 'reviewing'])
          ->count();
        $openBackgroundChecks = \App\Models\BackgroundCheck::query()
          ->whereHas('property', fn ($q) => $q->where('landlord_id', auth()->id()))
          ->whereIn('status', ['requested', 'pending', 'manual_review'])
          ->count();
      @endphp
      <a href="{{ route('applications.index') }}" class="db-nav-item {{ request()->routeIs('applications.index') ? 'active' : '' }}">
        <span class="ni">📋</span><span class="db-nav-txt">Applications</span>
        @if($pendingApplications > 0)<span class="db-nav-badge">{{ $pendingApplications }}</span>@endif
      </a>
      <a href="{{ route('background-checks.index') }}" class="db-nav-item {{ request()->routeIs('background-checks.index') ? 'active' : '' }}">
        <span class="ni">🔍</span><span class="db-nav-txt">Background checks</span>
        @if($openBackgroundChecks > 0)<span class="db-nav-badge">{{ $openBackgroundChecks }}</span>@endif
      </a>
      <a href="{{ route('documents.index') }}" class="db-nav-item {{ request()->routeIs('documents.*') || request()->routeIs('lease-templates.*') ? 'active' : '' }}">
        <span class="ni">📁</span><span class="db-nav-txt">Documents</span>
      </a>
    </div>
    <div class="db-nav-section">
      <span class="db-nav-label">Finance</span>
      @php
        $awaitingInvoiceCount = \App\Models\MaintenanceInvoice::query()
          ->where('landlord_id', auth()->id())
          ->visibleToLandlord()
          ->awaitingLandlordApproval()
          ->get()
          ->filter(fn ($inv) => $inv->amountDueMinor() > 0)
          ->count();
      @endphp
      <a href="{{ route('landlord.invoices.index') }}" class="db-nav-item {{ request()->routeIs('landlord.invoices.*') ? 'active' : '' }}">
        <span class="ni">🧾</span><span class="db-nav-txt">Invoices</span>
        @if($awaitingInvoiceCount > 0)<span class="db-nav-badge">{{ $awaitingInvoiceCount }}</span>@endif
      </a>
      <a href="{{ route('payments.index') }}" class="db-nav-item {{ request()->routeIs('payments.*') ? 'active' : '' }}">
        <span class="ni">💳</span><span class="db-nav-txt">Payments</span>
      </a>
      <a href="{{ route('fx-ledger.index') }}" class="db-nav-item {{ request()->routeIs('fx-ledger.*') ? 'active' : '' }}">
        <span class="ni">💱</span><span class="db-nav-txt">FX Ledger</span>
      </a>
      <a href="{{ route('tax-export.index') }}" class="db-nav-item {{ request()->routeIs('tax-export.*') ? 'active' : '' }}">
        <span class="ni">📤</span><span class="db-nav-txt">Tax Export</span>
      </a>
      <a href="{{ route('billing.index') }}" class="db-nav-item {{ request()->routeIs('billing.*') ? 'active' : '' }}">
        <span class="ni">🧮</span><span class="db-nav-txt">Billing</span>
      </a>
    </div>
    <div class="db-nav-section">
      <span class="db-nav-label">Operations</span>
      <a href="{{ route('maintenance.index') }}" class="db-nav-item {{ request()->routeIs('maintenance.*') ? 'active' : '' }}">
        <span class="ni">🔧</span><span class="db-nav-txt">Maintenance requests</span>
        @php $openMaintenance = auth()->user()->properties()->with('leases.maintenanceRequests')->get()->flatMap(fn($p) => $p->leases)->flatMap(fn($l) => $l->maintenanceRequests)->where('status','submitted')->count(); @endphp
        @if($openMaintenance > 0)<span class="db-nav-badge">{{ $openMaintenance }}</span>@endif
      </a>
      <a href="{{ route('landlord.maintenance-team.index') }}" class="db-nav-item {{ request()->routeIs('landlord.maintenance-team.*') ? 'active' : '' }}">
        <span class="ni">🧰</span><span class="db-nav-txt">Maintenance team</span>
      </a>
      <a href="{{ route('landlord.cleaning-team.index') }}" class="db-nav-item {{ request()->routeIs('landlord.cleaning-team.*') ? 'active' : '' }}">
        <span class="ni">🧹</span><span class="db-nav-txt">Cleaning crews</span>
      </a>
    </div>
    <div class="db-nav-section">
      <span class="db-nav-label">Communications</span>
      <a href="{{ route('messages.index') }}" class="db-nav-item {{ request()->routeIs('messages.*') ? 'active' : '' }}">
        <span class="ni">💬</span><span class="db-nav-txt">Messages</span>
      </a>
      <a href="{{ route('landlord.communication.index') }}" class="db-nav-item {{ request()->routeIs('landlord.communication.*') ? 'active' : '' }}">
        <span class="ni">📧</span><span class="db-nav-txt">Email templates</span>
      </a>
    </div>
    @include('dashboard.partials.nav-deals')
    <div class="db-nav-section">
      <span class="db-nav-label">Help</span>
      <a href="{{ route('help.videos') }}" class="db-nav-item {{ request()->routeIs('help.videos') ? 'active' : '' }}">
        <span class="ni">▶</span><span class="db-nav-txt">Videos</span>
      </a>
      <a href="{{ route('help.collateral') }}" class="db-nav-item {{ request()->routeIs('help.collateral') ? 'active' : '' }}">
        <span class="ni">📄</span><span class="db-nav-txt">Collateral</span>
      </a>
      <a href="{{ route('help.helpline') }}" class="db-nav-item {{ request()->routeIs('help.helpline') ? 'active' : '' }}">
        <span class="ni">📞</span><span class="db-nav-txt">Helpline</span>
      </a>
    </div>
    @elseif(auth()->user()->isMaintenance())
    <div class="db-nav-section">
      <a href="{{ route('maint.dashboard') }}" class="db-nav-item active">
        <span class="ni">🔧</span><span class="db-nav-txt">Open maintenance portal</span>
      </a>
    </div>
    @elseif(auth()->user()->isCleaning())
    <div class="db-nav-section">
      <a href="{{ route('clean.dashboard') }}" class="db-nav-item active">
        <span class="ni">🧹</span><span class="db-nav-txt">Open cleaning portal</span>
      </a>
    </div>
    @elseif(auth()->user()->isTenant())
    <div class="db-nav-section">
      <span class="db-nav-label">Overview</span>
      <a href="{{ route('dashboard') }}" class="db-nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
        <span class="ni">📊</span><span class="db-nav-txt">Dashboard</span>
      </a>
    </div>
    <div class="db-nav-section">
      <span class="db-nav-label">Home</span>
      <a href="{{ route('tenant.home') }}" class="db-nav-item {{ request()->routeIs('tenant.home') ? 'active' : '' }}">
        <span class="ni">🏠</span><span class="db-nav-txt">Details &amp; lease</span>
      </a>
    </div>
    <div class="db-nav-section">
      <span class="db-nav-label">Operations</span>
      <a href="{{ route('maintenance.index') }}" class="db-nav-item {{ request()->routeIs('maintenance.*') ? 'active' : '' }}">
        <span class="ni">🔧</span><span class="db-nav-txt">Maintenance</span>
      </a>
    </div>
    <div class="db-nav-section">
      <span class="db-nav-label">Finance</span>
      <a href="{{ route('tenant.payments') }}" class="db-nav-item {{ request()->routeIs('tenant.payments*') ? 'active' : '' }}">
        <span class="ni">💳</span><span class="db-nav-txt">Payments</span>
      </a>
      <a href="{{ route('tenant.account-ledger') }}" class="db-nav-item {{ request()->routeIs('tenant.account-ledger') ? 'active' : '' }}">
        <span class="ni">📒</span><span class="db-nav-txt">Account ledger</span>
      </a>
    </div>
    <div class="db-nav-section">
      <span class="db-nav-label">Communications</span>
      <a href="{{ route('messages.index') }}" class="db-nav-item {{ request()->routeIs('messages.*') ? 'active' : '' }}">
        <span class="ni">💬</span><span class="db-nav-txt">Messages</span>
      </a>
    </div>
    @include('dashboard.partials.nav-deals')
    <div class="db-nav-section">
      <span class="db-nav-label">Help</span>
      <a href="{{ route('help.videos') }}" class="db-nav-item {{ request()->routeIs('help.videos') ? 'active' : '' }}">
        <span class="ni">▶</span><span class="db-nav-txt">Videos</span>
      </a>
      <a href="{{ route('help.collateral') }}" class="db-nav-item {{ request()->routeIs('help.collateral') ? 'active' : '' }}">
        <span class="ni">📄</span><span class="db-nav-txt">Collateral</span>
      </a>
      <a href="{{ route('help.helpline') }}" class="db-nav-item {{ request()->routeIs('help.helpline') ? 'active' : '' }}">
        <span class="ni">📞</span><span class="db-nav-txt">Helpline</span>
      </a>
    </div>
    @endif
  </nav>
</aside>

<!-- Main -->
<div class="db-main">
  @include('partials.sections.app-page-header', ['showContext' => true])
  <div class="db-content {{ request()->routeIs('landlord.account', 'tenant.account') ? 'db-content--account' : '' }}">
    @if(session('success'))
      <div class="db-alert db-alert-success">✓ {{ session('success') }}</div>
    @endif
    @if(session('error'))
      <div class="db-alert db-alert-error">✗ {{ session('error') }}</div>
    @endif
    @yield('content')
  </div>
</div>

{{-- Fixed slide panels must live outside overflow:auto/hidden ancestors or they are clipped / unusable --}}
@stack('dashboard-overlays')

@stack('scripts')
</body>
</html>
