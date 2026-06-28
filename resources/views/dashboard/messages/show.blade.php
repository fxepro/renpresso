@extends('dashboard.layout')
@section('page-title', 'Messages')
@section('breadcrumb')
  · <a href="{{ route('messages.index') }}" style="color:inherit;text-decoration:none;border-bottom:1px solid rgba(138,153,170,0.4)">Messages</a>
  @if($isLandlord ?? false)
    · <a href="{{ route('messages.property', $lease->property) }}" style="color:inherit;text-decoration:none;border-bottom:1px solid rgba(138,153,170,0.4)">{{ $lease->property->name }}</a>
  @endif
  · {{ $isLandlord ?? false ? ($otherParty?->fullName() ?? 'Tenant') : $lease->property->name }}
@endsection
@section('content')

@php
  $withName = $otherParty?->fullName() ?? 'the other party';
  $isLandlord = $isLandlord ?? (auth()->id() === $lease->property->landlord_id);
@endphp

<div class="db-card" style="margin-bottom:20px">
  <div class="db-card-body">
    <p style="margin:0;font-size:var(--fs-body);color:var(--text-mid);line-height:1.5">
      @if($isLandlord)
        Thread with <strong>{{ $withName }}</strong> at <strong>{{ $lease->property->name }}</strong>.
        Building-wide notices appear here too. To email <em>all</em> tenants, use
        <a href="{{ route('messages.property', $lease->property) }}" style="color:var(--terra)">building notices</a>.
      @else
        Your unit at <strong>{{ $lease->property->name }}</strong>.
        Building notices and your replies with <strong>{{ $withName }}</strong> are in one timeline.
      @endif
    </p>
  </div>
</div>

<div class="db-card" style="margin-bottom:20px">
  <div class="db-card-header">
    <h2 class="db-card-title">Conversation</h2>
  </div>
  <div class="db-card-body" style="padding-top:16px">
    @if($messages->isEmpty())
      <p class="db-sub" style="margin-bottom:16px">No messages yet.</p>
    @else
      <div style="display:flex;flex-direction:column;gap:14px;margin-bottom:8px">
        @foreach($messages as $msg)
          @php
            $mine = $msg->sender_id === auth()->id();
            $isBroadcast = $msg->isPropertyBroadcast();
          @endphp
          <div style="display:flex;justify-content:{{ $mine ? 'flex-end' : 'flex-start' }}">
            <div style="max-width:min(560px,92%);background:{{ $isBroadcast ? 'var(--gold-pale)' : ($mine ? 'var(--terra-pale)' : 'var(--white)') }};border:1px solid var(--cream-dark);border-radius:12px;padding:12px 14px">
              <div style="font-size:var(--fs-step);color:var(--text-light);margin-bottom:6px">
                @if($isBroadcast)
                  <span class="badge badge-gold" style="margin-right:6px">Building notice</span>
                @endif
                {{ $mine ? 'You' : $msg->sender->fullName() }}
                · {{ $msg->created_at->format('d M Y, H:i') }}
                @if($msg->emailed_at && $isBroadcast)
                  <span class="badge badge-gold" style="margin-left:6px;font-size:11px">Emailed all</span>
                @elseif($msg->emailed_at)
                  <span class="badge badge-gold" style="margin-left:6px;font-size:11px">Emailed</span>
                @endif
              </div>
              <div style="font-size:var(--fs-body);color:var(--text-dark);white-space:pre-wrap;line-height:1.5">{{ $msg->body }}</div>
            </div>
          </div>
        @endforeach
      </div>
    @endif

    <form method="POST" action="{{ route('messages.store', $lease) }}" class="db-form" style="max-width:none;margin-top:20px;border-top:1px solid var(--cream-dark);padding-top:20px">
      @csrf
      <div class="db-form-group">
        <label for="body">{{ $isLandlord ? 'Reply to this tenant (in-app)' : 'Your message' }}</label>
        <textarea id="body" name="body" class="db-textarea" rows="4" required placeholder="{{ $isLandlord ? 'Private reply for this unit…' : 'Type your message…' }}" maxlength="5000">{{ old('body') }}</textarea>
        @error('body')<div class="db-form-error">{{ $message }}</div>@enderror
      </div>
      @unless($isLandlord)
      <div class="db-form-group">
        <label style="display:flex;align-items:flex-start;gap:10px;font-weight:500;cursor:pointer">
          <input type="checkbox" name="also_email" value="1" {{ old('also_email') ? 'checked' : '' }} style="width:18px;height:18px;margin-top:2px;accent-color:var(--terra);flex-shrink:0">
          <span>Also email a copy to {{ $withName }}</span>
        </label>
      </div>
      <p class="db-form-hint">Your reply is tied to this lease so your landlord knows which unit you mean.</p>
      @else
      <p class="db-form-hint">Landlord replies on a lease thread are in-app only. Use building notices to email every tenant at once.</p>
      @endunless
      <button type="submit" class="db-form-submit">{{ $isLandlord ? 'Send reply' : 'Send message' }}</button>
    </form>
  </div>
</div>
@endsection
