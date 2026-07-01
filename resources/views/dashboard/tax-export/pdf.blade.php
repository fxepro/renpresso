@php
  use App\Support\CurrencyDisplay;

  $property = $report['property'];
  $landlord = $report['landlord'];
  $homeCurrency = $report['home_currency'];
  $localCurrency = strtoupper($property->currency_code);
  $homeDecimals = CurrencyDisplay::decimalPlaces($homeCurrency);
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Tax export — {{ $property->name }} — {{ $report['year'] }}</title>
<style>
  * { box-sizing: border-box; }
  body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #0D1F35; margin: 36px 40px; line-height: 1.45; }
  h1 { font-size: 18px; margin: 0 0 4px; }
  .meta { color: #4A5A6A; font-size: 10px; margin-bottom: 22px; }
  .block { margin-bottom: 16px; }
  .label { font-size: 9px; text-transform: uppercase; letter-spacing: 0.06em; color: #8A99AA; margin-bottom: 2px; }
  table { width: 100%; border-collapse: collapse; margin-top: 8px; }
  th { text-align: left; font-size: 9px; text-transform: uppercase; letter-spacing: 0.04em; color: #4A5A6A; border-bottom: 1px solid #F0EDE4; padding: 8px 6px; }
  td { padding: 7px 6px; border-bottom: 1px solid #FAF8F3; vertical-align: top; }
  td.num { text-align: right; white-space: nowrap; }
  tfoot td { font-weight: bold; border-top: 2px solid #F0EDE4; border-bottom: none; padding-top: 10px; }
  .footnote { margin-top: 20px; font-size: 9px; color: #8A99AA; }
  .brand { color: #C4622D; font-weight: bold; }
</style>
</head>
<body>
  <h1>Annual rent income report</h1>
  <div class="meta">
    <span class="brand">Renpresso</span> · Tax year {{ $report['year'] }} · Generated {{ $report['generated_at']->format('d M Y H:i') }}
  </div>

  <div class="block">
    <div class="label">Landlord</div>
    <div>{{ $landlord->leasePartyName(false) }}</div>
  </div>

  <div class="block">
    <div class="label">Property</div>
    <div><strong>{{ $property->name }}</strong></div>
    <div>{{ $property->address_line1 }}</div>
    <div>{{ trim(implode(', ', array_filter([$property->city, $property->state_province, $property->postal_code]))) }}</div>
    <div>{{ $property->country_code }}</div>
  </div>

  <div class="block">
    <div class="label">Reportable currency (home ledger)</div>
    <div>{{ $homeCurrency }}</div>
  </div>

  <table>
    <thead>
      <tr>
        <th>Date</th>
        <th>Tenant</th>
        <th>Unit</th>
        <th class="num">Local</th>
        <th class="num">FX</th>
        <th class="num">Reportable</th>
        <th>Ref</th>
      </tr>
    </thead>
    <tbody>
      @forelse($report['payments'] as $row)
      <tr>
        <td>{{ $row['date'] }}</td>
        <td>{{ $row['tenant_name'] ?: '—' }}</td>
        <td>{{ $row['unit_label'] ?? '—' }}</td>
        <td class="num">{{ CurrencyDisplay::formatMinor($row['amount_minor_units'], $row['currency_code']) }}</td>
        <td class="num">{{ number_format($row['fx_rate'], 4) }}</td>
        <td class="num">{{ CurrencyDisplay::formatMinor($row['home_amount_minor_units'], $row['home_currency_code']) }}</td>
        <td style="font-size:9px;color:#8A99AA">{{ \Illuminate\Support\Str::limit($row['processor_ref'] ?? '', 18) }}</td>
      </tr>
      @empty
      <tr>
        <td colspan="7" style="text-align:center;padding:24px;color:#8A99AA">No successful rent payments in {{ $report['year'] }}.</td>
      </tr>
      @endforelse
    </tbody>
    @if($report['payment_count'] > 0)
    <tfoot>
      <tr>
        <td colspan="3">Annual totals ({{ $report['payment_count'] }} payment{{ $report['payment_count'] === 1 ? '' : 's' }})</td>
        <td class="num">{{ CurrencyDisplay::formatMinor($report['totals']['local_minor_units'], $localCurrency) }}</td>
        <td></td>
        <td class="num">{{ CurrencyDisplay::formatMinor($report['totals']['home_minor_units'], $homeCurrency) }}</td>
        <td></td>
      </tr>
    </tfoot>
    @endif
  </table>

  <p class="footnote">
    Reportable amounts use FX rates snapshotted at payment time and match your Renpresso FX Ledger.
    Intended for Schedule E / CPA review — not a substitute for professional tax advice.
  </p>
</body>
</html>
