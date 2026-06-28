@extends('dashboard.maintenance-portal.layout')
@section('page-title', $invoice->invoice_number)
@section('breadcrumb', 'Payments · Invoices')
@section('content')
<div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px;margin-bottom:18px">
  <div>
    <a href="{{ route('maint.payments.invoices') }}" class="db-table-link" style="font-size:14px">← All invoices</a>
    <h2 style="font-family:'Fraunces',serif;font-size:var(--fs-heading);margin:8px 0 4px">{{ $invoice->invoice_number }}</h2>
    <span class="badge badge-{{ $invoice->statusBadgeClass() }}">{{ $invoice->statusLabel() }}</span>
    @if($invoice->description)
      <p style="margin:8px 0 0;color:var(--text-mid)">{{ $invoice->description }}</p>
    @endif
  </div>
  <div style="display:flex;flex-wrap:wrap;gap:8px">
    @if($invoice->isDraft())
      <a href="{{ route('maint.payments.invoices.edit', $invoice) }}" class="db-btn db-btn-ghost" style="text-decoration:none">Edit</a>
      <form method="POST" action="{{ route('maint.payments.invoices.send', $invoice) }}">@csrf
        <button type="submit" class="db-btn db-btn-primary">Send</button>
      </form>
      <form method="POST" action="{{ route('maint.payments.invoices.destroy', $invoice) }}" onsubmit="return confirm('Delete this draft invoice?')">@csrf @method('DELETE')
        <button type="submit" class="db-btn db-btn-danger">Delete</button>
      </form>
    @elseif(! $invoice->isCancelled() && $invoice->status !== 'paid')
      <form method="POST" action="{{ route('maint.payments.invoices.cancel', $invoice) }}" onsubmit="return confirm('Cancel this invoice?')">@csrf
        <button type="submit" class="db-btn db-btn-ghost">Cancel invoice</button>
      </form>
    @endif
  </div>
</div>

@if(session('success'))
  <div class="db-alert" style="background:var(--green-pale);color:var(--green);margin-bottom:16px">{{ session('success') }}</div>
@endif

@include('dashboard.maintenance-portal.payments.invoices.partials.log-payment')

<div class="maint-grid-2" style="align-items:start;margin-bottom:18px">
  <div class="db-card">
    <div class="db-card-header"><span class="db-card-title">Bill to</span></div>
    <div class="db-card-body">
      <dl style="margin:0;display:grid;grid-template-columns:120px 1fr;gap:8px 12px;font-size:15px">
        <dt style="color:var(--text-light)">Name</dt><dd style="margin:0">{{ $invoice->bill_to_name ?: ($invoice->landlord?->fullName() ?? '—') }}</dd>
        <dt style="color:var(--text-light)">Email</dt><dd style="margin:0">{{ $invoice->bill_to_email ?: ($invoice->landlord?->email ?? '—') }}</dd>
        <dt style="color:var(--text-light)">Landlord</dt><dd style="margin:0">{{ $invoice->landlord?->fullName() ?? '—' }}</dd>
        <dt style="color:var(--text-light)">Property</dt><dd style="margin:0">{{ $invoice->property?->name ?: $invoice->property?->address_line1 ?? '—' }}</dd>
        <dt style="color:var(--text-light)">Request</dt><dd style="margin:0">{{ $invoice->maintenanceRequest?->title ?? '—' }}</dd>
        <dt style="color:var(--text-light)">Due</dt><dd style="margin:0">{{ $invoice->due_date?->format('d M Y') ?? '—' }}</dd>
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

@if($invoice->notes_customer || $invoice->notes_internal)
<div class="maint-grid-2" style="align-items:start;margin-bottom:18px">
  @if($invoice->notes_customer)
  <div class="db-card">
    <div class="db-card-header"><span class="db-card-title">Notes for customer</span></div>
    <div class="db-card-body"><p style="margin:0;white-space:pre-wrap;line-height:1.55">{{ $invoice->notes_customer }}</p></div>
  </div>
  @endif
  @if($invoice->notes_internal)
  <div class="db-card">
    <div class="db-card-header"><span class="db-card-title">Internal notes</span></div>
    <div class="db-card-body"><p style="margin:0;white-space:pre-wrap;line-height:1.55">{{ $invoice->notes_internal }}</p></div>
  </div>
  @endif
</div>
@endif

<div class="maint-grid-2" style="align-items:start;margin-bottom:18px">
  <div class="db-card">
    <div class="db-card-header"><span class="db-card-title">Attachments ({{ $invoice->attachments->count() }})</span></div>
    <div class="db-card-body">
      @if(! $invoice->isCancelled())
      <form method="POST" action="{{ route('maint.payments.invoices.attachments.store', $invoice) }}" enctype="multipart/form-data" class="db-form" style="margin-bottom:16px;padding-bottom:16px;border-bottom:1px solid var(--cream-dark)">
        @csrf
        <div class="db-form-row">
          <div class="db-form-group">
            <label>Type</label>
            <select name="kind" class="db-select" required>
              @foreach(\App\Models\MaintenanceInvoiceAttachment::KINDS as $k)
                <option value="{{ $k }}">{{ ucfirst(str_replace('_', ' ', $k)) }}</option>
              @endforeach
            </select>
          </div>
          <div class="db-form-group">
            <label>File</label>
            <input type="file" name="file" class="db-input" required accept=".pdf,.jpg,.jpeg,.png,.webp">
          </div>
        </div>
        <div class="db-form-group"><label>Caption (optional)</label><input type="text" name="caption" class="db-input" maxlength="255"></div>
        <button type="submit" class="db-btn db-btn-ghost">Upload</button>
      </form>
      @endif
      @forelse($invoice->attachments as $att)
        <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;padding:10px 0;border-bottom:1px solid var(--cream-dark)">
          <div>
            <strong>{{ $att->original_filename }}</strong>
            <span style="font-size:13px;color:var(--text-light);display:block">{{ $att->kindLabel() }} · {{ number_format($att->size_bytes / 1024, 1) }} KB</span>
            @if($att->caption)<span style="font-size:13px;color:var(--text-mid)">{{ $att->caption }}</span>@endif
          </div>
          <div style="display:flex;gap:8px">
            <a href="{{ route('maint.payments.invoices.attachments.file', $att) }}" class="db-btn db-btn-ghost" style="font-size:13px;text-decoration:none" target="_blank">Download</a>
            @if(! $invoice->isCancelled())
            <form method="POST" action="{{ route('maint.payments.invoices.attachments.destroy', $att) }}" onsubmit="return confirm('Remove attachment?')">@csrf @method('DELETE')
              <button type="submit" class="db-btn db-btn-danger" style="font-size:13px">Remove</button>
            </form>
            @endif
          </div>
        </div>
      @empty
        <p style="margin:0;color:var(--text-light)">No attachments yet.</p>
      @endforelse
    </div>
  </div>

  <div class="db-card">
    <div class="db-card-header"><span class="db-card-title">Activity</span></div>
    <div class="db-card-body">
      <ul style="list-style:none;margin:0;padding:0">
        @foreach($invoice->events as $ev)
        <li style="padding:10px 0;border-bottom:1px solid var(--cream-dark);font-size:14px">
          <strong>{{ $ev->label() }}</strong>
          <span style="color:var(--text-light);display:block">{{ $ev->created_at->format('d M Y H:i') }}@if($ev->actor) · {{ $ev->actor->fullName() }}@endif</span>
        </li>
        @endforeach
      </ul>
    </div>
  </div>
</div>

@if($invoice->paymentsReceived->isNotEmpty())
<div class="db-card">
  <div class="db-card-header"><span class="db-card-title">Payments from landlord</span></div>
  <div class="db-card-body" style="padding:0">
    <div class="db-table-wrap">
      <table class="db-table">
        <thead><tr><th>Date</th><th>Amount</th><th>Method</th><th>Reference</th></tr></thead>
        <tbody>
          @foreach($invoice->paymentsReceived as $p)
          <tr>
            <td>{{ $p->paid_on->format('d M Y') }}</td>
            <td>{{ $p->formattedAmount() }}</td>
            <td>{{ $p->method ?? '—' }}</td>
            <td>{{ $p->reference ?? '—' }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>
@endif
@endsection
