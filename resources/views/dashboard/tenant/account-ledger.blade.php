@extends('dashboard.layout')
@section('page-title', 'Account ledger')
@section('breadcrumb', 'Finance')
@section('content')

@php
  $fmt = fn (int $minor) => number_format($minor / 100, 2);
@endphp

@if(! $lease)
<div class="db-empty" style="min-height:60vh">
  <div class="db-empty-icon">📒</div>
  <h3>No active lease</h3>
  <p>Your account ledger will appear when you have an active lease.</p>
</div>
@else

<div class="db-card" style="margin-bottom:0">
  <div class="db-card-body" style="padding-bottom:12px">
    <h2 style="margin:0 0 6px;font-size:22px;font-weight:600;color:var(--text)">Account Ledger</h2>
    <p style="margin:0;font-size:14px;color:var(--text-mid)">Showing all transactions</p>
    @if($lease->property)
      <p style="margin:8px 0 0;font-size:13px;color:var(--text-light)">
        {{ $lease->property->name }} · {{ $lease->currency_code }}
      </p>
    @endif
  </div>

  <div class="rm-ledger-starting">
    <span>Starting Balance</span>
    <span>{{ $fmt($ledger['starting_minor']) }}</span>
  </div>

  <div class="db-table-wrap rm-ledger-table">
    <table class="db-table rm-ledger">
      <thead>
        <tr>
          <th>Date</th>
          <th>Description</th>
          <th>Paid By</th>
          <th class="rm-num">Charge</th>
          <th class="rm-num">Payment</th>
          <th class="rm-num">Balance</th>
        </tr>
      </thead>
      <tbody>
        @forelse($ledger['rows'] as $row)
        @php $entry = $row['entry']; @endphp
        <tr>
          <td class="rm-ledger-date">{{ $entry->entry_date->format('m/d/Y') }}</td>
          <td class="rm-ledger-desc">{{ $entry->description }}</td>
          <td>{{ $entry->paid_by ?? '' }}</td>
          <td class="rm-num">{{ $entry->formattedCharge() ?? '' }}</td>
          <td class="rm-num">{{ $entry->formattedPayment() ?? '' }}</td>
          <td class="rm-num"><strong>{{ $fmt($row['balance_minor']) }}</strong></td>
        </tr>
        @empty
        <tr>
          <td colspan="6" style="text-align:center;padding:32px;color:var(--text-light)">No ledger entries yet.</td>
        </tr>
        @endforelse
      </tbody>
      @if($ledger['rows']->isNotEmpty())
      <tfoot>
        <tr class="rm-ledger-foot">
          <td colspan="5" style="text-align:right;font-weight:600">Current balance</td>
          <td class="rm-num"><strong>{{ $fmt($ledger['ending_minor']) }}</strong></td>
        </tr>
      </tfoot>
      @endif
    </table>
  </div>
</div>

<style>
  .rm-ledger-starting {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 20px;
    border-top: 1px solid var(--border, #e8e4df);
    border-bottom: 1px solid var(--border, #e8e4df);
    font-size: 14px;
    color: var(--text);
  }
  .rm-ledger-starting span:last-child {
    font-variant-numeric: tabular-nums;
    font-weight: 500;
  }
  .rm-ledger thead th {
    background: #f3f4f6;
    font-size: 12px;
    font-weight: 600;
    color: var(--text-mid);
    white-space: nowrap;
  }
  .rm-ledger tbody tr:nth-child(even) {
    background: #fafafa;
  }
  .rm-ledger .rm-num {
    text-align: right;
    font-variant-numeric: tabular-nums;
    white-space: nowrap;
    width: 1%;
  }
  .rm-ledger-date {
    white-space: nowrap;
    width: 1%;
  }
  .rm-ledger-desc {
    min-width: 200px;
    max-width: 480px;
    line-height: 1.45;
  }
  .rm-ledger-foot td {
    background: var(--cream-dark, #f5f3ef);
    border-top: 2px solid var(--border, #e8e4df);
  }
</style>

@endif
@endsection
