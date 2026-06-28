@extends('dashboard.layout')
@section('page-title', $team->name)
@section('breadcrumb', $team->locationLabel())

@section('topbar-actions')
  <a href="{{ route('landlord.cleaning-team.index') }}" class="db-btn db-btn-ghost">← All crews</a>
  @if($isEngaged)
    <form method="POST" action="{{ route('landlord.cleaning-team.disengage', $team) }}" style="display:inline" onsubmit="return confirm('Remove from your roster?');">
      @csrf @method('DELETE')
      <button type="submit" class="db-btn db-btn-danger">Remove from my roster</button>
    </form>
  @elseif($matchesPortfolio)
    <form method="POST" action="{{ route('landlord.cleaning-team.engage', $team) }}" style="display:inline">
      @csrf
      <button type="submit" class="db-btn db-btn-primary">Add to my roster</button>
    </form>
  @endif
@endsection

@push('styles')
<style>
.mt-profile-hero { background:var(--navy); color:var(--white); border-radius:var(--radius-lg); padding:32px 36px; margin-bottom:18px; }
.mt-profile-hero h1 { font-family:'Fraunces',serif; font-size:var(--fs-display); font-weight:500; margin-bottom:8px; letter-spacing:-0.02em; }
.mt-profile-hero .loc { color:rgba(255,255,255,0.55); font-size:var(--fs-step); margin-bottom:14px; }
.mt-profile-hero .mt-stars .mt-star { color:rgba(255,255,255,0.25); }
.mt-profile-hero .mt-stars .mt-star.filled { color:var(--gold); }
.mt-profile-hero .mt-rating-num { color:var(--white); }
.mt-profile-hero .mt-review-count { color:rgba(255,255,255,0.45); }
.mt-profile-grid { display:grid; grid-template-columns:1fr 340px; gap:18px; align-items:start; }
@media (max-width:900px) { .mt-profile-grid { grid-template-columns:1fr; } }
.mt-services { display:flex; flex-wrap:wrap; gap:8px; margin-top:14px; }
.mt-profile-hero .badge-navy { background:rgba(255,255,255,0.12); color:var(--white); }
.mt-kyc-row { display:flex; justify-content:space-between; padding:10px 0; border-bottom:1px solid var(--cream-dark); font-size:var(--fs-body); }
.mt-kyc-row:last-child { border-bottom:none; }
.mt-kyc-label { color:var(--text-light); }
.mt-kyc-value { color:var(--text-dark); font-weight:500; text-align:right; }
.mt-review-item { padding:16px 0; border-bottom:1px solid var(--cream-dark); }
.mt-review-item:last-child { border-bottom:none; }
.mt-review-meta { font-size:var(--fs-step); color:var(--text-light); margin-top:6px; }
.mt-review-body { color:var(--text-mid); line-height:1.55; margin-top:8px; }
.mt-stars { display:inline-flex; align-items:center; gap:2px; }
.mt-star { color:var(--cream-dark); font-size:15px; }
.mt-star.filled { color:var(--gold); }
.mt-rating-num { margin-left:6px; font-weight:600; }
.mt-star-input { display:flex; gap:8px; margin:8px 0 12px; flex-direction:row; }
.mt-star-input label.star-opt { cursor:pointer; font-size:26px; color:var(--cream-dark); }
.mt-star-input input { position:absolute; opacity:0; width:0; height:0; }
.mt-star-input input:checked + label.star-opt { color:var(--gold); }
</style>
@endpush

@section('content')
@php
  $avg = $team->averageRating();
  $count = $team->reviewCount();
@endphp

<div class="mt-profile-hero">
  <h1>{{ $team->name }}</h1>
  <p class="loc">{{ $team->locationLabel() }}@if($team->phone) · {{ $team->phone }}@endif</p>
  @if($count > 0)
    @include('dashboard.cleaning-team.partials.rating-stars', ['rating' => $avg])
    <span class="mt-review-count">({{ $count }} {{ $count === 1 ? 'review' : 'reviews' }})</span>
  @else
    <span class="mt-review-count">No reviews yet</span>
  @endif
  @if($isEngaged)
    <span class="badge badge-green" style="margin-left:10px">On your roster</span>
  @endif
  @if($team->serviceList()->isNotEmpty())
    <div class="mt-services">
      @foreach($team->serviceList() as $service)
        <span class="badge badge-navy">{{ $service }}</span>
      @endforeach
    </div>
  @endif
</div>

<div class="mt-profile-grid">
  <div>
    <div class="db-card" style="margin-bottom:18px">
      <div class="db-card-header"><h2 class="db-card-title">About</h2></div>
      <div class="db-card-body">
        <p style="line-height:1.65;color:var(--text-mid)">{{ $team->description ?: 'No description provided.' }}</p>
      </div>
    </div>

    <div class="db-card">
      <div class="db-card-header"><h2 class="db-card-title">Reviews</h2></div>
      <div class="db-card-body">
        @forelse($team->reviews as $review)
          <div class="mt-review-item">
            @include('dashboard.cleaning-team.partials.rating-stars', ['rating' => $review->rating, 'showValue' => false])
            @if($review->comment)<p class="mt-review-body">{{ $review->comment }}</p>@endif
            <p class="mt-review-meta">{{ $review->landlord->fullName() }} · {{ $review->created_at->format('d M Y') }}</p>
          </div>
        @empty
          <p class="db-form-hint">No reviews yet. Be the first after you add this crew to your roster.</p>
        @endforelse

        @if($isEngaged)
          <hr style="border:none;border-top:1px solid var(--cream-dark);margin:24px 0">
          <h3 style="font-size:var(--fs-title);margin-bottom:12px">{{ $myReview ? 'Update your review' : 'Leave a review' }}</h3>
          <form method="POST" action="{{ route('landlord.cleaning-team.review', $team) }}" class="db-form mt-review-form" style="max-width:none">
            @csrf
            <label class="db-form-group" style="display:block">
              <span style="font-weight:600;font-size:var(--fs-step)">Rating</span>
              <div class="mt-star-input">
                @for($s = 1; $s <= 5; $s++)
                  <span style="display:inline-flex;align-items:center">
                    <input type="radio" name="rating" id="star{{ $s }}" value="{{ $s }}" {{ (int) old('rating', $myReview?->rating) === $s ? 'checked' : '' }} required>
                    <label for="star{{ $s }}" class="star-opt" title="{{ $s }} stars">★</label>
                  </span>
                @endfor
              </div>
              @error('rating')<span class="db-form-error">{{ $message }}</span>@enderror
            </label>
            <div class="db-form-group">
              <label for="comment">Comment (optional)</label>
              <textarea class="db-textarea" id="comment" name="comment" rows="3" placeholder="Share your experience with this crew…">{{ old('comment', $myReview?->comment) }}</textarea>
              @error('comment')<span class="db-form-error">{{ $message }}</span>@enderror
            </div>
            <button type="submit" class="db-form-submit">{{ $myReview ? 'Update review' : 'Submit review' }}</button>
          </form>
        @elseif($matchesPortfolio)
          <p class="db-form-hint" style="margin-top:16px">Add this crew to your roster to leave a review.</p>
        @endif
      </div>
    </div>
  </div>

  <div>
    <div class="db-card">
      <div class="db-card-header"><h2 class="db-card-title">Contact</h2></div>
      <div class="db-card-body">
        <div class="mt-kyc-row"><span class="mt-kyc-label">Primary contact</span><span class="mt-kyc-value">{{ $team->owner->fullName() }}</span></div>
        @if($team->phone)
          <div class="mt-kyc-row"><span class="mt-kyc-label">Phone</span><span class="mt-kyc-value">{{ $team->phone }}</span></div>
        @endif
        <div class="mt-kyc-row"><span class="mt-kyc-label">Email</span><span class="mt-kyc-value" style="word-break:break-all">{{ $team->owner->email }}</span></div>
      </div>
    </div>
  </div>
</div>
@endsection
