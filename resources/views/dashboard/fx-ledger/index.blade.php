@extends('dashboard.layout')
@section('page-title', 'FX Ledger')
@section('breadcrumb', 'Finance')

@section('content')
@php
  use App\Support\CurrencyDisplay;

  $homeSym = CurrencyDisplay::symbol($homeCurrency);
  $homeDecimals = CurrencyDisplay::decimalPlaces($homeCurrency);
  $monthLabel = \Carbon\Carbon::createFromDate($year, $month, 1)->format('F Y');
  $unrepatriated = max(0, $yearTotal - $repatriatedYear);
@endphp

<div class="db-stats" style="margin-bottom:28px">
  <div class="db-stat green">
    <div class="db-stat-label">{{ $monthLabel }} ({{ $homeCurrency }} ledger)</div>
    <div class="db-stat-value">{{ $homeSym }}{{ number_format($monthTotal / 100, $homeDecimals) }}</div>
    <div class="db-stat-sub">Rent collected · snapshotted FX</div>
  </div>
  <div class="db-stat">
    <div class="db-stat-label">{{ $year }} year-to-date</div>
    <div class="db-stat-value">{{ $homeSym }}{{ number_format($yearTotal / 100, $homeDecimals) }}</div>
    <div class="db-stat-sub">All successful rent payments</div>
  </div>
  <div class="db-stat terra">
    <div class="db-stat-label">Repatriated in {{ $year }}</div>
    <div class="db-stat-value">{{ $homeSym }}{{ number_format($repatriatedYear / 100, $homeDecimals) }}</div>
    <div class="db-stat-sub">Manual transfer log</div>
  </div>
  <div class="db-stat">
    <div class="db-stat-label">Cross-border properties</div>
    <div class="db-stat-value">{{ $crossBorderProperties->count() }}</div>
    <div class="db-stat-sub">Home: {{ $homeCountry }} · {{ $homeCurrency }}</div>
  </div>
</div>

<div class="db-card" style="margin-bottom:24px">
  <div class="db-card-header" style="flex-wrap:wrap;gap:12px">
    <span class="db-card-title">Period</span>
    <form method="GET" action="{{ route('fx-ledger.index') }}" style="display:flex;align-items:center;gap:10px;margin-left:auto">
      <select name="month" class="db-input" style="width:auto;padding:8px 12px">
        @for($m = 1; $m <= 12; $m++)
          <option value="{{ $m }}" @selected($m === $month)>{{ \Carbon\Carbon::createFromDate($year, $m, 1)->format('F') }}</option>
        @endfor
      </select>
      <select name="year" class="db-input" style="width:auto;padding:8px 12px">
        @for($y = now()->year; $y >= now()->year - 5; $y--)
          <option value="{{ $y }}" @selected($y === $year)>{{ $y }}</option>
        @endfor
      </select>
      <button type="submit" class="db-btn db-btn-ghost" style="padding:8px 14px">Apply</button>
    </form>
  </div>
  <div class="db-card-body" style="padding-top:0">
    <p style="margin:0 0 16px;color:var(--text-light);font-size:14px;line-height:1.5">
      Amounts in your home currency use FX rates snapshotted at payment time — historical totals never recalculate when rates move.
      @if($unrepatriated > 0)
        <span style="display:block;margin-top:6px">Collected in {{ $year }} minus logged repatriations: <strong>{{ $homeSym }}{{ number_format($unrepatriated / 100, $homeDecimals) }}</strong> (informational — not a bank balance).</span>
      @endif
    </p>
    <div class="db-table-wrap">
      <table class="db-table">
        <thead>
          <tr>
            <th>Property</th>
            <th>Local currency</th>
            <th>Collected (local)</th>
            <th>{{ $homeCurrency }} ledger</th>
            <th style="text-align:center">Payments</th>
          </tr>
        </thead>
        <tbody>
          @forelse($monthlySummary as $row)
          @php
            $property = $properties->firstWhere('id', $row['property_id']);
            $flag = $property ? config('countries.'.$property->country_code.'.flag', '🏠') : '🏠';
          @endphp
          <tr>
            <td>
              <div class="db-flag-name">
                <span class="db-flag">{{ $flag }}</span>
                <div>
                  <div class="db-name">{{ $row['property_name'] }}</div>
                  @if($property)
                    <div class="db-sub">{{ $property->city }}</div>
                  @endif
                </div>
              </div>
            </td>
            <td>{{ strtoupper($row['currency_code']) }}</td>
            <td><strong>{{ CurrencyDisplay::formatMinor($row['total_local_minor_units'], $row['currency_code']) }}</strong></td>
            <td>{{ CurrencyDisplay::formatMinor($row['total_home_minor_units'], $row['home_currency_code']) }}</td>
            <td style="text-align:center">{{ $row['payment_count'] }}</td>
          </tr>
          @empty
          <tr>
            <td colspan="5" style="text-align:center;padding:32px;color:var(--text-light)">No successful rent payments for {{ $monthLabel }}.</td>
          </tr>
          @endforelse
        </tbody>
        @if(count($monthlySummary) > 0)
        <tfoot>
          <tr style="font-weight:600;background:var(--cream)">
            <td colspan="3" style="text-align:right">Month total ({{ $homeCurrency }})</td>
            <td>{{ CurrencyDisplay::formatMinor($monthTotal, $homeCurrency) }}</td>
            <td></td>
          </tr>
        </tfoot>
        @endif
      </table>
    </div>
  </div>
</div>

<div class="db-grid-2">
  <div class="db-card">
    <div class="db-card-header">
      <span class="db-card-title">Rent payments · {{ $monthLabel }}</span>
      <a href="{{ route('payments.index', ['tab' => 'rent']) }}" class="db-btn db-btn-ghost" style="font-size:12px;padding:5px 10px">All payments</a>
    </div>
    <div class="db-table-wrap">
      <table class="db-table">
        <thead>
          <tr>
            <th>Property</th>
            <th>Local</th>
            <th>Rate</th>
            <th>{{ $homeCurrency }}</th>
            <th>Collected</th>
          </tr>
        </thead>
        <tbody>
          @forelse($fxPayments as $pay)
          <tr>
            <td>
              <div class="db-name">{{ $pay->lease->property->name }}</div>
              <div class="db-sub">{{ $pay->lease->tenant->first_name ?? '—' }}</div>
            </td>
            <td>{{ CurrencyDisplay::formatMinor($pay->amount_minor_units, $pay->currency_code) }}</td>
            <td style="color:var(--text-light);font-size:13px">{{ number_format($pay->fxRate(), 4) }}</td>
            <td><strong>{{ CurrencyDisplay::formatMinor($pay->home_amount_minor_units, $pay->home_currency_code) }}</strong></td>
            <td>{{ $pay->collected_at?->format('d M Y') ?? '—' }}</td>
          </tr>
          @empty
          <tr>
            <td colspan="5" style="text-align:center;padding:32px;color:var(--text-light)">No payments this month.</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    @if($fxPayments->hasPages())
    <div style="padding:16px 20px;border-top:1px solid var(--cream-dark)">
      {{ $fxPayments->links() }}
    </div>
    @endif
  </div>

  <div class="db-card">
    <div class="db-card-header">
      <span class="db-card-title">Log repatriation</span>
    </div>
    <div class="db-card-body">
      @if($crossBorderProperties->isEmpty())
        <p style="color:var(--text-light);margin:0">All properties use your home country and currency. Repatriation logging is for cross-border portfolios.</p>
      @else
        <form method="POST" action="{{ route('fx-ledger.repatriation.store') }}" class="db-form">
          @csrf
          <input type="hidden" name="year" value="{{ $year }}">
          <input type="hidden" name="month" value="{{ $month }}">

          <div class="db-form-group">
            <label for="property_id">Property</label>
            <select name="property_id" id="property_id" class="db-input" required>
              <option value="">Select property</option>
              @foreach($crossBorderProperties as $property)
                <option value="{{ $property->id }}" @selected(old('property_id') === $property->id)>
                  {{ $property->name }} ({{ strtoupper($property->currency_code) }})
                </option>
              @endforeach
            </select>
            @error('property_id')<span class="db-field-error">{{ $message }}</span>@enderror
          </div>

          <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
            <div class="db-form-group">
              <label for="amount">Amount sent (local)</label>
              <input type="number" name="amount" id="amount" class="db-input" step="0.01" min="0.01" value="{{ old('amount') }}" required placeholder="e.g. 50000">
              @error('amount')<span class="db-field-error">{{ $message }}</span>@enderror
            </div>
            <div class="db-form-group">
              <label for="home_amount">Received ({{ $homeCurrency }})</label>
              <input type="number" name="home_amount" id="home_amount" class="db-input" step="0.01" min="0.01" value="{{ old('home_amount') }}" required placeholder="e.g. 3200">
              @error('home_amount')<span class="db-field-error">{{ $message }}</span>@enderror
            </div>
          </div>

          <div class="db-form-group">
            <label for="repatriated_on">Transfer date</label>
            <input type="date" name="repatriated_on" id="repatriated_on" class="db-input" value="{{ old('repatriated_on', now()->toDateString()) }}" max="{{ now()->toDateString() }}" required>
            @error('repatriated_on')<span class="db-field-error">{{ $message }}</span>@enderror
          </div>

          <div class="db-form-group">
            <label for="notes">Notes <span style="color:var(--text-light);font-weight:400">(optional)</span></label>
            <textarea name="notes" id="notes" class="db-input" rows="2" placeholder="Bank reference, wire ID, etc.">{{ old('notes') }}</textarea>
            @error('notes')<span class="db-field-error">{{ $message }}</span>@enderror
          </div>

          <button type="submit" class="db-btn db-btn-primary">Save repatriation</button>
        </form>
      @endif
    </div>
  </div>
</div>

<div class="db-card" style="margin-top:24px">
  <div class="db-card-header">
    <span class="db-card-title">Repatriation history</span>
  </div>
  <div class="db-table-wrap">
    <table class="db-table">
      <thead>
        <tr>
          <th>Date</th>
          <th>Property</th>
          <th>Sent (local)</th>
          <th>Rate</th>
          <th>Received ({{ $homeCurrency }})</th>
          <th>Notes</th>
        </tr>
      </thead>
      <tbody>
        @forelse($repatriations as $log)
        <tr>
          <td>{{ $log->repatriated_on->format('d M Y') }}</td>
          <td>{{ $log->property->name ?? '—' }}</td>
          <td>{{ CurrencyDisplay::formatMinor($log->amount_minor_units, $log->currency_code) }}</td>
          <td style="color:var(--text-light);font-size:13px">{{ number_format($log->fx_rate_snapshot / 1_000_000, 4) }}</td>
          <td><strong>{{ CurrencyDisplay::formatMinor($log->home_amount_minor_units, $log->home_currency_code) }}</strong></td>
          <td style="color:var(--text-light);max-width:200px">{{ $log->notes ?: '—' }}</td>
        </tr>
        @empty
        <tr>
          <td colspan="6" style="text-align:center;padding:32px;color:var(--text-light)">No repatriations logged yet.</td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($repatriations->hasPages())
  <div style="padding:16px 20px;border-top:1px solid var(--cream-dark)">
    {{ $repatriations->links() }}
  </div>
  @endif
</div>
@endsection
