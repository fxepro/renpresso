@extends('layouts.onboarding', ['page' => 'waitlist'])

@section('title', 'Join the waitlist')
@section('meta_description', 'Join the Renpresso waitlist for early access to rent collection and portfolio management.')

@section('content')

@include('partials.sections.waitlist-form', [
  'waitlistSuccess' => session('waitlist_success'),
])

@endsection
