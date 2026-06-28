<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', config('site.tagline')) — {{ config('site.name') }}</title>
<meta name="description" content="@yield('meta_description', config('site.description'))">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,300;0,9..144,500;0,9..144,700&family=Outfit:wght@300;400;500;600&display=swap" rel="stylesheet">
@vite(['resources/css/app.css', 'resources/js/app.js'])
@stack('styles')
</head>
<body @class([
  'auth-page' => $authPage ?? false,
])>

@include('partials.rm-toast')
@unless($hideHeader ?? false)
  @include('partials.layout.site-header', ['page' => $page ?? ''])
@endunless

<main @class([
  'page-main',
  'page-main--auth' => $authPage ?? false,
])>
  @hasSection('page-header')
    @yield('page-header')
  @endif

  <div class="page-body @yield('body_class')">
    @yield('content')
  </div>
</main>

@unless($hideFooter ?? false)
  @include('partials.layout.site-footer', ['page' => $page ?? ''])
@endunless

@unless($authPage ?? false)
  @include('partials.layout.cookie-banner')
@endunless

@stack('scripts')

</body>
</html>
