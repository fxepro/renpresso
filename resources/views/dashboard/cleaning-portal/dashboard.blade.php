@extends('dashboard.cleaning-portal.layout')
@section('page-title', 'Dashboard')
@section('content')
@if(!$team)
  <div class="db-card">
    <div class="db-card-body">
      <p style="color:var(--text-mid);margin:0 0 16px">Your crew profile should be set up from registration. Contact support if this page is empty.</p>
    </div>
  </div>
@else
<div class="db-stats">
  <div class="db-stat">
    <div class="db-stat-label">Linked landlords</div>
    <div class="db-stat-value">{{ $stats['landlords_linked'] }}</div>
    <div class="db-stat-sub">On their roster</div>
  </div>
  <div class="db-stat">
    <div class="db-stat-label">Operating cities</div>
    <div class="db-stat-value">{{ $stats['cities'] }}</div>
  </div>
  <div class="db-stat">
    <div class="db-stat-label">Reviews</div>
    <div class="db-stat-value">{{ $stats['reviews'] }}</div>
  </div>
  <div class="db-stat">
    <div class="db-stat-label">Avg rating</div>
    <div class="db-stat-value">{{ $stats['avg_rating'] ? number_format($stats['avg_rating'], 1) : '—' }}</div>
  </div>
</div>

<div class="clean-quick-links" style="margin-bottom:20px">
  <a href="{{ route('clean.team.edit') }}" class="clean-quick-link"><strong>Crew profile</strong><span>{{ $team->name }} — services &amp; listing</span></a>
  <a href="{{ route('clean.cities.index') }}" class="clean-quick-link"><strong>Operating cities</strong><span>{{ $stats['cities'] }} area{{ $stats['cities'] === 1 ? '' : 's' }} you serve</span></a>
</div>

<div class="db-card">
  <div class="db-card-header"><span class="db-card-title">Recent reviews</span><a href="{{ route('clean.account', ['tab' => 'reviews']) }}" class="db-btn db-btn-ghost">All reviews</a></div>
  <div class="db-card-body" style="padding:0">
    @if($recentReviews->isEmpty())
      <p style="padding:22px;color:var(--text-light);margin:0">No reviews yet. Landlords on your roster can leave feedback after turnovers.</p>
    @else
    <div class="db-table-wrap">
      <table class="db-table">
        <thead><tr><th>Landlord</th><th>Rating</th><th>Comment</th></tr></thead>
        <tbody>
          @foreach($recentReviews as $review)
          <tr>
            <td>{{ $review->landlord->fullName() }}</td>
            <td><strong>{{ $review->rating }}/5</strong></td>
            <td>{{ Str::limit($review->comment ?: '—', 80) }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    @endif
  </div>
</div>
<p style="font-size:14px;color:var(--text-light);margin:0">Turnover job booking from the landlord calendar is coming soon.</p>
@endif
@endsection
