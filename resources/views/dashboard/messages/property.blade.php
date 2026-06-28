@extends('dashboard.layout')
@section('page-title', 'Building notices')
@section('breadcrumb')
  · <a href="{{ route('messages.index') }}" style="color:inherit;text-decoration:none;border-bottom:1px solid rgba(138,153,170,0.4)">Messages</a>
  · {{ $property->name }}
@endsection
@section('content')

@php
  $tenantCount = $property->messagingLeases->pluck('tenant_id')->filter()->unique()->count();
@endphp

<div class="db-card" style="margin-bottom:20px">
  <div class="db-card-body">
    <p style="margin:0;font-size:var(--fs-body);color:var(--text-mid);line-height:1.5">
      Post a <strong>building notice</strong> for <strong>{{ $property->name }}</strong>.
      All tenants with an active or pending lease see it in-app on their unit thread.
      Email goes to <strong>{{ $tenantCount }}</strong> unique tenant address{{ $tenantCount === 1 ? '' : 'es' }} (not one email per lease).
    </p>
  </div>
</div>

<div class="db-grid-2" style="margin-bottom:20px;align-items:start">
  <div class="db-card">
    <div class="db-card-header">
      <h2 class="db-card-title">New building notice</h2>
    </div>
    <div class="db-card-body">
      <form method="POST" action="{{ route('messages.property.store', $property) }}" class="db-form" style="max-width:none">
        @csrf
        <div class="db-form-group">
          <label for="body">Message to all tenants</label>
          <textarea id="body" name="body" class="db-textarea" rows="5" required maxlength="5000" placeholder="e.g. Water shutoff Tuesday 9am–noon…">{{ old('body') }}</textarea>
          @error('body')<div class="db-form-error">{{ $message }}</div>@enderror
        </div>
        <div class="db-form-group">
          <label style="display:flex;align-items:flex-start;gap:10px;font-weight:500;cursor:pointer">
            <input type="checkbox" name="also_email" value="1" {{ old('also_email', true) ? 'checked' : '' }} style="width:18px;height:18px;margin-top:2px;accent-color:var(--terra);flex-shrink:0">
            <span>Email all tenants (one per person)</span>
          </label>
        </div>
        <p class="db-form-hint">For a single tenant or a private reply, use that unit&rsquo;s lease thread instead — those stay in-app only.</p>
        <button type="submit" class="db-form-submit">Send building notice</button>
      </form>
    </div>
  </div>

  <div class="db-card">
    <div class="db-card-header">
      <h2 class="db-card-title">Past notices</h2>
    </div>
    <div class="db-card-body" style="max-height:420px;overflow-y:auto">
      @forelse($broadcasts as $msg)
        <div style="padding:12px 0;border-bottom:1px solid var(--cream-dark)">
          <div style="font-size:var(--fs-step);color:var(--text-light);margin-bottom:6px">
            {{ $msg->created_at->format('d M Y, H:i') }}
            @if($msg->emailed_at)
              <span class="badge badge-gold" style="margin-left:6px">Emailed tenants</span>
            @endif
          </div>
          <div style="white-space:pre-wrap;font-size:var(--fs-body);line-height:1.5">{{ $msg->body }}</div>
        </div>
      @empty
        <p class="db-sub">No building notices yet.</p>
      @endforelse
    </div>
  </div>
</div>

@if($leaseThreads->isNotEmpty())
<div class="db-card">
  <div class="db-card-header">
    <h2 class="db-card-title">Tenant replies — by unit (lease)</h2>
  </div>
  <div class="db-table-wrap">
    <table class="db-table">
      <thead>
        <tr>
          <th>Tenant</th>
          <th>Last message</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @foreach($leaseThreads as $lease)
        <tr>
          <td>
            <strong>{{ $lease->tenant?->fullName() ?? 'Unassigned' }}</strong>
            @if($lease->unread_from_tenant > 0)
              <span class="badge badge-terra" style="margin-left:8px">{{ $lease->unread_from_tenant }} new</span>
            @endif
          </td>
          <td>
            @if($lease->last_lease_message_at)
              {{ \Carbon\Carbon::parse($lease->last_lease_message_at)->format('d M Y, H:i') }}
            @else
              <span class="db-sub">No replies yet</span>
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
</div>
@endif
@endsection
