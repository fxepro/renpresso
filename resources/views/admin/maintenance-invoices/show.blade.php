@extends('admin.layout')
@section('title', ($invoice->invoice_number ?? 'Invoice').' — Maintenance')
@section('page-title', $invoice->invoice_number ?? 'Invoice detail')
@section('breadcrumb', 'Maintenance invoices')

@section('topbar-actions')
  <a href="{{ route('admin.maintenance-invoices') }}" class="db-btn db-btn-ghost" style="text-decoration:none">← All invoices</a>
  @if($invoice->property)
    <a href="{{ route('admin.properties.show', $invoice->property) }}" class="db-btn db-btn-ghost" style="text-decoration:none">Property</a>
  @endif
  @if($invoice->landlord)
    <a href="{{ route('admin.landlords.show', $invoice->landlord) }}" class="db-btn db-btn-ghost" style="text-decoration:none">Landlord</a>
  @endif
@endsection

@push('styles')
<style>
.inv-status {
  display: inline-flex; font-size: 12px; font-weight: 600;
  padding: 3px 11px; border-radius: 20px;
}
.inv-status.draft          { background: #f5f5f5; color: #9e9e9e; }
.inv-status.sent           { background: #e3f2fd; color: #1565c0; }
.inv-status.partially_paid { background: #fff8e1; color: #f57f17; }
.inv-status.paid           { background: #e8f5e9; color: #2e7d32; }
.inv-status.cancelled      { background: #fce4ec; color: #b71c1c; }
.info-row {
  display: flex; align-items: baseline; gap: 12px;
  padding: 10px 24px; border-bottom: 1px solid var(--cream-dark); font-size: 14px;
}
.info-row:last-child { border-bottom: none; }
.info-label {
  font-size: 11px; font-weight: 700; letter-spacing: .07em;
  text-transform: uppercase; color: var(--text-light);
  min-width: 120px; flex-shrink: 0;
}
.info-val { color: var(--text-dark); font-weight: 500; }
.line-row { display: flex; justify-content: space-between; align-items: baseline; padding: 10px 0; border-bottom: 1px solid var(--cream-dark); }
.line-row:last-of-type { border-bottom: none; }
.line-desc { font-size: 14px; color: var(--text-dark); }
.line-qty  { font-size: 13px; color: var(--text-mid); min-width: 60px; text-align: center; }
.line-amt  { font-size: 14px; font-weight: 600; color: var(--text-dark); text-align: right; min-width: 100px; }
</style>
@endpush

@section('content')

@php
  $isOverdue = $invoice->status === 'sent' && $invoice->due_date?->isPast();
  $displayStatus = $isOverdue ? 'overdue' : $invoice->status;
  $commission = (int) round($invoice->amount_minor * 0.05);
@endphp

<div class="db-stats">
  <div class="db-stat {{ $invoice->status === 'paid' ? 'green' : '' }}">
    <div class="db-stat-label">Status</div>
    <div class="db-stat-value" style="margin-top:4px;font-size:20px">
      <span class="inv-status {{ $displayStatus }}">
        {{ $isOverdue ? 'Overdue' : ucwords(str_replace('_',' ',$invoice->status)) }}
      </span>
    </div>
    <div class="db-stat-sub">
      @if($invoice->paid_at) Paid {{ $invoice->paid_at->format('d M Y') }}
      @elseif($invoice->due_date) Due {{ $invoice->due_date->format('d M Y') }}
      @else No due date set @endif
    </div>
  </div>
  <div class="db-stat">
    <div class="db-stat-label">Invoice total</div>
    <div class="db-stat-value" style="font-size:var(--fs-heading)">
      {{ number_format($invoice->amount_minor / 100, 2) }}
    </div>
    <div class="db-stat-sub">{{ strtoupper($invoice->currency_code) }}
      @if($invoice->tax_minor) · incl. tax @endif
    </div>
  </div>
  <div class="db-stat green">
    <div class="db-stat-label">Platform commission (5%)</div>
    <div class="db-stat-value" style="font-size:var(--fs-heading)">
      {{ number_format($commission / 100, 2) }}
    </div>
    <div class="db-stat-sub">{{ strtoupper($invoice->currency_code) }} · on payment</div>
  </div>
  <div class="db-stat">
    <div class="db-stat-label">Approved by landlord</div>
    <div class="db-stat-value" style="font-size:20px;margin-top:4px">
      @if($invoice->landlord_approved_at)
        <span style="color:#2e7d32;font-size:16px">✓ {{ $invoice->landlord_approved_at->format('d M Y') }}</span>
      @else
        <span style="color:#9e9e9e;font-size:14px">Pending</span>
      @endif
    </div>
    <div class="db-stat-sub">Landlord approval required before payment</div>
  </div>
</div>

<div style="display:grid;grid-template-columns:1.4fr 1fr 1fr;gap:18px;margin-bottom:20px">

  {{-- Invoice meta --}}
  <div class="db-card" style="margin:0">
    <div class="db-card-header"><span class="db-card-title">Invoice details</span></div>
    <div class="db-card-body" style="padding:0">
      @foreach([
        ['Invoice #',   $invoice->invoice_number ?? '—'],
        ['Description', $invoice->description ?? '—'],
        ['Bill to',     $invoice->bill_to_name ?? '—'],
        ['Bill email',  $invoice->bill_to_email ?? '—'],
        ['Subtotal',    strtoupper($invoice->currency_code).' '.number_format(($invoice->subtotal_minor ?? $invoice->amount_minor) / 100, 2)],
        ['Tax',         $invoice->tax_minor ? strtoupper($invoice->currency_code).' '.number_format($invoice->tax_minor/100,2) : 'None'],
        ['Total',       strtoupper($invoice->currency_code).' '.number_format($invoice->amount_minor/100,2)],
        ['Issued',      $invoice->issued_at?->format('d M Y') ?? '—'],
        ['Sent',        $invoice->sent_at?->format('d M Y') ?? '—'],
        ['Due date',    $invoice->due_date?->format('d M Y') ?? '—'],
        ['Paid',        $invoice->paid_at?->format('d M Y') ?? '—'],
      ] as [$lbl, $val])
      <div class="info-row">
        <span class="info-label">{{ $lbl }}</span>
        <span class="info-val">{{ $val }}</span>
      </div>
      @endforeach
    </div>
  </div>

  {{-- Team --}}
  <div class="db-card" style="margin:0">
    <div class="db-card-header"><span class="db-card-title">Service team</span></div>
    <div class="db-card-body" style="padding:0">
      @if($invoice->team)
      @foreach([
        ['Team',    $invoice->team->name],
        ['City',    $invoice->team->city ?? '—'],
        ['Country', strtoupper($invoice->team->country_code ?? '—')],
        ['Phone',   $invoice->team->phone ?? '—'],
      ] as [$lbl, $val])
      <div class="info-row">
        <span class="info-label">{{ $lbl }}</span>
        <span class="info-val">{{ $val }}</span>
      </div>
      @endforeach
      @else
        <div class="info-row"><span style="color:var(--text-light)">No team linked</span></div>
      @endif
    </div>
  </div>

  {{-- Property + Landlord --}}
  <div class="db-card" style="margin:0">
    <div class="db-card-header"><span class="db-card-title">Property · Landlord</span></div>
    <div class="db-card-body" style="padding:0">
      @if($invoice->property)
      <div class="info-row">
        <span class="info-label">Property</span>
        <span class="info-val">
          <a href="{{ route('admin.properties.show', $invoice->property) }}"
             style="color:var(--terra);text-decoration:none">{{ $invoice->property->name }}</a>
        </span>
      </div>
      <div class="info-row">
        <span class="info-label">Country</span>
        <span class="info-val">{{ strtoupper($invoice->property->country_code ?? '—') }}</span>
      </div>
      @endif
      @if($invoice->landlord)
      <div class="info-row">
        <span class="info-label">Landlord</span>
        <span class="info-val">
          <a href="{{ route('admin.landlords.show', $invoice->landlord) }}"
             style="color:var(--terra);text-decoration:none">
            {{ $invoice->landlord->first_name }} {{ $invoice->landlord->last_name }}
          </a>
        </span>
      </div>
      <div class="info-row">
        <span class="info-label">Email</span>
        <span class="info-val" style="font-size:13px">{{ $invoice->landlord->email }}</span>
      </div>
      @endif
      @if($invoice->maintenanceRequest)
      <div class="info-row">
        <span class="info-label">Request</span>
        <span class="info-val">
          <a href="{{ route('admin.maintenance-requests.show', $invoice->maintenanceRequest) }}"
             style="color:var(--terra);text-decoration:none;font-size:13px">
            {{ $invoice->maintenanceRequest->title }}
          </a>
        </span>
      </div>
      @endif
    </div>
  </div>
</div>

{{-- Line items --}}
@if($invoice->lines && $invoice->lines->count())
<div class="db-card" style="margin-bottom:20px">
  <div class="db-card-header"><span class="db-card-title">Line items ({{ $invoice->lines->count() }})</span></div>
  <div class="db-card-body">
    @foreach($invoice->lines as $line)
    <div class="line-row">
      <div class="line-desc">{{ $line->description ?? '—' }}</div>
      <div class="line-qty">× {{ $line->quantity ?? 1 }}</div>
      <div class="line-amt">
        {{ strtoupper($invoice->currency_code) }}
        {{ number_format(($line->amount_minor ?? $line->unit_price_minor * ($line->quantity ?? 1)) / 100, 2) }}
      </div>
    </div>
    @endforeach
    <div class="line-row" style="border-top:2px solid var(--cream-dark);margin-top:4px;padding-top:14px">
      <div class="line-desc" style="font-weight:700">Total</div>
      <div class="line-qty"></div>
      <div class="line-amt">{{ strtoupper($invoice->currency_code) }} {{ number_format($invoice->amount_minor/100,2) }}</div>
    </div>
  </div>
</div>
@endif

{{-- Payments received --}}
<div class="db-card">
  <div class="db-card-header">
    <span class="db-card-title">Payments received ({{ $invoice->paymentsReceived?->count() ?? 0 }})</span>
  </div>
  <div class="db-card-body" style="{{ $invoice->paymentsReceived?->isEmpty() ? '' : 'padding:0' }}">
    @if(!$invoice->paymentsReceived || $invoice->paymentsReceived->isEmpty())
      <div class="db-empty" style="padding:28px 0">
        <div class="db-empty-icon">💳</div>
        <h3 style="font-size:16px">No payments yet</h3>
      </div>
    @else
    <div class="db-table-wrap">
      <table class="db-table">
        <thead>
          <tr>
            <th>Date</th>
            <th style="text-align:right">Amount</th>
            <th>Method</th>
            <th>Reference</th>
          </tr>
        </thead>
        <tbody>
          @foreach($invoice->paymentsReceived as $pmt)
          <tr>
            <td>{{ $pmt->received_at ? \Carbon\Carbon::parse($pmt->received_at)->format('d M Y') : '—' }}</td>
            <td style="text-align:right;font-weight:600">
              {{ strtoupper($pmt->currency_code ?? $invoice->currency_code) }}
              {{ number_format($pmt->amount_minor / 100, 2) }}
            </td>
            <td style="font-size:13px;color:var(--text-mid)">{{ $pmt->payment_method ?? '—' }}</td>
            <td style="font-size:12px;font-family:monospace;color:var(--text-light)">{{ $pmt->reference ?? '—' }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    @endif
  </div>
</div>

@endsection
