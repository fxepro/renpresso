@extends('admin.layout')
@section('title', 'Markets & pricing')
@section('page-title', 'Markets & pricing')
@section('breadcrumb', 'Settings')
@section('content')
<p class="admin-portal-note">Per-country <strong>processor (company)</strong>, platform fees, and disc %. Billing currency is local. US/CA/EU payment methods are under <a href="{{ route('admin.settings.payments') }}" class="db-table-link">Payments</a>.</p>

@include('admin.partials.amount-note')

<div class="db-card" style="margin-bottom:18px">
  <div class="db-card-body" style="display:flex;flex-wrap:wrap;gap:12px;align-items:center">
    <span style="color:var(--text-mid);font-size:14px">Bulk apply signup/monthly/disc × tier:</span>
    @foreach(['standard' => '1.0×', 'emerging' => '0.55×', 'frontier' => '0.35×'] as $tier => $label)
    <form method="POST" action="{{ route('admin.settings.markets.apply-defaults') }}" style="display:inline" onsubmit="return confirm('Apply {{ $label }} to all {{ $tier }} markets?')">
      @csrf
      <input type="hidden" name="pricing_tier" value="{{ $tier }}">
      <button type="submit" class="db-btn db-btn-ghost">{{ ucfirst($tier) }} {{ $label }}</button>
    </form>
    @endforeach
  </div>
</div>

<div class="db-card">
  <div class="db-card-header">
    <span class="db-card-title">Country markets ({{ $markets->count() }})</span>
  </div>
  <div class="db-card-body" style="padding:0">
    <div class="db-table-wrap admin-markets-wrap">
      <table class="db-table admin-markets-table">
        <thead>
          <tr>
            <th class="mk-col-chev"></th>
            <th class="mk-col-country">Country</th>
            <th class="mk-col-tier">Tier</th>
            <th class="mk-col-currency">Currency</th>
            <th class="mk-col-processor">Processor</th>
            <th class="mk-col-fee">Signup</th>
            <th class="mk-col-fee">Monthly</th>
            <th class="mk-col-disc">Disc %</th>
            <th class="mk-col-active">Active</th>
          </tr>
        </thead>
        <tbody>
          @foreach($markets as $market)
          @php
            $rowId = 'market-row-'.$market->country_code;
            $proc = $processorsBySlug->get($market->rent_processor_slug);
            $procLabel = $proc?->name ?? ($market->rent_processor_slug ?: '—');
          @endphp
          <tr class="admin-market-summary-row">
            <td class="mk-col-chev admin-market-chev-cell">
              <input type="checkbox" id="{{ $rowId }}" class="admin-market-expand-input" hidden>
              <label for="{{ $rowId }}" class="admin-market-chev" title="Edit {{ $market->country_code }}">▸</label>
            </td>
            <td class="mk-col-country"><strong>{{ $market->country_code }}</strong></td>
            <td class="mk-col-tier"><span class="badge badge-navy">{{ $market->pricing_tier }}</span></td>
            <td class="mk-col-currency">{{ $market->billing_currency }}</td>
            <td class="mk-col-processor">{{ $procLabel }}</td>
            <td class="mk-col-fee">{{ $market->formatSignupFee() }}</td>
            <td class="mk-col-fee">{{ $market->formatMonthlyFee() }}</td>
            <td class="mk-col-disc">{{ $market->maintenanceCommissionPercent() }}</td>
            <td class="mk-col-active">{{ $market->is_active ? 'Yes' : 'No' }}</td>
          </tr>
          <tr class="admin-market-edit-row">
            <td colspan="9">
              <div class="admin-market-edit-panel">
                <form method="POST" action="{{ route('admin.settings.markets.update', $market) }}" class="admin-market-edit-form">
                  @csrf
                  @method('PATCH')
                  <div class="db-form-group">
                    <label>Processor (default)</label>
                    <select name="rent_processor_slug" class="db-select" required>
                      @foreach($processors as $p)
                        <option value="{{ $p->slug }}" @selected($market->rent_processor_slug === $p->slug)>{{ $p->name }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="db-form-group">
                    <label>Tier</label>
                    <select name="pricing_tier" class="db-select">
                      @foreach(\App\Models\CountryMarket::TIERS as $t)
                        <option value="{{ $t }}" @selected($market->pricing_tier === $t)>{{ ucfirst($t) }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="db-form-group">
                    <label>Signup / unit ({{ $market->billing_currency }})</label>
                    <input type="number" name="signup_fee" class="db-input" min="0" step="0.01" value="{{ number_format($market->signup_fee_minor_per_unit / 100, 2, '.', '') }}" required>
                  </div>
                  <div class="db-form-group">
                    <label>Monthly / unit ({{ $market->billing_currency }})</label>
                    <input type="number" name="monthly_fee" class="db-input" min="0" step="0.01" value="{{ number_format($market->monthly_fee_minor_per_unit / 100, 2, '.', '') }}" required>
                  </div>
                  <div class="db-form-group">
                    <label>Disc % (maintenance)</label>
                    <input type="number" name="maintenance_commission_percent" class="db-input" min="0" max="100" step="0.01" value="{{ number_format($market->maintenance_commission_bps / 100, 2, '.', '') }}" required>
                  </div>
                  <div class="db-form-group" style="display:flex;align-items:flex-end">
                    <label style="display:flex;align-items:center;gap:8px;margin:0">
                      <input type="hidden" name="is_active" value="0">
                      <input type="checkbox" name="is_active" value="1" @checked($market->is_active)> Active
                    </label>
                  </div>
                  <button type="submit" class="db-form-submit">Save</button>
                </form>
              </div>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>

<p style="margin-top:16px"><a href="{{ route('admin.settings.index') }}" class="db-table-link">← All settings</a></p>
@endsection

@push('styles')
<style>
.admin-markets-wrap {
  overflow-x: auto;
}
.admin-markets-table {
  width: 100%;
  min-width: 960px;
}
.admin-markets-table th,
.admin-markets-table td {
  overflow: visible;
  text-overflow: clip;
}
.admin-markets-table .mk-col-chev { width: 48px; }
.admin-markets-table .mk-col-country { width: 72px; white-space: nowrap; }
.admin-markets-table .mk-col-tier { width: 108px; white-space: nowrap; }
.admin-markets-table .mk-col-currency { width: 80px; white-space: nowrap; }
.admin-markets-table .mk-col-processor { min-width: 130px; }
.admin-markets-table .mk-col-fee { min-width: 118px; white-space: nowrap; }
.admin-markets-table .mk-col-disc { width: 72px; white-space: nowrap; }
.admin-markets-table .mk-col-active { width: 64px; white-space: nowrap; }
.admin-markets-table thead th {
  white-space: nowrap;
  line-height: 1.35;
}
.admin-market-chev-cell {
  text-align: center;
  vertical-align: middle;
  padding-left: 12px;
  padding-right: 12px;
}
.admin-market-chev {
  cursor: pointer;
  color: var(--terra);
  font-weight: 600;
  font-size: 16px;
  display: inline-block;
  transition: transform 0.15s ease;
  user-select: none;
}
.admin-market-edit-row {
  display: none;
}
.admin-market-summary-row:has(.admin-market-expand-input:checked) .admin-market-chev {
  transform: rotate(90deg);
}
.admin-market-summary-row:has(.admin-market-expand-input:checked) + .admin-market-edit-row {
  display: table-row;
}
.admin-market-edit-row > td {
  padding: 0;
  background: var(--cream);
  border-bottom: 1px solid var(--cream-dark);
}
.admin-market-edit-panel {
  padding: 16px 18px 18px;
  border-top: 1px solid var(--cream-dark);
}
.admin-market-edit-form {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
  gap: 14px 18px;
  align-items: end;
}
.admin-market-edit-form .db-form-submit {
  grid-column: 1 / -1;
  justify-self: start;
}
</style>
@endpush
