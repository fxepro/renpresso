@extends('dashboard.layout')
@php
  $unitSeq = $unitSeq ?? null;
  $isBuildingView = $property->isMultiUnit();
  $openUnitPanel = request()->integer('unit');
@endphp
@section('page-title', $property->name)
@section('breadcrumb')
  @if($property->isMultiUnit())
    <a href="{{ route('properties.index', ['portfolio' => 'multi']) }}">← Multi-unit portfolio</a>
  @else
    <a href="{{ route('properties.index', ['portfolio' => 'single']) }}">← Properties</a>
  @endif
@endsection
@section('topbar-actions')
  @if($isBuildingView)
    <a href="{{ route('messages.property', $property) }}" class="db-btn db-btn-ghost">Building notices</a>
    <button type="button" class="db-btn db-btn-ghost" onclick="openPanel('{{ $property->id }}',{buildingOnly:true})">Edit building</button>
    @if(!$property->isAtLicensedUnitCapacity())
    <a href="{{ route('leases.create', $property) }}" class="db-btn db-btn-primary">+ Add unit lease</a>
    @endif
  @else
    @php $lease = ($activeLeases ?? collect())->first(); @endphp
    @if($lease)
      <a href="{{ route('messages.show', $lease) }}" class="db-btn db-btn-ghost">Message lessee</a>
    @endif
    @if(!$property->isAtLicensedUnitCapacity())
    <a href="{{ route('leases.create', $property) }}" class="db-btn db-btn-primary">+ Add lease</a>
    @endif
  @endif
@endsection
@section('content')

@push('styles')
<style>
.detail-rows {
  max-width: 36rem;
  border: 1px solid var(--cream-dark);
  border-radius: 10px;
  padding: 2px 18px;
  background: var(--white);
}
.detail-row {
  display: grid;
  grid-template-columns: minmax(9rem, 40%) 1fr;
  column-gap: 1rem;
  align-items: baseline;
  padding: 11px 0;
  border-bottom: 1px solid var(--cream-dark);
}
.detail-row:last-child { border-bottom: none; }
.detail-label { font-size: 13px; font-weight: 500; color: var(--text-light); text-align: left; }
.detail-value { font-size: 14px; font-weight: 500; color: var(--text-dark); text-align: left; line-height: 1.45; word-break: break-word; }
#panelOverlay { position:fixed;inset:0;background:rgba(13,31,53,0.35);z-index:300;opacity:0;pointer-events:none;transition:opacity 0.25s; }
#panelOverlay.open { opacity:1;pointer-events:all; }
#slidePanel { position:fixed;top:0;right:0;bottom:0;width:62%;background:var(--white);z-index:301;transform:translateX(100%);transition:transform 0.28s cubic-bezier(.4,0,.2,1);display:flex;flex-direction:column;box-shadow:-8px 0 40px rgba(0,0,0,0.12); }
#slidePanel.open { transform:translateX(0); }
.panel-header { display:flex;align-items:center;justify-content:space-between;padding:20px 28px;border-bottom:1px solid var(--cream-dark);flex-shrink:0; }
.panel-title { font-family:'Fraunces',serif;font-size:20px;font-weight:500;color:var(--text-dark); }
.panel-close { width:34px;height:34px;border-radius:8px;border:1px solid var(--cream-dark);background:transparent;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:18px;color:var(--text-light); }
.panel-tabs { display:flex;border-bottom:1px solid var(--cream-dark);padding:0 28px;flex-shrink:0; }
.panel-tab { padding:12px 0;margin-right:24px;font-size:14px;font-weight:500;color:var(--text-light);cursor:pointer;border-bottom:2px solid transparent; }
.panel-tab.active { color:var(--terra);border-bottom-color:var(--terra); }
.panel-body { flex:1;overflow-y:auto;padding:28px; }
.panel-footer { padding:16px 28px;border-top:1px solid var(--cream-dark);display:flex;align-items:center;justify-content:space-between;flex-shrink:0;background:var(--cream); }
.property-show-units tr.unit-row:hover { background:var(--cream); }
@@media (max-width:900px) { #slidePanel { width:min(100vw,100%); } }
</style>
@endpush

@php
  $flags       = $flags ?? [];
  $activeLeases = isset($activeLeases) ? $activeLeases : $property->leases->where('status','active')->sortBy(fn ($l) => sprintf('%06d|%s',(int)$l->unit_seq,strtolower($l->unit_label??'')));
@endphp

@include('dashboard.properties.partials.single-unit-detail', [
  'property'        => $property,
  'activeLeases'    => $activeLeases,
  'activeSubLeases' => $activeSubLeases ?? collect(),
  'unitSeq'         => null,
  'unitSlotsPayload'=> $unitSlotsPayload ?? null,
])

@include('dashboard.properties.partials.property-manage-panel', ['panelProperties' => collect([$property]), 'flags' => $flags])

@if($isBuildingView && $openUnitPanel >= 1)
<script>
document.addEventListener('DOMContentLoaded', function () {
  if (typeof openPanel === 'function') {
    openPanel(@json($property->id), { unitSeq: @json($openUnitPanel) });
  }
});
</script>
@endif

@endsection
