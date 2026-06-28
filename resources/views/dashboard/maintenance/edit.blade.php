@extends(auth()->user()->isMaintenance() ? 'dashboard.maintenance-portal.layout' : 'dashboard.layout')
@section('page-title', 'Edit request')
@section('breadcrumb')
  <a href="{{ route('maintenance.show', $maintenanceRequest) }}" class="db-breadcrumb">← {{ $maintenanceRequest->title }}</a>
@endsection

@section('content')
<div class="db-card" style="max-width:720px">
  <div class="db-card-header"><span class="db-card-title">Edit request</span></div>
  <div class="db-card-body">
    @if($maintenanceRequest->status !== 'submitted')
      <div class="db-alert db-alert-error" style="margin-bottom:16px">Only requests still in <strong>Submitted</strong> status can be edited by tenants.</div>
    @endif
    <form method="POST" action="{{ route('maintenance.details.update', $maintenanceRequest) }}" class="db-form" enctype="multipart/form-data" style="max-width:none">
      @csrf
      @method('PUT')
      @include('dashboard.maintenance.partials.form', [
        'categories' => $categories,
        'maintenanceRequest' => $maintenanceRequest,
      ])
      <div style="display:flex;flex-wrap:wrap;gap:12px;margin-top:8px">
        <button type="submit" class="db-form-submit">Save changes</button>
        <a href="{{ route('maintenance.show', $maintenanceRequest) }}" class="db-btn db-btn-ghost" style="text-decoration:none">Cancel</a>
      </div>
    </form>
  </div>
</div>
@endsection
