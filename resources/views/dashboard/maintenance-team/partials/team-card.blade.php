@php
  $compact = $compact ?? false;
  $isEngaged = $isEngaged ?? false;
  $avg = $team->averageRating();
  $count = $team->reviewCount();
@endphp
<div class="mt-team-card {{ $compact ? 'mt-team-card-compact' : '' }}">
  <div class="mt-team-card-top">
    <div>
      <h4><a href="{{ route('landlord.maintenance-team.show', $team) }}" class="mt-team-link">{{ $team->name }}</a></h4>
      <div class="mt-team-meta">{{ $team->locationLabel() }}</div>
      @if($count > 0)
        @include('dashboard.maintenance-team.partials.rating-stars', ['rating' => $avg, 'size' => 'sm'])
        <span class="mt-review-count">({{ $count }} {{ $count === 1 ? 'review' : 'reviews' }})</span>
      @else
        <span class="mt-review-count">No reviews yet</span>
      @endif
    </div>
    @if($isEngaged)
      <span class="badge badge-green">On your team</span>
    @endif
  </div>
  @if($team->serviceList()->isNotEmpty())
    <div class="mt-services">
      @foreach($team->serviceList()->take($compact ? 3 : 6) as $service)
        <span class="badge badge-navy">{{ $service }}</span>
      @endforeach
    </div>
  @endif
  @if(!$compact && $team->description)
    <p class="mt-team-desc">{{ Str::limit($team->description, 160) }}</p>
  @endif
  <div class="mt-team-actions">
    <a href="{{ route('landlord.maintenance-team.show', $team) }}" class="db-btn db-btn-ghost">View profile</a>
    @if($isEngaged)
      <form method="POST" action="{{ route('landlord.maintenance-team.disengage', $team) }}" onsubmit="return confirm('Remove this team from your roster?');">
        @csrf @method('DELETE')
        <button type="submit" class="db-btn db-btn-danger">Remove</button>
      </form>
    @else
      <form method="POST" action="{{ route('landlord.maintenance-team.engage', $team) }}">
        @csrf
        <button type="submit" class="db-btn db-btn-primary">Add to my team</button>
      </form>
    @endif
  </div>
</div>
