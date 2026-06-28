@extends('admin.layout')
@section('title', $pageTitle)
@section('page-title', $pageTitle)
@section('breadcrumb', $breadcrumb)
@section('content')
<p class="admin-portal-note">{{ $description }}</p>
<div class="db-card">
  <div class="db-card-body">
    <div class="db-empty" style="padding:40px 20px">
      <div class="db-empty-icon">🚧</div>
      <h3>Coming soon</h3>
      <p>This admin screen is in the navigation so finance and ops views stay consistent. Implementation is tracked in the platform billing roadmap.</p>
      <a href="{{ route('admin.dashboard') }}" class="db-btn db-btn-primary" style="text-decoration:none;margin-top:8px">Back to dashboard</a>
    </div>
  </div>
</div>
@endsection
