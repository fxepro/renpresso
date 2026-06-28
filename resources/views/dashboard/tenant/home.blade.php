@extends('dashboard.layout')
@section('page-title', 'Details & lease')
@section('content')
@if(! $lease)
<div class="db-empty" style="min-height:60vh">
  <div class="db-empty-icon">🏠</div>
  <h3>No active lease</h3>
  <p>Your lease details will appear here once your landlord activates your tenancy.</p>
</div>
@else
@php $p = $lease->property; $landlord = $p->landlord; @endphp
<div class="db-grid-2">
  <div class="db-card">
    <div class="db-card-header"><span class="db-card-title">Property</span></div>
    <div class="db-card-body">
      <table style="width:100%;font-size:var(--fs-body);border-collapse:collapse">
        @foreach([
          ['Name', $p->name],
          ['Address', trim($p->address_line1.' '.($p->address_line2 ?? ''))],
          ['City', $p->city.($p->state_province ? ', '.$p->state_province : '')],
          ['Postal', $p->postal_code ?? '—'],
          ['Country', config('countries.'.$p->country_code.'.name', $p->country_code)],
          ['Unit', $lease->displayUnit()],
        ] as [$label, $value])
        <tr style="border-bottom:1px solid var(--cream-dark)">
          <td style="padding:10px 0;color:var(--text-light);width:36%">{{ $label }}</td>
          <td style="padding:10px 0;font-weight:500">{{ $value }}</td>
        </tr>
        @endforeach
      </table>
    </div>
  </div>
  <div class="db-card">
    <div class="db-card-header"><span class="db-card-title">Lease</span></div>
    <div class="db-card-body">
      <table style="width:100%;font-size:var(--fs-body);border-collapse:collapse">
        @foreach([
          ['Rent', number_format($lease->rent_minor_units /100,2).' '.$lease->currency_code.'/mo'],
          ['Due day', \App\Models\Lease::ordinalDay((int) $lease->due_day).' of month'],
          ['Grace period', $lease->grace_period_days.' days after due'],
          ['Late fee', $lease->formattedLateFee()
              ? $lease->formattedLateFee().' from '.\App\Models\Lease::ordinalDay($lease->lateFeeDayOfMonth()).' of month'
              : 'From '.\App\Models\Lease::ordinalDay($lease->lateFeeDayOfMonth()).' of month'],
          ['Start', $lease->start_date->format('d M Y')],
          ['End', $lease->end_date?->format('d M Y') ?? 'Rolling'],
          ['Status', ucfirst($lease->status)],
          ['Deposit', $lease->deposit_minor_units ? number_format($lease->deposit_minor_units /100,2).' '.$lease->currency_code : '—'],
        ] as [$label, $value])
        <tr style="border-bottom:1px solid var(--cream-dark)">
          <td style="padding:10px 0;color:var(--text-light);width:36%">{{ $label }}</td>
          <td style="padding:10px 0;font-weight:500">{{ $value }}</td>
        </tr>
        @endforeach
      </table>
    </div>
  </div>
</div>
@if($landlord)
<div class="db-card" style="margin-top:18px">
  <div class="db-card-header"><span class="db-card-title">Landlord / property manager</span></div>
  <div class="db-card-body">
    <p style="font-weight:500">{{ $landlord->fullName() }}</p>
    <p style="color:var(--text-light);margin-top:4px">{{ $landlord->email }}</p>
    @if($landlord->phone)
      <p style="color:var(--text-light);margin-top:4px">{{ $landlord->phone }}</p>
    @endif
    <a href="{{ route('messages.index') }}" class="db-btn db-btn-primary" style="margin-top:16px;text-decoration:none">Message landlord</a>
  </div>
</div>
@endif
@endif
@endsection
