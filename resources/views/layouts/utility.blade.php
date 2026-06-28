{{--
  Utility shell — legal and other narrow prose pages.
  Uses MarketingShell chrome (header + footer from config).
--}}
@extends('layouts.marketing', ['page' => $page ?? ''])

@section('page-header')
  @include('partials.sections.page-header', [
    'eyebrow' => $utilityEyebrow ?? 'Legal',
    'title'   => trim($__env->yieldContent('heading')),
    'meta'    => trim($__env->yieldContent('meta')) ?: null,
    'tone'    => $utilityTone ?? 'dark',
  ])
@endsection

@section('body_class', 'container-narrow utility-prose')

@section('content')
  @yield('utility')
@endsection
