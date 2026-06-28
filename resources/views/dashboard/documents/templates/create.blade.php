@extends('dashboard.layout')
@section('page-title', 'New lease template')
@section('breadcrumb', '← Documents · Leases')
@section('content')
<div style="max-width:720px">
  <div class="db-card">
    <div class="db-card-header"><span class="db-card-title">Create lease template</span></div>
    <div class="db-card-body">
      <form method="POST" action="{{ route('lease-templates.store') }}" class="db-form" enctype="multipart/form-data">
        @csrf
        @if($errors->any())
          <div class="db-alert db-alert-error">{{ $errors->first() }}</div>
        @endif
        @include('dashboard.documents.templates._form')
        <div style="display:flex;gap:12px;margin-top:8px">
          <button type="submit" class="db-form-submit">Save template</button>
          <a href="{{ route('documents.index', ['tab' => 'leases']) }}" class="db-btn db-btn-ghost">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
