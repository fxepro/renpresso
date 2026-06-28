@php
  $avg = $team?->averageRating();
  $count = $team?->reviewCount() ?? 0;
@endphp
<p class="db-form-hint" style="margin:0 0 16px;max-width:52rem;line-height:1.55">Reviews from landlords who have added your team to their roster. You cannot edit reviews — respond via quality of service.</p>

@if(! $team)
  <div class="db-alert" style="background:var(--gold-pale);color:var(--gold)">Set up your company profile first.</div>
@else
  <div class="db-card" style="margin-bottom:18px">
    <div class="db-card-body" style="display:flex;flex-wrap:wrap;align-items:center;gap:16px">
      @if($avg)
        <div>
          <p style="font-size:12px;color:var(--text-light);margin:0 0 4px;text-transform:uppercase;letter-spacing:0.04em">Average rating</p>
          <p style="margin:0;font-family:'Fraunces',serif;font-size:32px;font-weight:600">{{ number_format($avg, 1) }} <span style="font-size:18px;color:var(--gold)">★</span></p>
        </div>
      @endif
      <div>
        <p style="font-size:12px;color:var(--text-light);margin:0 0 4px;text-transform:uppercase;letter-spacing:0.04em">Total reviews</p>
        <p style="margin:0;font-size:20px;font-weight:600">{{ $count }}</p>
      </div>
      @if(! $team->is_listed)
        <p style="margin:0;font-size:14px;color:var(--text-mid)">Enable <strong>Listed in directory</strong> under Account → Professional company to attract more landlords.</p>
      @endif
    </div>
  </div>

  <div class="db-card">
    <div class="db-card-header"><span class="db-card-title">Landlord reviews</span></div>
    <div class="db-card-body">
      @forelse($reviews as $review)
        <div style="padding:16px 0;border-bottom:1px solid var(--cream-dark)">
          <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px">
            @for($i = 1; $i <= 5; $i++)
              <span style="color:{{ $i <= $review->rating ? 'var(--gold)' : 'var(--cream-dark)' }}">★</span>
            @endfor
            <span style="font-size:14px;font-weight:600;color:var(--text-dark)">{{ $review->rating }}/5</span>
          </div>
          @if($review->comment)<p style="font-size:15px;color:var(--text-mid);line-height:1.55;margin:0 0 8px">{{ $review->comment }}</p>@endif
          <p style="font-size:13px;color:var(--text-light);margin:0">{{ $review->landlord?->fullName() ?? 'Landlord' }} · {{ $review->created_at->format('d M Y') }}</p>
        </div>
      @empty
        <p style="margin:0;color:var(--text-light)">No reviews yet. Reviews appear after landlords add you to their roster and complete jobs.</p>
      @endforelse
    </div>
  </div>
@endif
