@extends('layouts.onboarding', ['page' => 'register'])

@section('title', 'Create your account')
@section('meta_description', 'Create your Renpresso landlord account and tell us about your portfolio.')

@section('content')

@include('partials.sections.signup-form', [
  'signupSuccess' => session('signup_success'),
])

@endsection
