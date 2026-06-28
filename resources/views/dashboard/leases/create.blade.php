@extends('dashboard.layout')
@section('page-title', 'New Lease')
@section('breadcrumb', '← '.$property->name)
@push('styles')
@include('partials.rm-account-ui')
@endpush
@section('content')
@php
  $slotUsed = $property->activeLeaseCount();
  $slotMax = $property->unit_capacity;
  $residenceCurrency = strtoupper($property->currency_code);
  $homeCurrency = strtoupper(auth()->user()->home_currency ?? 'USD');
  $rentSym = \App\Support\CurrencyDisplay::symbol($residenceCurrency);
  $rentDecimals = \App\Support\CurrencyDisplay::decimalPlaces($residenceCurrency);
  $rentStep = \App\Support\CurrencyDisplay::amountStep($residenceCurrency);
@endphp
<div style="max-width:640px">
  <div class="db-card">
    <div class="db-card-header">
      <span class="db-card-title">Create {{ $property->isMultiUnit() ? 'unit lease' : 'lease' }} — {{ $property->name }}</span>
    </div>
    <div class="db-card-body">
      <form method="POST" action="{{ route('leases.store',$property) }}" class="db-form">
        @csrf
        @if(isset($unitSeq) && $unitSeq !== null)
          <input type="hidden" name="unit_seq" value="{{ $unitSeq }}">
        @endif
        @if($errors->any())<div class="db-alert db-alert-error">{{ $errors->first() }}</div>@endif
        @if($property->isMultiUnit() && isset($unitSeq) && $unitSeq !== null)
          <div class="db-alert db-alert-success" style="margin-bottom:14px;font-size:13px">
            Creating a lease for <strong>licensed slot #{{ $unitSeq }}</strong>. The door label you enter below should match what you use for this unit in the building.
          </div>
        @elseif($property->isMultiUnit() && $slotMax)
          <div class="db-alert db-alert-success" style="margin-bottom:14px;font-size:13px">
            Licensed slots: <strong>{{ $slotUsed }} / {{ $slotMax }}</strong> in use. The next lease gets internal unit <strong>#{{ $property->nextUnitSeq() }}</strong> automatically.
          </div>
        @elseif($property->isMultiUnit())
          <div class="db-alert db-alert-error" style="margin-bottom:14px;font-size:13px">Set <strong>licensed unit slots</strong> on the property (Edit) before adding leases.</div>
        @else
          <div class="db-alert db-alert-success" style="margin-bottom:14px;font-size:13px">Single-unit property: the whole building is one lease. Unit # and door label show as <strong>—</strong> in the app (internal slot 0 is not shown).</div>
        @endif
        @if($property->isMultiUnit())
        <div class="db-form-group">
          <label>Unit label (door / apt) <span class="req">*</span></label>
          <input type="text" name="unit_label" class="db-input" placeholder="e.g. 265, 388, 4B" value="{{ old('unit_label', $prefillLabel ?? '') }}" required maxlength="64">
          <span class="db-form-hint">Shown to tenants and on maintenance. Internal unit # is assigned automatically.</span>
        </div>
        @endif
        <div class="db-form-group">
          <label>Tenant email <span class="req">*</span></label>
          <input type="email" name="tenant_email" class="db-input" placeholder="tenant@example.com" value="{{ old('tenant_email') }}" required>
          <span class="db-form-hint">We'll send them an invite to set up their payment mandate.</span>
        </div>
        @if($property->rent_minor_units)
          <div class="db-alert db-alert-success" style="margin-bottom:14px;font-size:13px">
            <strong>From property Rent tab:</strong>
            {{ \App\Support\CurrencyDisplay::formatMinor($property->rent_minor_units, $landlordCurrency) }}/mo total
            @if($property->base_rent_minor_units)
              — base {{ \App\Support\CurrencyDisplay::formatMinor($property->base_rent_minor_units, $landlordCurrency) }}
              @foreach($property->normalizedRentChargeLines() as $line)
                @if($line['amount_minor_units'] > 0)
                  + {{ $line['label'] }} {{ \App\Support\CurrencyDisplay::formatMinor($line['amount_minor_units'], $landlordCurrency) }}
                @endif
              @endforeach
            @endif
          </div>
        @endif
        <div class="db-form-row">
          <div class="db-form-group">
            <label>Total monthly rent ({{ $residenceCurrency }}) <span class="req">*</span></label>
            <div style="display:flex;align-items:center;gap:6px">
              <span style="font-weight:600;color:var(--text-mid)">{{ $rentSym }}</span>
              <input type="number" name="rent_amount" class="db-input" placeholder="{{ $rentDecimals === 0 ? 'e.g. 150000' : 'e.g. 1500.00' }}" min="{{ $rentDecimals === 0 ? '1' : '0.01' }}" step="{{ $rentStep }}" value="{{ old('rent_amount', isset($defaultRentAmount) ? number_format($defaultRentAmount, $rentDecimals, '.', '') : '') }}" required style="flex:1">
            </div>
            <span class="db-form-hint">Tenant pays in {{ $residenceCurrency }} (property country). @if($residenceCurrency !== $homeCurrency)FX ledger &amp; reports use {{ $homeCurrency }}.@endif</span>
          </div>
          <div class="db-form-group">
            <label>Due day of month <span class="req">*</span></label>
            <select name="due_day" class="db-select" required>
              @for($i=1;$i<=28;$i++)
                <option value="{{ $i }}" {{ old('due_day',1)==$i?'selected':'' }}>{{ $i }}{{ match(true){$i===1=>'st',$i===2=>'nd',$i===3=>'rd',default=>'th'} }}</option>
              @endfor
            </select>
          </div>
        </div>
        <div class="db-form-row">
          <div class="db-form-group">
            <label>Start date <span class="req">*</span></label>
            <input type="date" name="start_date" class="db-input" value="{{ old('start_date', now()->format('Y-m-d')) }}" required>
          </div>
          <div class="db-form-group">
            <label>End date</label>
            <input type="date" name="end_date" class="db-input" value="{{ old('end_date') }}">
            <span class="db-form-hint">Leave blank for rolling tenancy</span>
          </div>
        </div>
        <div class="db-form-row">
          <div class="db-form-group">
            <label>Deposit ({{ $property->currency_code }})</label>
            <input type="number" name="deposit_amount" class="db-input" placeholder="Optional" min="0" value="{{ old('deposit_amount') }}">
          </div>
          <div class="db-form-group">
            <label>Grace period (days after due)</label>
            <input type="number" name="grace_period_days" class="db-input" value="{{ old('grace_period_days',5) }}" min="0" max="30">
            <span class="db-form-hint">e.g. due 1st + 5 days grace → late fee from 6th</span>
          </div>
        </div>
        <div class="db-form-group">
          <label>Late fee ({{ $property->currency_code }})</label>
          <input type="number" name="late_fee_amount" class="db-input" placeholder="Optional flat fee per month" min="0" step="0.01" value="{{ old('late_fee_amount') }}">
          <span class="db-form-hint">Applied from the day after grace ends (due day + grace days).</span>
        </div>
        @if($landlord->businessEntityReadyForLease())
          <div class="db-form-group" style="margin-top:8px">
            <label class="rm-acc-check-row" style="margin-bottom:0">
              <input type="hidden" name="use_business_entity" value="0">
              <input type="checkbox" name="use_business_entity" value="1"
                @checked(old('use_business_entity', $landlord->use_business_entity_in_lease))>
              <span class="rm-acc-check-text">
                <span class="rm-acc-check-title">Use business entity in lease</span>
                <span class="rm-acc-check-meta">{{ $landlord->business_legal_name }}</span>
              </span>
            </label>
          </div>
        @else
          <p class="db-form-hint" style="margin:12px 0 0">
            Leases list you as <strong>{{ $landlord->leasePartyName(false) }}</strong>.
            <a href="{{ route('landlord.account', ['tab' => 'business']) }}" class="db-table-link">Add business entity</a> to sign as a company.
          </p>
        @endif
        <button type="submit" class="db-form-submit">Create lease & invite tenant →</button>
      </form>
    </div>
  </div>
</div>
@endsection
