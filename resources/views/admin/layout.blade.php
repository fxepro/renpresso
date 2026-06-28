<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect fill='%234a6b8a' width='32' height='32' rx='6'/%3E%3Ctext x='16' y='22' text-anchor='middle' fill='white' font-size='14' font-family='system-ui'%3EA%3C/text%3E%3C/svg%3E">
<title>@yield('title', 'Admin') — Renpresso</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,300;0,9..144,500;0,9..144,700&family=Outfit:wght@300;400;500;600&display=swap" rel="stylesheet">
@vite(['resources/css/dashboard.css', 'resources/css/dashboard-admin.css', 'resources/js/dashboard.js', 'resources/js/dashboard-admin.js'])
@stack('styles')
</head>
<body class="db-body db-theme-admin" data-sidebar-key="rm_admin_sidebar_collapsed">

@include('partials.rm-toast')

<aside class="db-sidebar" id="dbSidebar">
  <div class="db-logo">
    <div class="db-logo-inner">
      <a class="db-logo-link" href="{{ route('admin.dashboard') }}">Ren<span>presso</span> <span style="font-size:13px;font-weight:500;opacity:0.85">Admin</span></a>
      <button type="button" class="db-sidebar-toggle" id="dbSidebarToggle" aria-expanded="true" aria-controls="dbSidebar" title="Collapse sidebar">
        <span class="sr-only">Toggle sidebar</span>
        <svg class="icon-panel-close" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
          <rect x="3" y="4" width="14" height="16" rx="2"/><path d="M21 8v8"/>
        </svg>
        <svg class="icon-panel-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
          <rect x="7" y="4" width="14" height="16" rx="2"/><path d="M3 8v8"/>
        </svg>
      </button>
    </div>
  </div>
  @include('admin.partials.sidebar')
  <div class="db-sidebar-footer">
    <div class="db-user">
      <div class="db-avatar">{{ strtoupper(substr(auth()->user()->first_name ?? 'A', 0, 1)) }}</div>
      <div class="db-user-text">
        <div class="db-user-name">{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</div>
        <div class="db-user-role">Platform admin</div>
      </div>
    </div>
    <form method="POST" action="{{ route('auth.logout') }}">
      @csrf
      <button type="submit" class="db-logout"><span class="db-logout-ico" aria-hidden="true">↩</span><span class="db-logout-txt">Sign out</span></button>
    </form>
  </div>
</aside>

<div class="db-main">
  @include('partials.sections.app-page-header', ['kicker' => 'Internal', 'showMarketingLink' => true])
  <div class="db-content">
    @if(session('success'))
      <div class="db-alert db-alert-success">✓ {{ session('success') }}</div>
    @endif
    @if(session('error'))
      <div class="db-alert db-alert-error">✗ {{ session('error') }}</div>
    @endif
    @yield('content')
  </div>
</div>

@stack('scripts')
</body>
</html>
