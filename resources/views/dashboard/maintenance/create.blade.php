@extends(auth()->user()->isMaintenance() ? 'dashboard.maintenance-portal.layout' : 'dashboard.layout')
@section('page-title', 'New maintenance request')
@section('breadcrumb')
  <a href="{{ route('maintenance.index') }}" class="db-breadcrumb">← Requests</a>
@endsection

@section('content')
<div class="db-card" style="max-width:720px">
  <div class="db-card-header"><span class="db-card-title">Submit a request</span></div>
  <div class="db-card-body">
    <p class="db-form-hint" style="margin:0 0 20px">Describe the issue and add photos if you can. Your landlord will be notified.</p>
    <form method="POST" action="{{ route('maintenance.store') }}" class="db-form" enctype="multipart/form-data" style="max-width:none">
      @csrf
      @include('dashboard.maintenance.partials.form', ['categories' => $categories, 'lease' => $lease ?? null, 'leases' => $leases ?? null])
      <button type="submit" class="db-form-submit">Submit request</button>
    </form>
  </div>
</div>
@endsection
