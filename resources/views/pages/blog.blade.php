@extends('layouts.marketing', ['page' => 'blog'])

@section('title', 'Blog')
@section('meta_description', 'Stories and updates from the Renpresso team — launching soon.')

@section('content')

@include('partials.sections.marketing-hero', [
  'label' => 'Blog',
  'title' => 'Stories from the<br><em>Renpresso</em> team.',
  'lead' => 'Product updates, landlord guides, and international property insights — publishing soon.',
  'ctas' => [
    ['href' => url('/waitlist'), 'label' => 'Join the waitlist', 'class' => 'rm-btn rm-btn-primary btn-lg'],
    ['href' => url('/contact'), 'label' => 'Contact us', 'class' => 'btn-outline-light'],
  ],
])

<section class="story">
  <div class="container container-sm">
    <div class="reveal u-text-center">
      <p class="section-label u-text-center">Coming soon</p>
      <h2 class="section-title section-title--center">We're writing the first posts now.</h2>
      <p class="section-sub u-text-center">Join the waitlist to get launch updates, or contact us if you are a journalist covering proptech.</p>
    </div>
  </div>
</section>

@endsection
