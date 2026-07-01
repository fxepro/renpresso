<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'Cleaning crew') — {{ config('app.name') }}</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,300;0,9..144,500;0,9..144,700&family=Outfit:wght@300;400;500;600&display=swap" rel="stylesheet">
@vite(['resources/css/dashboard.css', 'resources/css/dashboard-portal.css'])
@stack('styles')
</head>
<body class="db-body db-theme-cleaning db-portal-layout">
@include('partials.rm-toast')
<aside class="db-sidebar" id="dbSidebar">
  <div class="db-logo">
    <a href="{{ route('clean.dashboard') }}" class="db-logo-link">Ren<span>presso</span></a>
    <div class="db-logo-sub">Cleaning crew</div>
  </div>
  <nav class="db-nav">
    <div class="db-nav-section">
      <span class="db-nav-label">Overview</span>
      <a href="{{ route('clean.dashboard') }}" class="db-nav-item {{ request()->routeIs('clean.dashboard') ? 'active' : '' }}">
        <span class="ni">📊</span><span class="db-nav-txt">Dashboard</span>
      </a>
    </div>
    <div class="db-nav-section">
      <span class="db-nav-label">Operations</span>
      <a href="{{ route('clean.team.edit') }}" class="db-nav-item {{ request()->routeIs('clean.team.*') ? 'active' : '' }}">
        <span class="ni">🧹</span><span class="db-nav-txt">Crew profile</span>
      </a>
      <a href="{{ route('clean.cities.index') }}" class="db-nav-item {{ request()->routeIs('clean.cities.*') ? 'active' : '' }}">
        <span class="ni">📍</span><span class="db-nav-txt">Operating cities</span>
      </a>
    </div>
  </nav>
</aside>
<div class="db-main">
  @include('partials.sections.app-page-header', ['showContext' => true])
  <div class="db-content">
    @if(session('success'))<div class="db-alert db-alert-success">✓ {{ session('success') }}</div>@endif
    @if(session('error'))<div class="db-alert db-alert-error">{{ session('error') }}</div>@endif
    @if($errors->any())<div class="db-alert db-alert-error">{{ $errors->first() }}</div>@endif
    @yield('content')
  </div>
</div>
@stack('scripts')
</body>
</html>
