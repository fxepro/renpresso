@extends('dashboard.maintenance-portal.layout')
@section('page-title', 'Payments')
@section('breadcrumb', 'Payments')
@section('content')
<p class="db-form-hint" style="margin:0 0 18px;max-width:42rem;line-height:1.55">Payments from landlords are logged against sent invoices. Open an invoice to log a payment when the landlord pays.</p>

@if(session('success'))
  <div class="db-alert" style="background:var(--green-pale);color:var(--green);margin-bottom:16px">{{ session('success') }}</div>
@endif

@if($awaitingInvoices->isNotEmpty())
<div class="db-card" style="margin-bottom:18px">
  <div class="db-card-header"><span class="db-card-title">Awaiting payment ({{ $awaitingInvoices->count() }})</span></div>
  <div class="db-card-body" style="padding:0">
    <div class="db-table-wrap">
      <table class="db-table">
        <thead><tr><th>Invoice</th><th>Landlord</th><th>Due</th><th>Balance</th><th></th></tr></thead>
        <tbody>
          @foreach($awaitingInvoices as $inv)
          <tr>
            <td><a href="{{ route('maint.payments.invoices.show', $inv) }}" class="db-table-link"><strong>{{ $inv->invoice_number }}</strong></a></td>
            <td>{{ $inv->bill_to_name ?: ($inv->landlord?->fullName() ?? '—') }}</td>
            <td>{{ $inv->due_date?->format('d M Y') ?? '—' }}</td>
            <td><strong>{{ $inv->formattedAmountDue() }}</strong></td>
            <td><a href="{{ route('maint.payments.invoices.show', $inv) }}#log-payment" class="db-btn db-btn-ghost" style="font-size:13px;text-decoration:none">Log payment</a></td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>
@endif

<div class="db-card">
  <div class="db-card-header"><span class="db-card-title">Payment history</span></div>
  <div class="db-card-body" style="padding:0">
    <div class="db-table-wrap">
      <table class="db-table">
        <thead><tr><th>Date</th><th>Amount</th><th>Invoice</th><th>Landlord</th><th>Method</th><th></th></tr></thead>
        <tbody>
          @forelse($payments as $p)
          <tr>
            <td>{{ $p->paid_on->format('d M Y') }}</td>
            <td><strong>{{ $p->formattedAmount() }}</strong></td>
            <td>
              @if($p->invoice)
                <a href="{{ route('maint.payments.invoices.show', $p->invoice) }}" class="db-table-link">{{ $p->invoice->invoice_number }}</a>
              @else — @endif
            </td>
            <td>{{ $p->landlord?->fullName() ?? $p->invoice?->landlord?->fullName() ?? '—' }}</td>
            <td>{{ $p->method ?? '—' }}</td>
            <td>
              @if($p->invoice)
              <form method="POST" action="{{ route('maint.payments.destroy', $p) }}" onsubmit="return confirm('Remove this payment?')">@csrf @method('DELETE')
                <button type="submit" class="db-btn db-btn-danger" style="font-size:13px">Remove</button>
              </form>
              @endif
            </td>
          </tr>
          @empty
          <tr><td colspan="6" style="padding:24px;color:var(--text-light)">No payments yet. Log payments on sent invoices when landlords pay.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
