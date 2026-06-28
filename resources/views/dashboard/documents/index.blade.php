@extends('dashboard.layout')
@section('page-title', 'Documents')

@push('styles')
<style>
.doc-page-tabs { display:flex; gap:4px; background:var(--cream-dark); border-radius:8px; padding:3px; margin-bottom:20px; width:fit-content; max-width:100%; flex-wrap:wrap; }
.doc-page-tab { display:inline-block; padding:8px 18px; border-radius:6px; font-size:14px; font-weight:600; text-decoration:none; color:var(--text-mid); transition:all .15s; }
.doc-page-tab:hover { color:var(--text-dark); }
.doc-page-tab.active { background:var(--white); color:var(--text-dark); box-shadow:0 1px 3px rgba(0,0,0,.08); }
.doc-drive { background:var(--white); border:1px solid var(--cream-dark); border-radius:var(--radius); overflow:hidden; }
.doc-toolbar { display:flex; flex-wrap:wrap; gap:12px; align-items:center; padding:14px 18px; border-bottom:1px solid var(--cream-dark); background:var(--cream); }
.doc-toolbar form { display:flex; flex-wrap:wrap; gap:10px; align-items:center; flex:1; min-width:0; }
.doc-toolbar .doc-search { flex:1; min-width:180px; max-width:320px; }
.doc-toolbar select { min-width:130px; }
.doc-col-sort { color:var(--text-dark); text-decoration:none; font-weight:600; }
.doc-col-sort:hover { color:var(--terra); }
.doc-col-sort.active { color:var(--terra); }
.doc-row { transition:background .12s; }
.doc-row:hover { background:var(--cream); }
.doc-filename { font-weight:500; color:var(--text-dark); }
.doc-meta { font-size:var(--fs-step); color:var(--text-light); margin-top:2px; }
.doc-icon { font-size:22px; width:40px; text-align:center; flex-shrink:0; }
.doc-namecell { display:flex; align-items:flex-start; gap:12px; min-width:0; }
.doc-actions { white-space:nowrap; }
</style>
@endpush

@section('topbar-actions')
@if(($tab ?? 'documents') === 'leases')
  <a href="{{ route('lease-templates.create') }}" class="db-btn db-btn-primary">+ New template</a>
@endif
@endsection

@section('content')
@php $tab = $tab ?? 'documents'; @endphp

<div class="doc-page-tabs">
  <a href="{{ route('documents.index', ['tab' => 'leases']) }}" class="doc-page-tab {{ $tab === 'leases' ? 'active' : '' }}">Leases</a>
  <a href="{{ route('documents.index', ['tab' => 'documents']) }}" class="doc-page-tab {{ $tab === 'documents' ? 'active' : '' }}">Documents</a>
</div>

@if(session('success'))
  <div class="db-alert db-alert-success" style="margin-bottom:16px">{{ session('success') }}</div>
@endif

@if($tab === 'leases')
  @include('dashboard.documents.partials.leases-tab')
@else
  @include('dashboard.documents.partials.documents-tab')
@endif
@endsection
