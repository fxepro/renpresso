@php
  $banksSec = $banksSec ?? 'accounts';
@endphp

<p class="db-form-hint" style="margin:0 0 16px;max-width:52rem;line-height:1.55">
  Rent is collected in the <strong>property country currency</strong>. Outside <strong>US/CA</strong>, a <strong>local collection</strong> account is required. Move funds to your <strong>home country</strong> yourself and log repatriation in the FX ledger. Values are <strong>encrypted at rest</strong>.
</p>

@include('dashboard.landlord.account.partials.sub-nav', [
  'tab' => 'banks',
  'activeSec' => $banksSec,
  'ariaLabel' => 'Bank account sections',
  'items' => [
    'accounts' => 'Saved accounts',
    'add'      => 'Add account',
  ],
])

@if($banksSec === 'add')
  @include('dashboard.landlord.account.banks-add')
@else
  @include('dashboard.landlord.account.banks-accounts')
@endif
