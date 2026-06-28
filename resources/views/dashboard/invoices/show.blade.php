@extends('dashboard.layout')
@section('page-title', $invoice->invoice_number)
@section('breadcrumb', 'Finance · Invoices')
@section('content')
<div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px;margin-bottom:18px">
  <div>
    <a href="{{ route('landlord.invoices.index') }}" class="db-table-link" style="font-size:14px">← All invoices</a>
    <h2 style="font-family:'Fraunces',serif;font-size:var(--fs-heading);margin:8px 0 4px">{{ $invoice->invoice_number }}</h2>
    <span class="badge badge-{{ $invoice->statusBadgeClass() }}">{{ $invoice->landlordStatusLabel() }}</span>
    @if($invoice->description)
      <p style="margin:8px 0 0;color:var(--text-mid)">{{ $invoice->description }}</p>
    @endif
  </div>
</div>

@if(session('success'))
  <div class="db-alert" style="background:var(--green-pale);color:var(--green);margin-bottom:16px">{{ session('success') }}</div>
@endif
@if(session('error'))
  <div class="db-alert" style="background:var(--red-pale);color:var(--red);margin-bottom:16px">{{ session('error') }}</div>
@endif

@if($invoice->needsLandlordApproval())
<div class="db-card" id="approve" style="margin-bottom:18px;border-color:var(--gold)">
  <div class="db-card-header"><span class="db-card-title">Approve &amp; pay</span></div>
  <div class="db-card-body">
    <p style="font-size:15px;color:var(--text-mid);line-height:1.55;margin:0 0 16px">
      Approving authorizes <strong>{{ $invoice->formattedAmountDue() }}</strong> to be charged through your linked billing account and paid to <strong>{{ $invoice->team?->name }}</strong>. This is handled automatically by the platform.
    </p>
    <form method="POST" action="{{ route('landlord.invoices.approve', $invoice) }}" onsubmit="return confirm('Approve and pay {{ $invoice->formattedAmountDue() }} to {{ $invoice->team?->name }}?')">
      @csrf
      <button type="submit" class="db-btn db-btn-primary">Approve</button>
    </form>
  </div>
</div>
@elseif($invoice->status === 'paid')
<div class="db-alert" style="background:var(--green-pale);color:var(--green);margin-bottom:18px">
  Paid in full{{ $invoice->landlord_approved_at ? ' · approved '.$invoice->landlord_approved_at->format('d M Y') : '' }}
</div>
@endif

<div class="maint-grid-2" style="align-items:start;margin-bottom:18px">
  <div class="db-card">
    <div class="db-card-header"><span class="db-card-title">From</span></div>
    <div class="db-card-body">
      <dl style="margin:0;display:grid;grid-template-columns:120px 1fr;gap:8px 12px;font-size:15px">
        <dt style="color:var(--text-light)">Maintenance team</dt><dd style="margin:0">{{ $invoice->team?->name ?? '—' }}</dd>
        <dt style="color:var(--text-light)">Property</dt><dd style="margin:0">{{ $invoice->property?->name ?: $invoice->property?->address_line1 ?? '—' }}</dd>
        <dt style="color:var(--text-light)">Request</dt><dd style="margin:0">{{ $invoice->maintenanceRequest?->title ?? '—' }}</dd>
        <dt style="color:var(--text-light)">Due</dt><dd style="margin:0">{{ $invoice->due_date?->format('d M Y') ?? '—' }}</dd>
        <dt style="color:var(--text-light)">Sent</dt><dd style="margin:0">{{ $invoice->sent_at?->format('d M Y') ?? '—' }}</dd>
      </dl>
    </div>
  </div>
  <div class="db-card">
    <div class="db-card-header"><span class="db-card-title">Totals</span></div>
    <div class="db-card-body">
      <dl style="margin:0;font-size:15px">
        <div style="display:flex;justify-content:space-between;padding:6px 0"><dt style="color:var(--text-light)">Subtotal</dt><dd style="margin:0">{{ $invoice->formatMinor($invoice->subtotal_minor) }}</dd></div>
        <div style="display:flex;justify-content:space-between;padding:6px 0"><dt style="color:var(--text-light)">Tax</dt><dd style="margin:0">{{ $invoice->formatMinor($invoice->tax_minor) }}</dd></div>
        <div style="display:flex;justify-content:space-between;padding:8px 0;border-top:1px solid var(--cream-dark);font-weight:600"><dt>Total</dt><dd style="margin:0">{{ $invoice->formattedAmount() }}</dd></div>
        <div style="display:flex;justify-content:space-between;padding:6px 0"><dt style="color:var(--text-light)">Paid</dt><dd style="margin:0">{{ $invoice->formatMinor($invoice->amountPaidMinor()) }}</dd></div>
        <div style="display:flex;justify-content:space-between;padding:6px 0;font-weight:600;color:var(--terra)"><dt>Amount due</dt><dd style="margin:0">{{ $invoice->formattedAmountDue() }}</dd></div>
      </dl>
    </div>
  </div>
</div>

@if($invoice->notes_customer)
<div class="db-card" style="margin-bottom:18px">
  <div class="db-card-header"><span class="db-card-title">Notes</span></div>
  <div class="db-card-body"><p style="margin:0;white-space:pre-wrap;line-height:1.55">{{ $invoice->notes_customer }}</p></div>
</div>
@endif

<div class="db-card" style="margin-bottom:18px">
  <div class="db-card-header"><span class="db-card-title">Line items</span></div>
  <div class="db-card-body" style="padding:0">
    <div class="db-table-wrap">
      <table class="db-table">
        <thead><tr><th>Description</th><th>Qty</th><th>Unit</th><th>Total</th></tr></thead>
        <tbody>
          @foreach($invoice->lines as $line)
          <tr>
            <td>{{ $line->description }}</td>
            <td>{{ rtrim(rtrim(number_format($line->quantity, 3, '.', ''), '0'), '.') }}</td>
            <td>{{ $invoice->formatMinor($line->unit_price_minor) }}</td>
            <td><strong>{{ $invoice->formatMinor($line->line_total_minor) }}</strong></td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>

@if($invoice->attachments->isNotEmpty())
<div class="db-card" style="margin-bottom:18px">
  <div class="db-card-header"><span class="db-card-title">Attachments</span></div>
  <div class="db-card-body">
    @foreach($invoice->attachments as $att)
      <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid var(--cream-dark)">
        <span>{{ $att->original_filename }} <span style="color:var(--text-light);font-size:13px">({{ $att->kindLabel() }})</span></span>
        <a href="{{ route('landlord.invoices.attachments.file', $att) }}" class="db-table-link" target="_blank">Download</a>
      </div>
    @endforeach
  </div>
</div>
@endif
@endsection
