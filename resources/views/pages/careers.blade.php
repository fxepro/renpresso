@extends('layouts.marketing', ['page' => 'careers'])

@section('title', 'Careers')
@section('meta_description', 'Join the Renpresso team — building rent collection software for independent landlords.')

@section('content')

@include('partials.sections.marketing-hero', [
  'label' => 'Careers',
  'title' => 'Help us build rent<br>collection for <em>every</em> country.',
  'lead' => 'We are a small remote team based in the US. Hiring will open as we approach launch.',
  'ctas' => [
    ['href' => url('/contact'), 'label' => 'Get in touch', 'class' => 'rm-btn rm-btn-primary btn-lg'],
    ['href' => url('/about'), 'label' => 'About us', 'class' => 'btn-outline-light'],
  ],
])

<section class="story">
  <div class="container container-sm">
    <div class="reveal u-text-center">
      <p class="section-label u-text-center">Open roles</p>
      <h2 class="section-title section-title--center">No open listings yet.</h2>
      <p class="section-sub u-text-center">Send a note through contact with your background — we read every message and keep strong introductions on file.</p>
    </div>
  </div>
</section>

@endsection
