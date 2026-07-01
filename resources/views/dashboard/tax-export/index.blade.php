@extends('dashboard.layout')
@section('page-title', 'Tax Export')
@section('breadcrumb', 'Finance')

@section('content')
@php
  use App\Support\CurrencyDisplay;

  $homeSym = CurrencyDisplay::symbol($homeCurrency);
  $homeDecimals = CurrencyDisplay::decimalPlaces($homeCurrency);
@endphp

<div class="db-stats" style="margin-bottom:28px">
  <div class="db-stat green">
    <div class="db-stat-label">{{ $year }} reportable rent ({{ $homeCurrency }})</div>
    <div class="db-stat-value">{{ $homeSym }}{{ number_format($portfolioTotal / 100, $homeDecimals) }}</div>
    <div class="db-stat-sub">Successful collections · home ledger</div>
  </div>
  <div class="db-stat">
    <div class="db-stat-label">Properties with income</div>
    <div class="db-stat-value">{{ $propertiesWithData }}</div>
    <div class="db-stat-sub">{{ count($summaries) }} in portfolio</div>
  </div>
  <div class="db-stat">
    <div class="db-stat-label">Payments in {{ $year }}</div>
    <div class="db-stat-value">{{ $portfolioPayments }}</div>
    <div class="db-stat-sub">One row per collection</div>
  </div>
  <div class="db-stat terra">
    <div class="db-stat-label">Export format</div>
    <div class="db-stat-value" style="font-size:22px">CSV + PDF</div>
    <div class="db-stat-sub">Per property · CPA-ready</div>
  </div>
</div>

<div class="db-card" style="margin-bottom:24px">
  <div class="db-card-header" style="flex-wrap:wrap;gap:12px">
    <span class="db-card-title">Tax year</span>
    <form method="GET" action="{{ route('tax-export.index') }}" style="display:flex;align-items:center;gap:10px;margin-left:auto">
      <select name="year" class="db-input" style="width:auto;padding:8px 12px">
        @for($y = now()->year; $y >= now()->year - 6; $y--)
          <option value="{{ $y }}" @selected($y === $year)>{{ $y }}</option>
        @endfor
      </select>
      <button type="submit" class="db-btn db-btn-ghost" style="padding:8px 14px">Apply</button>
    </form>
  </div>
  <div class="db-card-body" style="padding-top:0">
    <p style="margin:0;color:var(--text-light);font-size:14px;line-height:1.55">
      Download a complete payment history per property for your CPA or Schedule E filing.
      Reportable amounts use FX rates snapshotted at collection time — the same figures as your
      <a href="{{ route('fx-ledger.index', ['year' => $year]) }}" style="color:var(--terra)">FX Ledger</a>.
      This export is informational; consult a tax professional for filing.
    </p>
  </div>
</div>

<div class="db-card">
  <div class="db-card-header">
    <span class="db-card-title">Properties — FY {{ $year }}</span>
  </div>
  <div class="db-table-wrap">
    <table class="db-table">
      <thead>
        <tr>
          <th>Property</th>
          <th>Local total</th>
          <th>{{ $homeCurrency }} reportable</th>
          <th style="text-align:center">Payments</th>
          <th style="text-align:right">Export</th>
        </tr>
      </thead>
      <tbody>
        @forelse($summaries as $row)
        @php
          $property = $row['property'];
          $flag = config('countries.'.$property->country_code.'.flag', '🏠');
        @endphp
        <tr>
          <td>
            <div class="db-flag-name">
              <span class="db-flag">{{ $flag }}</span>
              <div>
                <div class="db-name">{{ $property->name }}</div>
                <div class="db-sub">{{ $property->city }} · {{ strtoupper($property->currency_code) }}</div>
              </div>
            </div>
          </td>
          <td>
            @if($row['has_payments'])
              {{ CurrencyDisplay::formatMinor($row['totals']['local_minor_units'], $property->currency_code) }}
            @else
              <span style="color:var(--text-light)">—</span>
            @endif
          </td>
          <td>
            @if($row['has_payments'])
              <strong>{{ CurrencyDisplay::formatMinor($row['totals']['home_minor_units'], $homeCurrency) }}</strong>
            @else
              <span style="color:var(--text-light)">No collections</span>
            @endif
          </td>
          <td style="text-align:center">{{ $row['payment_count'] }}</td>
          <td style="text-align:right;white-space:nowrap">
            @if($row['has_payments'])
              <a href="{{ route('tax-export.csv', ['property' => $property, 'year' => $year]) }}" class="db-btn db-btn-ghost" style="font-size:12px;padding:6px 12px;margin-left:4px">CSV</a>
              <a href="{{ route('tax-export.pdf', ['property' => $property, 'year' => $year]) }}" class="db-btn db-btn-primary" style="font-size:12px;padding:6px 12px;margin-left:4px">PDF</a>
            @else
              <span style="color:var(--text-light);font-size:13px">—</span>
            @endif
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="5" style="text-align:center;padding:40px;color:var(--text-light)">Add a property to generate tax exports.</td>
        </tr>
        @endforelse
      </tbody>
      @if(count($summaries) > 0 && $portfolioTotal > 0)
      <tfoot>
        <tr style="font-weight:600;background:var(--cream)">
          <td>Portfolio total</td>
          <td></td>
          <td>{{ CurrencyDisplay::formatMinor($portfolioTotal, $homeCurrency) }}</td>
          <td style="text-align:center">{{ $portfolioPayments }}</td>
          <td></td>
        </tr>
      </tfoot>
      @endif
    </table>
  </div>
</div>

@if($propertiesWithData > 0)
<div class="db-card" style="margin-top:24px">
  <div class="db-card-header">
    <span class="db-card-title">What's included</span>
  </div>
  <div class="db-card-body" style="padding-top:0">
    <ul style="margin:0;padding-left:20px;color:var(--text-mid);font-size:14px;line-height:1.7">
      <li>Every successful rent payment collected in {{ $year }}, sorted by date</li>
      <li>Tenant name, unit label, local amount, snapshotted FX rate, and {{ $homeCurrency }} reportable total</li>
      <li>Processor reference ID for audit trail</li>
      <li>Property address block and annual totals on CSV/PDF cover sections</li>
    </ul>
  </div>
</div>
@endif
@endsection
