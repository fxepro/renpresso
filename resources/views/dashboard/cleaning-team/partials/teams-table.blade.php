@php
  $showEngage = $showEngage ?? false;
  $showRemove = $showRemove ?? false;
@endphp
<div class="db-table-wrap">
  <table class="db-table mt-teams-table">
    <thead>
      <tr>
        <th>Crew</th>
        <th>Location</th>
        <th class="mt-th-services">Services</th>
        <th>Rating</th>
        <th>Contact</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      @foreach($teams as $team)
      @php
        $avg = $team->averageRating();
        $count = $team->reviewCount();
      @endphp
      <tr>
        <td>
          <a href="{{ route('landlord.cleaning-team.show', $team) }}" class="db-table-link">{{ $team->name }}</a>
          @if($team->owner)
            <span style="display:block;font-size:13px;color:var(--text-light);margin-top:2px">{{ $team->owner->fullName() }}</span>
          @endif
        </td>
        <td>{{ $team->locationLabel() }}</td>
        <td class="mt-td-services">
          @if($team->serviceList()->isNotEmpty())
            <div class="mt-services-cell">
              @foreach($team->serviceList() as $service)
                <span class="badge badge-navy">{{ $service }}</span>
              @endforeach
            </div>
          @else
            <span class="mt-services-empty">—</span>
          @endif
        </td>
        <td class="mt-td-rating">
          @if($count > 0)
            @include('dashboard.cleaning-team.partials.rating-stars', ['rating' => $avg, 'size' => 'sm', 'showValue' => true])
            <span class="mt-review-count">({{ $count }})</span>
          @else
            <span class="mt-review-count">—</span>
          @endif
        </td>
        <td>{{ $team->owner->fullName() }}</td>
        <td class="mt-td-actions">
          <div class="mt-row-actions">
            <a href="{{ route('landlord.cleaning-team.show', $team) }}" class="db-btn db-btn-ghost" style="padding:6px 12px;font-size:14px">View</a>
            @if($showRemove)
              <form method="POST" action="{{ route('landlord.cleaning-team.disengage', $team) }}" onsubmit="return confirm('Remove this crew from your roster?');">
                @csrf @method('DELETE')
                <button type="submit" class="db-btn db-btn-danger" style="padding:6px 12px;font-size:14px">Remove</button>
              </form>
            @endif
            @if($showEngage)
              <form method="POST" action="{{ route('landlord.cleaning-team.engage', $team) }}">
                @csrf
                <button type="submit" class="db-btn db-btn-primary" style="padding:6px 12px;font-size:14px">Add</button>
              </form>
            @endif
          </div>
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
</div>
