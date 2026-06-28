@extends('layouts.marketing', ['page' => 'waitlist'])

@section('title', 'Join the waitlist')
@section('meta_description', 'Join the Renpresso waitlist for early access. Launching first in the United States.')

@section('content')

@include('partials.sections.waitlist-form', [
  'waitlistSuccess' => session('waitlist_success'),
])

@endsection
