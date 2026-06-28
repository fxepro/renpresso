@extends('dashboard.layout')
@section('page-title', 'Messages')

@push('styles')
<style>
.msg-tabs { display:flex; gap:4px; padding:0 24px; border-bottom:1px solid var(--cream-dark); background:var(--cream); }
.msg-tab { padding:14px 18px; font-family:'Outfit',sans-serif; font-size:var(--fs-body); font-weight:500; color:var(--text-light); text-decoration:none; border-bottom:2px solid transparent; margin-bottom:-1px; transition:color 0.15s, border-color 0.15s; }
.msg-tab:hover { color:var(--text-dark); }
.msg-tab.active { color:var(--terra); border-bottom-color:var(--terra); }
.msg-tab-panel { display:none; }
.msg-tab-panel.active { display:block; }
</style>
@endpush

@section('content')

<div class="db-card" style="margin-bottom:20px">
  <div class="db-card-body">
    <p style="font-size:var(--fs-body);color:var(--text-mid);line-height:1.55;max-width:820px;margin:0">
      @if($user->isLandlord())
        <strong>Property</strong> — building notices to all tenants (one email per person).
        <strong>Lease</strong> — per-unit threads when tenants reply or you message one tenant in-app.
      @else
        <strong>Property</strong> — building notices for where you rent.
        <strong>Lease</strong> — your unit thread with your landlord.
      @endif
    </p>
  </div>
</div>

@php
  $activeTab = $activeTab ?? request('tab', 'property');
  $propertyEmpty = ($properties ?? collect())->isEmpty();
  $leaseEmpty = ($leases ?? collect())->isEmpty();
  $allEmpty = $propertyEmpty && $leaseEmpty;
@endphp

@if($allEmpty)
  <div class="db-empty" style="min-height:45vh">
    <div class="db-empty-icon">💬</div>
    <h3>No messaging yet</h3>
    <p>
      @if($user->isLandlord())
        Add active or pending leases on a property to message tenants.
      @else
        You need an active or pending lease to message your landlord.
      @endif
    </p>
  </div>
@else
<div class="db-card">
  <nav class="msg-tabs" aria-label="Message views">
    <a href="{{ route('messages.index', ['tab' => 'property']) }}"
       class="msg-tab {{ $activeTab === 'property' ? 'active' : '' }}">
      Property
    </a>
    <a href="{{ route('messages.index', ['tab' => 'lease']) }}"
       class="msg-tab {{ $activeTab === 'lease' ? 'active' : '' }}">
      Lease
    </a>
  </nav>

  <div class="msg-tab-panel {{ $activeTab === 'property' ? 'active' : '' }}" id="tab-property">
    <div class="db-card-body">
      @if($propertyEmpty)
        <div class="db-empty" style="padding:32px 0">
          <div class="db-empty-icon">🏢</div>
          <h3>No properties yet</h3>
          <p>
            @if($user->isLandlord())
              Properties with active leases appear here for building notices.
            @else
              No building notices yet.
            @endif
          </p>
        </div>
      @else
        <div class="db-table-wrap">
          <table class="db-table">
            <thead>
              <tr>
                <th>Property</th>
                @if($user->isLandlord())
                  <th>Units</th>
                @else
                  <th>Landlord</th>
                @endif
                <th>Last building notice</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              @foreach($properties as $property)
                @php
                  $tenantLease = $user->isTenant()
                    ? ($leases ?? collect())->firstWhere('property_id', $property->id)
                    : null;
                @endphp
                <tr>
                  <td>
                    <div class="db-flag-name">
                      <span class="db-flag">{{ config('countries.'.$property->country_code.'.flag','🏠') }}</span>
                      <div>
                        <div class="db-name">{{ $property->name }}</div>
                        <div class="db-sub">{{ $property->city }}, {{ $property->country_code }}</div>
                      </div>
                    </div>
                  </td>
                  <td>
                    @if($user->isLandlord())
                      {{ $property->unit_count ?? '—' }}
                    @else
                      {{ $property->landlord?->fullName() ?? '—' }}
                    @endif
                  </td>
                  <td>
                    @if($property->last_broadcast_at)
                      {{ \Carbon\Carbon::parse($property->last_broadcast_at)->format('d M Y, H:i') }}
                    @else
                      <span class="db-sub">None yet</span>
                    @endif
                  </td>
                  <td style="text-align:right">
                    @if($user->isLandlord())
                      <a href="{{ route('messages.property', $property) }}" class="db-table-link">Open →</a>
                    @elseif($tenantLease)
                      <a href="{{ route('messages.show', $tenantLease) }}" class="db-table-link">View in thread →</a>
                    @endif
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @endif
    </div>
  </div>

  <div class="msg-tab-panel {{ $activeTab === 'lease' ? 'active' : '' }}" id="tab-lease">
    <div class="db-card-body">
      @if($leaseEmpty)
        <div class="db-empty" style="padding:32px 0">
          <div class="db-empty-icon">📋</div>
          <h3>No lease threads yet</h3>
          <p>
            @if($user->isLandlord())
              Tenant replies on a unit appear here. Switch to <strong>Property</strong> to send a building notice.
            @else
              Your lease thread will appear here once you have an active tenancy.
            @endif
          </p>
          @if($user->isLandlord() && ! $propertyEmpty)
            <a href="{{ route('messages.index', ['tab' => 'property']) }}" class="db-btn db-btn-primary" style="margin-top:12px">Property</a>
          @endif
        </div>
      @else
        <div class="db-table-wrap">
          <table class="db-table">
            <thead>
              <tr>
                <th>Property</th>
                <th>{{ $user->isLandlord() ? 'Tenant' : 'Landlord' }}</th>
                <th>Last activity</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              @foreach($leases as $lease)
              <tr>
                <td>
                  <div class="db-flag-name">
                    <span class="db-flag">{{ config('countries.'.$lease->property->country_code.'.flag','🏠') }}</span>
                    <div>
                      <div class="db-name">{{ $lease->property->name }}</div>
                      <div class="db-sub">{{ $lease->property->city }}</div>
                    </div>
                  </div>
                </td>
                <td>
                  @if($user->isLandlord())
                    <strong>{{ $lease->tenant?->fullName() ?? '—' }}</strong>
                    @if($lease->unread_from_tenant > 0)
                      <span class="badge badge-terra" style="margin-left:8px">{{ $lease->unread_from_tenant }} new</span>
                    @endif
                  @else
                    {{ $lease->property->landlord?->fullName() ?? '—' }}
                    @if($lease->unread_for_me > 0)
                      <span class="badge badge-terra" style="margin-left:8px">{{ $lease->unread_for_me }} new</span>
                    @endif
                  @endif
                </td>
                <td>
                  @if($lease->messages_max_created_at)
                    {{ \Carbon\Carbon::parse($lease->messages_max_created_at)->format('d M Y, H:i') }}
                  @else
                    <span class="db-sub">No messages yet</span>
                  @endif
                </td>
                <td style="text-align:right">
                  <a href="{{ route('messages.show', $lease) }}" class="db-table-link">Open thread →</a>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @endif
    </div>
  </div>
</div>
@endif
@endsection
