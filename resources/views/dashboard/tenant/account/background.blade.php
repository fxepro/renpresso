@php
  $checkTypes = [
    'credit'        => ['Credit check', 'Score and credit history for rent decisions.'],
    'criminal'      => ['Criminal background', 'County / national criminal records search.'],
    'eviction'      => ['Eviction history', 'Prior eviction filings and judgments.'],
    'employment'    => ['Employment verification', 'Income and employer confirmation.'],
    'references'    => ['References', 'Landlord or personal reference checks.'],
    'right_to_rent' => ['Right to rent', 'Legal right to rent (where required).'],
  ];
@endphp

<div class="db-card" style="margin-bottom:18px">
  <div class="db-card-header"><span class="db-card-title">Last background check</span></div>
  <div class="db-card-body">
    @if($lastBackgroundCheck)
      <table class="db-table">
        <tbody>
          <tr>
            <td style="width:36%;color:var(--text-light)">Type</td>
            <td><strong>{{ ucfirst(str_replace('_', ' ', $lastBackgroundCheck->type)) }}</strong></td>
          </tr>
          <tr>
            <td style="color:var(--text-light)">Status</td>
            <td><span class="badge badge-{{ $lastBackgroundCheck->statusColor() }}">{{ ucfirst(str_replace('_', ' ', $lastBackgroundCheck->status)) }}</span></td>
          </tr>
          <tr>
            <td style="color:var(--text-light)">Completed</td>
            <td>{{ $lastBackgroundCheck->completed_at?->format('d M Y H:i') ?? 'In progress' }}</td>
          </tr>
          <tr>
            <td style="color:var(--text-light)">Last updated</td>
            <td>{{ $lastBackgroundCheck->updated_at->format('d M Y') }}</td>
          </tr>
        </tbody>
      </table>
    @else
      <div class="db-empty" style="padding:20px">
        <p>No background checks on file yet.</p>
      </div>
    @endif
  </div>
</div>

<div class="db-card" style="margin-bottom:18px">
  <div class="db-card-header"><span class="db-card-title">Request checks</span></div>
  <div class="db-card-body">
    <p class="db-form-hint" style="margin-bottom:4px">Select one or more checks to request. Each type is processed independently. Your landlord may charge a fee per check.</p>
    <form method="POST" action="{{ route('tenant.account.background-check') }}" class="db-form">
      @csrf
      @if($errors->has('types'))
        <div class="db-alert db-alert-error">{{ $errors->first('types') }}</div>
      @endif
      <div class="rm-check-grid">
        @foreach($checkTypes as $type => [$title, $hint])
          @php $inProgress = in_array($type, $pendingCheckTypes ?? [], true); @endphp
          <label class="rm-check-option">
            <input type="checkbox" name="types[]" value="{{ $type }}"
              {{ in_array($type, old('types', [])) ? 'checked' : '' }}
              {{ $inProgress ? 'disabled' : '' }}>
            <div>
              <strong>{{ $title }}@if($inProgress) <span class="badge badge-gold">In progress</span>@endif</strong>
              <span>{{ $hint }}</span>
            </div>
          </label>
        @endforeach
      </div>
      <button type="submit" class="db-form-submit">Request selected checks</button>
    </form>
  </div>
</div>

<div class="db-card">
  <div class="db-card-header"><span class="db-card-title">All screening history</span></div>
  <div class="db-card-body">
    @if($applications->isEmpty())
      <div class="db-empty" style="padding:24px">
        <p>No applications linked to {{ $user->email }}.</p>
      </div>
    @else
      <div class="db-table-wrap">
        <table class="db-table">
          <thead>
            <tr>
              <th>Property</th>
              <th>Check</th>
              <th>Status</th>
              <th>Requested</th>
              <th>Completed</th>
            </tr>
          </thead>
          <tbody>
            @foreach($applications as $app)
              @forelse($app->backgroundChecks->sortByDesc('updated_at') as $check)
              <tr>
                <td>{{ $app->property->name ?? '—' }}</td>
                <td>{{ ucfirst(str_replace('_', ' ', $check->type)) }}</td>
                <td><span class="badge badge-{{ $check->statusColor() }}">{{ ucfirst(str_replace('_', ' ', $check->status)) }}</span></td>
                <td>{{ $check->created_at->format('d M Y') }}</td>
                <td>{{ $check->completed_at?->format('d M Y') ?? '—' }}</td>
              </tr>
              @empty
              <tr>
                <td>{{ $app->property->name ?? '—' }}</td>
                <td colspan="4" style="color:var(--text-light)">No checks</td>
              </tr>
              @endforelse
            @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </div>
</div>
