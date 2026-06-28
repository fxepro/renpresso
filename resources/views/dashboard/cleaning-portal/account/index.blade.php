@extends('dashboard.cleaning-portal.layout')
@section('page-title', 'Account')
@section('content')
<nav class="clean-acc-tabs" aria-label="Account sections">
  <a href="{{ route('clean.account', ['tab' => 'profile']) }}" class="clean-acc-tab {{ $tab === 'profile' ? 'active' : '' }}">Profile &amp; password</a>
  <a href="{{ route('clean.team.edit') }}" class="clean-acc-tab {{ request()->routeIs('clean.team.*') ? 'active' : '' }}">Crew profile</a>
  <a href="{{ route('clean.account', ['tab' => 'reviews']) }}" class="clean-acc-tab {{ $tab === 'reviews' ? 'active' : '' }}">Reviews</a>
</nav>

@if($tab === 'profile')
<div class="clean-grid-2" style="align-items:start">
  <div class="db-card">
    <div class="db-card-header"><span class="db-card-title">Your details</span></div>
    <div class="db-card-body">
      <form method="POST" action="{{ route('clean.account.profile') }}" class="db-form" style="max-width:100%">
        @csrf @method('PUT')
        <div class="db-form-row">
          <div class="db-form-group"><label>First name</label><input type="text" name="first_name" class="db-input" value="{{ old('first_name', $user->first_name) }}" required></div>
          <div class="db-form-group"><label>Last name</label><input type="text" name="last_name" class="db-input" value="{{ old('last_name', $user->last_name) }}" required></div>
        </div>
        <div class="db-form-group"><label>Phone</label><input type="text" name="phone" class="db-input" value="{{ old('phone', $user->phone) }}"></div>
        <div class="db-form-group"><label>Email</label><input type="email" class="db-input" value="{{ $user->email }}" disabled></div>
        <button type="submit" class="db-form-submit">Save profile</button>
      </form>
    </div>
  </div>
  <div class="db-card">
    <div class="db-card-header"><span class="db-card-title">Password</span></div>
    <div class="db-card-body">
      <form method="POST" action="{{ route('clean.account.password') }}" class="db-form" style="max-width:100%">
        @csrf @method('PUT')
        <div class="db-form-group"><label>Current password</label><input type="password" name="current_password" class="db-input" required autocomplete="current-password"></div>
        <div class="db-form-group"><label>New password</label><input type="password" name="password" class="db-input" required autocomplete="new-password" minlength="8"></div>
        <div class="db-form-group"><label>Confirm new password</label><input type="password" name="password_confirmation" class="db-input" required autocomplete="new-password"></div>
        <button type="submit" class="db-form-submit">Update password</button>
      </form>
    </div>
  </div>
</div>
@elseif($tab === 'reviews')
<div class="db-card">
  <div class="db-card-header"><span class="db-card-title">Landlord reviews ({{ $reviews->count() }})</span></div>
  <div class="db-card-body">
    @forelse($reviews as $review)
      <div style="padding:14px 0;border-bottom:1px solid var(--cream-dark)">
        <strong>{{ $review->landlord->fullName() }}</strong>
        <span style="margin-left:8px;color:var(--gold)">{{ str_repeat('★', $review->rating) }}</span>
        <span style="font-size:13px;color:var(--text-light);margin-left:8px">{{ $review->created_at->format('d M Y') }}</span>
        @if($review->comment)<p style="margin-top:8px;color:var(--text-mid);line-height:1.55">{{ $review->comment }}</p>@endif
      </div>
    @empty
      <p style="color:var(--text-light);margin:0">No reviews yet.</p>
    @endforelse
  </div>
</div>
@endif
@endsection
