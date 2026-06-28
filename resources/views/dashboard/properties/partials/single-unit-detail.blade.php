@php
  use App\Support\CurrencyDisplay;
  $unitSeq          = $unitSeq ?? null;
  $unitSlotsPayload = $unitSlotsPayload ?? null;
  $isUnitInBlock    = $property->isMultiUnit() && $unitSeq !== null;
  $isBuildingLevel  = $property->isMultiUnit() && $unitSeq === null;
  $activeLeases     = $activeLeases ?? $property->leases->where('status','active')->sortBy(fn ($l) => sprintf('%06d|%s',(int)$l->unit_seq,strtolower($l->unit_label??'')));
  $unitDisplayLabel = $unitDisplayLabel ?? ($isUnitInBlock ? ('Unit '.$unitSeq) : $property->name);
  $panelOpenArgs    = $isUnitInBlock ? ", { unitSeq: {$unitSeq} }" : '';
@endphp

<div class="db-card" style="margin-bottom:20px">
  <div class="db-card-header" style="align-items:flex-start;flex-direction:column;gap:6px">
    <span class="db-card-title">{{ $isUnitInBlock ? $unitDisplayLabel : $property->name }}</span>
    @if($isUnitInBlock)
      <p style="font-size:13px;color:var(--text-light);margin:0">{{ $property->name }} · {{ $property->city }}</p>
    @endif
    <p style="font-size:14px;color:var(--text-mid);line-height:1.5;margin:0;max-width:48rem">Use the side panel for applications, lease, rent, payments, and media.</p>
    @if(!$isBuildingLevel)
    <button type="button" class="db-btn db-btn-primary" style="margin-top:10px;font-size:13px" onclick="openPanel('{{ $property->id }}'{{ $panelOpenArgs }})">Open panel</button>
    @endif
  </div>
</div>

@if($isBuildingLevel)
{{-- ── Building level: units table only ── --}}
<div class="db-card" style="margin-bottom:16px">
  <div class="db-card-header"><span class="db-card-title">Units</span></div>
  @if(!empty($unitSlotsPayload))
  <div class="db-table-wrap">
    <table class="db-table">
      <thead>
        <tr>
          <th style="text-align:center;width:3rem"></th>
          <th>Type</th><th>Listing</th><th>Units</th><th>Country</th><th>Lessee</th><th>Rent</th><th>Status</th>
        </tr>
      </thead>
      <tbody>
        @foreach($unitSlotsPayload as $u)
        @php $uLease = $u['lease'] ?? null; @endphp
        <tr style="cursor:pointer" onclick="openPanel('{{ $property->id }}',{unitSeq:{{ $u['seq'] }}})">
          <td style="text-align:center;color:var(--text-light);font-size:13px;font-variant-numeric:tabular-nums">{{ $loop->iteration }}</td>
          <td>{{ $u['bedrooms'] ?? '—' }}</td>
          <td>{{ $property->listing_visibility === 'public' ? 'Public' : 'Private' }}</td>
          <td><strong>{{ $u['displayLabel'] }}</strong></td>
          <td>{{ $property->country_code }}</td>
          <td>{{ $uLease?->tenant?->fullName() ?? '—' }}</td>
          <td>{{ $uLease ? number_format($uLease->rent_minor_units/100,2).' '.$uLease->currency_code : '—' }}</td>
          <td>
            @if($u['leased'])<span class="badge badge-green">Active</span>
            @else<span class="badge badge-grey">Vacant</span>@endif
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @else
  <div class="db-empty" style="padding:28px">
    <p style="margin:0;color:var(--text-mid)">No units configured yet.</p>
  </div>
  @endif
</div>

@else
{{-- ── Single unit or specific unit in block ── --}}
<div class="db-grid-2" style="margin-bottom:16px">
  <div class="db-card">
    <div class="db-card-header"><span class="db-card-title">Property info</span></div>
    <div class="db-card-body">
      <div class="detail-rows">
        @foreach(array_filter([
          $isUnitInBlock ? ['Unit', '#'.$unitSeq.' · '.$unitDisplayLabel] : null,
          ['Status',   $activeLeases->isNotEmpty() ? 'Active' : 'Vacant'],
          ['Country',  $property->country_code],
          ['Currency', $property->currency_code],
          ['Address',  $property->address_line1.', '.$property->city],
          ['Type',     ucfirst($property->type)],
          !$isUnitInBlock ? ['Occupancy', 'Single unit'] : null,
          !$isUnitInBlock ? ['Units', '1'] : null,
          ['Bedrooms', $property->bedrooms ?? '—'],
          ['Sublet', $property->sublet_allowed ? 'Allowed (max '.($property->bedrooms ?? '?').' sub-letters)' : 'Not allowed'],
          $property->sublet_allowed ? ['Sublet approval', $property->sublet_landlord_approval_required ? 'Landlord approves each sub-letter' : 'Auto after background check'] : null,
        ]) as $row)
        <div class="detail-row">
          <span class="detail-label">{{ $row[0] }}</span>
          <span class="detail-value">{{ $row[1] }}</span>
        </div>
        @endforeach
      </div>
    </div>
  </div>

  <div class="db-card">
    <div class="db-card-header"><span class="db-card-title">Master lease</span></div>
    @if($activeLeases->isEmpty())
    <div class="db-empty" style="padding:28px">
      <p>No active lease for this unit.</p>
      <a href="{{ route('leases.create', ['property' => $property, 'unit_seq' => $unitSeq]) }}" class="db-btn db-btn-primary" style="font-size:13px">+ Create lease</a>
    </div>
    @else
    @php $lease = $activeLeases->first(); @endphp
    <div class="db-card-body">
      <div class="detail-rows">
        @foreach([
          ['Unit #', $lease->displayUnitSeq()],
          ['Door / label', $lease->displayUnitLabel()],
          ['Primary lessee', $lease->tenant?->fullName() ?? '—'],
          ['Primary lessee email', $lease->tenant?->email ?? '—'],
          ['Rent', number_format($lease->rent_minor_units / 100, 2).' '.$lease->currency_code],
          ['Due day', \App\Models\Lease::ordinalDay((int) $lease->due_day).' of month'],
          ['Grace period', $lease->grace_period_days.' days after due'],
          ['Late fee', $lease->formattedLateFee()
              ? $lease->formattedLateFee().' from '.\App\Models\Lease::ordinalDay($lease->lateFeeDayOfMonth()).' of month'
              : 'From '.\App\Models\Lease::ordinalDay($lease->lateFeeDayOfMonth()).' of month (amount not set)'],
          ['Start', $lease->start_date->format('d M Y')],
          ['End', $lease->end_date?->format('d M Y') ?? 'Rolling'],
          ['Status', ucfirst($lease->status)],
        ] as [$label, $val])
        <div class="detail-row">
          <span class="detail-label">{{ $label }}</span>
          <span class="detail-value">{{ $val }}</span>
        </div>
        @endforeach
      </div>
      <a href="{{ route('leases.show', $lease) }}" class="db-btn db-btn-ghost" style="margin-top:14px;font-size:12px">View lease →</a>
    </div>
    @endif
  </div>

  @if($property->sublet_allowed)
  <div class="db-card" style="margin-top:16px">
    <div class="db-card-header"><span class="db-card-title">Sub-leases</span></div>
    @if(($activeSubLeases ?? collect())->isEmpty())
      <div class="db-empty" style="padding:24px">
        <p style="margin:0;color:var(--text-mid)">No sub-leases for this unit.</p>
      </div>
    @else
    <div class="db-table-wrap">
      <table class="db-table">
        <thead><tr><th>Label</th><th>Sub-lessee</th><th>Sub-rent</th><th>Start</th><th>Status</th></tr></thead>
        <tbody>
          @foreach($activeSubLeases as $sub)
          <tr>
            <td>{{ $sub->label ?? '—' }}</td>
            <td><strong>{{ $sub->subletter?->fullName() ?? '—' }}</strong><br><span style="font-size:12px;color:var(--text-light)">{{ $sub->subletter?->email }}</span></td>
            <td>{{ number_format($sub->rent_minor_units / 100, 2) }} {{ $sub->currency_code }}</td>
            <td>{{ $sub->start_date->format('d M Y') }}</td>
            <td><span class="badge badge-{{ $sub->status === 'active' ? 'green' : 'gold' }}">{{ ucfirst(str_replace('_', ' ', $sub->status)) }}</span></td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    @endif
  </div>
  @endif
</div>

@php
  $paymentsQuery = $property->leases->flatMap->payments;
  if ($isUnitInBlock) {
      $paymentsQuery = ($activeLeases->first()?->payments ?? collect());
  }
  $payments = $paymentsQuery->sortByDesc('due_date')->take(12);
@endphp
<div class="db-card">
  <div class="db-card-header"><span class="db-card-title">Payment history</span></div>
  @if($payments->isEmpty())
    <div class="db-empty" style="padding:28px"><p>No payments yet.</p></div>
  @else
  <div class="db-table-wrap">
    <table class="db-table">
      <thead><tr><th>Date</th>@if(!$isUnitInBlock)<th>Unit</th>@endif<th>Tenant</th><th>Amount</th><th>Status</th></tr></thead>
      <tbody>
        @foreach($payments as $pay)
        <tr>
          <td>{{ $pay->due_date?->format('d M Y') }}</td>
          @if(!$isUnitInBlock)<td>{{ $pay->lease->displayUnit() }}</td>@endif
          <td>{{ $pay->lease->tenant->first_name ?? '—' }}</td>
          <td><strong>{{ CurrencyDisplay::formatMinor($pay->amount_minor_units, $pay->currency_code) }}</strong></td>
          <td><span class="badge badge-{{ $pay->status === 'success' ? 'green' : ($pay->status === 'failed' ? 'red' : 'gold') }}">{{ ucfirst($pay->status) }}</span></td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @endif
</div>

@endif
