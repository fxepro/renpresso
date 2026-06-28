@extends('dashboard.layout')
@section('page-title', $lease->property->name)
@section('breadcrumb', '← Leases')
@section('content')
<div class="db-grid-2" style="margin-bottom:16px">
  <div class="db-card">
    <div class="db-card-header"><span class="db-card-title">Lease details</span></div>
    <div class="db-card-body">
      <table style="width:100%;font-size:13px;border-collapse:collapse">
        @foreach([
          ['Landlord (lease)', $lease->leaseLandlordName().($lease->resolvesUseBusinessEntity() ? ' (business)' : '')],
          ['Property',   $lease->property->name],
          ['Unit #',     $lease->displayUnitSeq()],
          ['Door / label', $lease->displayUnitLabel()],
          ['Tenant',     $lease->tenant->first_name.' '.$lease->tenant->last_name],
          ['Email',      $lease->tenant->email],
          ['Rent',       number_format($lease->rent_minor_units/100,2).' '.$lease->currency_code.'/mo'],
          ['Due day',    \App\Models\Lease::ordinalDay((int) $lease->due_day).' of month'],
          ['Grace period', $lease->grace_period_days.' days after due date'],
          ['Late fee',   $lease->formattedLateFee()
              ? $lease->formattedLateFee().' from '.\App\Models\Lease::ordinalDay($lease->lateFeeDayOfMonth()).' of month'
              : 'From '.\App\Models\Lease::ordinalDay($lease->lateFeeDayOfMonth()).' of month (amount not set)'],
          ['Start',      $lease->start_date->format('d M Y')],
          ['End',        $lease->end_date?->format('d M Y') ?? 'Rolling'],
          ['Status',     ucfirst($lease->status)],
        ] as [$l,$v])
        <tr style="border-bottom:1px solid var(--cream-dark)">
          <td style="padding:10px 0;color:var(--text-light);width:38%">{{ $l }}</td>
          <td style="padding:10px 0;font-weight:500;color:var(--text-dark)">{{ $v }}</td>
        </tr>
        @endforeach
      </table>
    </div>
  </div>
  <div class="db-card">
    <div class="db-card-header"><span class="db-card-title">Payment mandate</span></div>
    @php $mandate = $lease->mandates->where('status','active')->first() ?? $lease->mandates->first(); @endphp
    <div class="db-card-body">
      @if($mandate)
      <table style="width:100%;font-size:13px;border-collapse:collapse">
        @foreach([
          ['Method',    ucfirst($mandate->payment_method_type ?? config('countries.'.$lease->property->country_code.'.method','—'))],
          ['Processor', ucfirst($mandate->processor_slug)],
          ['Status',    ucfirst($mandate->status)],
          ['Authorised',$mandate->authorised_at?->format('d M Y') ?? '—'],
        ] as [$l,$v])
        <tr style="border-bottom:1px solid var(--cream-dark)">
          <td style="padding:10px 0;color:var(--text-light);width:38%">{{ $l }}</td>
          <td style="padding:10px 0;font-weight:500;color:var(--text-dark)">{{ $v }}</td>
        </tr>
        @endforeach
      </table>
      @else
        <div class="db-empty" style="padding:20px"><p>No mandate yet. Tenant invite pending.</p></div>
      @endif
    </div>
  </div>
</div>

<!-- Payments -->
<div class="db-card">
  <div class="db-card-header"><span class="db-card-title">Payment history</span></div>
  @if($lease->payments->isEmpty())
    <div class="db-empty" style="padding:28px"><p>No payments yet.</p></div>
  @else
  <div class="db-table-wrap">
    <table class="db-table">
      <thead><tr><th>Due date</th><th>Amount</th><th>Home equiv.</th><th>FX rate</th><th>Collected</th><th>Status</th></tr></thead>
      <tbody>
        @foreach($lease->payments->sortByDesc('due_date') as $pay)
        <tr>
          <td>{{ $pay->due_date?->format('d M Y') }}</td>
          <td><strong>{{ number_format($pay->amount_minor_units/100,2) }} {{ $pay->currency_code }}</strong></td>
          <td>{{ number_format($pay->home_amount_minor_units/100,2) }} {{ $pay->home_currency_code }}</td>
          <td>{{ number_format($pay->fx_rate_snapshot/1000000,4) }}</td>
          <td>{{ $pay->collected_at?->format('d M Y') ?? '—' }}</td>
          <td><span class="badge badge-{{ $pay->status==='success'?'green':($pay->status==='failed'?'red':'gold') }}">{{ ucfirst($pay->status) }}</span></td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @endif
</div>
@endsection
