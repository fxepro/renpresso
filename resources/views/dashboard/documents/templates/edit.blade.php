@extends('dashboard.layout')
@section('page-title', 'Edit lease template')
@section('breadcrumb', '← Documents · Leases')
@section('content')
<div style="max-width:720px">
  <div class="db-card">
    <div class="db-card-header"><span class="db-card-title">Edit — {{ $leaseTemplate->name }}</span></div>
    <div class="db-card-body">
      <form method="POST" action="{{ route('lease-templates.update', $leaseTemplate) }}" class="db-form" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @if($errors->any())
          <div class="db-alert db-alert-error">{{ $errors->first() }}</div>
        @endif
        @include('dashboard.documents.templates._form', ['leaseTemplate' => $leaseTemplate])
        <div style="display:flex;gap:12px;margin-top:8px">
          <button type="submit" class="db-form-submit">Save changes</button>
          <a href="{{ route('documents.index', ['tab' => 'leases']) }}" class="db-btn db-btn-ghost">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
