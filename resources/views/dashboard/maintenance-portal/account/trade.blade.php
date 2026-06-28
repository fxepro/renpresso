@php
  $tradeSection = $complianceSections['operations'] ?? null;
  $tradeDocTypes = array_keys($tradeSection['documents'] ?? []);
  $tradeSec = $tradeSec ?? ($tradeDocTypes[0] ?? 'trade_licence');
  $tradeItems = [];
  foreach ($tradeSection['documents'] ?? [] as $docType => $docDef) {
    $hasFile = ($documentsByType[$docType] ?? null)?->file_path ? 1 : 0;
    $tradeItems[$docType] = [
      'label' => str_replace(' insurance', '', explode(' ', $docDef['label'])[0] ?? $docDef['label']),
      'count' => $hasFile,
    ];
  }
@endphp
<p class="db-form-hint" style="margin:0 0 16px;max-width:52rem;line-height:1.55">Trade licences, liability cover, and background checks before working on occupied properties.</p>

@if(! $team)
  <div class="db-alert" style="background:var(--gold-pale);color:var(--gold)">Set up your company profile first.</div>
@elseif($tradeSection)
  @include('dashboard.maintenance-portal.account.partials.sub-nav', [
    'tab' => 'trade',
    'activeKey' => $tradeSec,
    'items' => collect($tradeSection['documents'])->mapWithKeys(fn ($doc, $key) => [
      $key => [
        'label' => $doc['label'],
        'count' => ($documentsByType[$key] ?? null)?->file_path ? 1 : 0,
      ],
    ])->all(),
    'ariaLabel' => 'Trade & insurance documents',
  ])
  <p class="db-form-hint" style="margin-bottom:12px">{{ $tradeSection['lead'] }}</p>
  @foreach($tradeSection['documents'] as $docType => $docDef)
    @if($tradeSec === $docType)
      @include('dashboard.maintenance-portal.account.partials.document-card', [
        'docType' => $docType,
        'docDef' => $docDef,
        'documentsByType' => $documentsByType,
        'accountTab' => 'trade',
        'accountSec' => $docType,
      ])
    @endif
  @endforeach
@endif
