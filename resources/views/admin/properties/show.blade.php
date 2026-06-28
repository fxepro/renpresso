@extends('admin.layout')
@section('title', $property->name)
@section('page-title', $property->name)
@section('breadcrumb', 'Properties')

@section('topbar-actions')
  <a href="{{ route('admin.properties') }}" class="db-btn db-btn-ghost" style="text-decoration:none">← All properties</a>
  @if($property->landlord)
    <a href="{{ route('admin.landlords.show', $property->landlord) }}" class="db-btn db-btn-ghost" style="text-decoration:none">Landlord profile</a>
  @endif
@endsection

@push('styles')
<style>
.prop-info-grid { display: grid; grid-template-columns: 1fr 1fr; }
.prop-info-row {
  display: flex; align-items: baseline; gap: 12px;
  padding: 10px 24px; border-bottom: 1px solid var(--cream-dark); font-size: 14px;
}
.prop-info-row:last-child { border-bottom: none; }
.prop-info-label {
  font-size: 11px; font-weight: 700; letter-spacing: .07em;
  text-transform: uppercase; color: var(--text-light);
  min-width: 120px; flex-shrink: 0;
}
.prop-info-val { color: var(--text-dark); font-weight: 500; }
.mode-chip {
  display: inline-flex; font-size: 11px; font-weight: 600;
  padding: 2px 8px; border-radius: 20px;
  background: var(--cream); color: var(--text-mid); border: 1px solid var(--cream-dark);
}
.mode-chip.multi { background: #e3f2fd; color: #1565c0; border-color: #90caf9; }
.lease-status-badge {
  display: inline-flex; font-size: 11px; font-weight: 600;
  padding: 2px 8px; border-radius: 20px;
}
.lease-status-badge.active     { background: #e8f5e9; color: #2e7d32; }
.lease-status-badge.expired    { background: #fff8e1; color: #f57f17; }
.lease-status-badge.terminated { background: #fce4ec; color: #b71c1c; }
.lease-status-badge.draft      { background: #f5f5f5; color: #9e9e9e; }
.unit-pill {
  display: inline-flex; align-items: center; gap: 5px;
  font-size: 12px; font-weight: 600; padding: 3px 10px;
  border-radius: 20px; background: var(--cream); border: 1px solid var(--cream-dark);
  color: var(--text-dark);
}
.unit-pill.occupied { background: #e8f5e9; border-color: #a5d6a7; color: #2e7d32; }
.unit-pill.vacant   { background: #f5f5f5; border-color: #e0e0e0; color: #9e9e9e; }
</style>
@endpush

@section('content')

@php
  $activeLeases  = $property->leases->where('status', 'active');
  $isMulti       = $property->occupancy_mode === 'multi';
  $slots         = $property->unit_slots_meta ?? [];
  $typeLabel     = match($property->type) {
    'apartment'  => 'Apartment',
    'house'      => 'House',
    'commercial' => 'Commercial',
    default      => ucfirst($property->type ?? '—'),
  };
@endphp

{{-- Stats row --}}
<div class="db-stats">
  <div class="db-stat">
    <div class="db-stat-label">Mode</div>
    <div class="db-stat-value" style="font-size:20px;margin-top:4px">
      <span class="mode-chip {{ $isMulti ? 'multi' : '' }}">
        {{ $isMulti ? 'Multi-unit' : 'Single-unit' }}
      </span>
    </div>
    <div class="db-stat-sub">{{ $typeLabel }}</div>
  </div>
  <div class="db-stat">
    <div class="db-stat-label">{{ $isMulti ? 'Licensed units' : 'Bedrooms' }}</div>
    <div class="db-stat-value">{{ $isMulti ? ($property->unit_capacity ?? '—') : ($property->bedrooms ?? '—') }}</div>
    <div class="db-stat-sub">{{ $isMulti ? 'Total capacity' : 'In this unit' }}</div>
  </div>
  <div class="db-stat {{ $activeLeases->count() > 0 ? 'green' : '' }}">
    <div class="db-stat-label">Active leases</div>
    <div class="db-stat-value">{{ $activeLeases->count() }}</div>
    <div class="db-stat-sub">{{ $property->leases->count() }} total across all time</div>
  </div>
  <div class="db-stat">
    <div class="db-stat-label">Country · Currency</div>
    <div class="db-stat-value" style="font-size:20px">
      {{ strtoupper($property->country_code ?? '—') }} · {{ strtoupper($property->currency_code ?? '—') }}
    </div>
    <div class="db-stat-sub">{{ ucfirst($property->rental_mode ?? 'long_term') }}</div>
  </div>
</div>

{{-- Two-column: Property info + Landlord --}}
<div style="display:grid;grid-template-columns:1.6fr 1fr;gap:18px;margin-bottom:20px">
  <div class="db-card" style="margin:0">
    <div class="db-card-header"><span class="db-card-title">Property details</span></div>
    <div class="db-card-body" style="padding:0">
      <div class="prop-info-grid">
        @foreach([
          ['Name',        $property->name],
          ['Address',     $property->address_line1.($property->address_line2 ? ', '.$property->address_line2 : '')],
          ['City',        $property->city ?? '—'],
          ['Postal code', $property->postal_code ?? '—'],
          ['Country',     strtoupper($property->country_code ?? '—')],
          ['Currency',    strtoupper($property->currency_code ?? '—')],
          ['Type',        $typeLabel],
          ['Rental mode', $property->rental_mode === 'short_term' ? 'Short-term' : 'Long-term'],
          ['Listing',     ucfirst($property->listing_visibility ?? 'private')],
          ['Sublet',      $property->sublet_allowed ? 'Allowed' : 'Not allowed'],
          ['Status',      ucfirst($property->status ?? '—')],
          ['Added',       $property->created_at?->format('d M Y') ?? '—'],
        ] as [$lbl, $val])
        <div class="prop-info-row">
          <span class="prop-info-label">{{ $lbl }}</span>
          <span class="prop-info-val">{{ $val }}</span>
        </div>
        @endforeach
      </div>
    </div>
  </div>

  <div class="db-card" style="margin:0">
    <div class="db-card-header"><span class="db-card-title">Landlord</span></div>
    <div class="db-card-body" style="padding:0">
      @if($property->landlord)
      @php
        $ll = $property->landlord;
        $accStatus = $ll->landlord_account_status ?? 'pending_activation';
        $accChip   = match($accStatus) {
          'active'                        => ['active',   'Active'],
          'pending', 'pending_activation' => ['pending',  'Pending'],
          default                         => ['inactive', 'Inactive'],
        };
      @endphp
      @foreach([
        ['Name',    $ll->first_name.' '.$ll->last_name],
        ['Email',   $ll->email],
        ['Country', strtoupper($ll->home_country ?? '—')],
        ['Currency', strtoupper($ll->home_currency ?? '—')],
        ['Account', null],
      ] as [$lbl, $val])
      <div class="prop-info-row">
        <span class="prop-info-label">{{ $lbl }}</span>
        @if($lbl === 'Account')
          <span class="lease-status-badge {{ $accChip[0] }}">{{ $accChip[1] }}</span>
        @else
          <span class="prop-info-val">{{ $val }}</span>
        @endif
      </div>
      @endforeach
      <div class="prop-info-row" style="border-top:1px solid var(--cream-dark);padding-top:14px">
        <a href="{{ route('admin.landlords.show', $ll) }}" class="db-btn db-btn-ghost" style="font-size:13px;text-decoration:none">
          Full landlord profile →
        </a>
      </div>
      @else
        <div class="prop-info-row"><span style="color:var(--text-light)">No landlord linked</span></div>
      @endif
    </div>
  </div>
</div>

{{-- Multi-unit: unit slot grid --}}
@if($isMulti && $property->unit_capacity)
<div class="db-card" style="margin-bottom:20px">
  <div class="db-card-header">
    <span class="db-card-title">Unit slots ({{ $property->unit_capacity }} licensed)</span>
  </div>
  <div class="db-card-body">
    @php
      $slotsBySeq = collect($slots)->keyBy('seq');
    @endphp
    <div style="display:flex;flex-wrap:wrap;gap:8px">
      @for($seq = 1; $seq <= $property->unit_capacity; $seq++)
        @php
          $slot       = $slotsBySeq->get($seq);
          $label      = $slot['label'] ?? "Unit {$seq}";
          $bedrooms   = $slot['bedrooms'] ?? null;
          $activeLease = $activeLeases->where('unit_seq', $seq)->first();
          $occupied    = (bool) $activeLease;
        @endphp
        <span class="unit-pill {{ $occupied ? 'occupied' : 'vacant' }}">
          {{ $label }}
          @if($bedrooms)
            <span style="font-weight:400;opacity:.7">{{ $bedrooms }}bd</span>
          @endif
          @if($occupied && $activeLease->tenant)
            · {{ $activeLease->tenant->first_name }}
          @elseif(!$occupied)
            <span style="font-weight:400;opacity:.6">vacant</span>
          @endif
        </span>
      @endfor
    </div>
    @if(collect($slots)->isEmpty())
      <p style="color:var(--text-light);font-size:13px;margin-top:8px">Unit metadata not yet configured — capacity reserved.</p>
    @endif
  </div>
</div>
@endif

{{-- Lease history --}}
<div class="db-card">
  <div class="db-card-header">
    <span class="db-card-title">Lease history ({{ $property->leases->count() }})</span>
  </div>
  <div class="db-card-body" style="padding:0">
    @if($property->leases->isEmpty())
      <div class="db-empty" style="padding:32px 20px">
        <div class="db-empty-icon">📋</div>
        <h3>No leases yet</h3>
        <p>This property has no lease history.</p>
      </div>
    @else
    <div class="db-table-wrap">
      <table class="db-table">
        <thead>
          <tr>
            <th>Tenant</th>
            @if($isMulti)<th style="text-align:center">Unit</th>@endif
            <th>Status</th>
            <th style="text-align:right">Rent</th>
            <th>Activated</th>
            <th>End date</th>
          </tr>
        </thead>
        <tbody>
          @foreach($property->leases as $lease)
          <tr>
            <td>
              @if($lease->tenant)
                <div style="font-weight:600;color:var(--text-dark)">{{ $lease->tenant->first_name }} {{ $lease->tenant->last_name }}</div>
                <div style="font-size:12px;color:var(--text-light)">{{ $lease->tenant->email }}</div>
              @else
                <span style="color:var(--text-light)">—</span>
              @endif
            </td>
            @if($isMulti)
            <td style="text-align:center;font-size:13px;color:var(--text-mid)">
              @php
                $slotLabel = $lease->unit_seq
                  ? ($slotsBySeq->get($lease->unit_seq)['label'] ?? "Unit {$lease->unit_seq}")
                  : '—';
              @endphp
              {{ $slotLabel }}
            </td>
            @endif
            <td>
              <span class="lease-status-badge {{ $lease->status }}">{{ ucfirst($lease->status) }}</span>
            </td>
            <td style="text-align:right;font-weight:600;font-size:13px">
              @if($lease->rent_minor_units)
                {{ strtoupper($property->currency_code) }} {{ number_format($lease->rent_minor_units / 100, 2) }}
              @else
                <span style="color:var(--text-light)">—</span>
              @endif
            </td>
            <td style="font-size:13px;color:var(--text-mid);white-space:nowrap">
              {{ $lease->activated_at ? \Carbon\Carbon::parse($lease->activated_at)->format('d M Y') : '—' }}
            </td>
            <td style="font-size:13px;color:var(--text-mid);white-space:nowrap">
              {{ $lease->end_date ? \Carbon\Carbon::parse($lease->end_date)->format('d M Y') : 'Open-ended' }}
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    @endif
  </div>
</div>

@endsection
