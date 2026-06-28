@extends('dashboard.maintenance-portal.layout')
@section('page-title', 'Invoices')
@section('breadcrumb', 'Payments')

@push('styles')
<style>
.inv-panel-overlay { position:fixed;inset:0;background:rgba(13,31,53,0.35);z-index:300;opacity:0;pointer-events:none;transition:opacity 0.25s; }
.inv-panel-overlay.open { opacity:1;pointer-events:all; }
.inv-slide-panel {
  position:fixed;top:0;right:0;bottom:0;width:75%;min-width:360px;max-width:1200px;
  background:var(--white);z-index:301;transform:translateX(100%);
  transition:transform 0.28s cubic-bezier(.4,0,.2,1);
  display:flex;flex-direction:column;box-shadow:-8px 0 40px rgba(0,0,0,0.12);
}
.inv-slide-panel.open { transform:translateX(0); }
.inv-slide-panel .panel-header { display:flex;align-items:center;justify-content:space-between;padding:20px 28px;border-bottom:1px solid var(--cream-dark);flex-shrink:0; }
.inv-slide-panel .panel-title { font-family:'Fraunces',serif;font-size:var(--fs-title);font-weight:500;color:var(--text-dark); }
.inv-slide-panel .panel-close { width:34px;height:34px;border-radius:8px;border:1px solid var(--cream-dark);background:transparent;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:18px;color:var(--text-light); }
.inv-slide-panel .panel-close:hover { background:var(--cream-dark);color:var(--text-dark); }
.inv-slide-panel .panel-body { flex:1;overflow-y:auto;padding:24px 28px; }
.inv-slide-panel .panel-footer { padding:16px 28px;border-top:1px solid var(--cream-dark);display:flex;justify-content:flex-end;gap:10px;flex-shrink:0;background:var(--cream); }
.inv-slide-panel #invPanelForm { width:100%; max-width:none; align-self:stretch; }
.inv-slide-panel .inv-panel-stack { display:flex; flex-direction:column; gap:18px; width:100%; }
.inv-slide-panel .inv-panel-stack > .db-card,
.inv-slide-panel #invPanelForm > .db-card { width:100%; }
.inv-slide-panel .db-form-group,
.inv-slide-panel .db-input,
.inv-slide-panel .db-select,
.inv-slide-panel .db-textarea { width:100%; max-width:100%; box-sizing:border-box; }
.inv-slide-panel .db-form-row { width:100%; }
.inv-slide-panel .db-table-wrap,
.inv-slide-panel .db-table { width:100%; }
@media (max-width: 900px) { .inv-slide-panel { width:100%; max-width:none; } }
</style>
@endpush

@section('content')
<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;margin-bottom:18px">
  <p style="margin:0;color:var(--text-mid);max-width:36rem">Create line-item invoices, attach photos or PDFs, and track payments from landlords on your roster.</p>
  <button type="button" class="db-btn db-btn-primary" onclick="openInvoicePanel('create')">+ New invoice</button>
</div>

@if(session('success'))
  <div class="db-alert" style="background:var(--green-pale);color:var(--green);margin-bottom:16px">{{ session('success') }}</div>
@endif

<div class="db-card">
  <div class="db-card-header" style="flex-wrap:wrap;gap:10px">
    <span class="db-card-title">All invoices ({{ $invoices->count() }})</span>
    <form method="GET" style="display:flex;gap:8px;align-items:center;margin-left:auto">
      @if(request('panel'))<input type="hidden" name="panel" value="{{ request('panel') }}">@endif
      @if(request('invoice'))<input type="hidden" name="invoice" value="{{ request('invoice') }}">@endif
      <select name="status" class="db-select" style="font-size:13px;width:auto" onchange="this.form.submit()">
        <option value="">All statuses</option>
        @foreach(\App\Models\MaintenanceInvoice::STATUSES as $s)
          <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
        @endforeach
      </select>
    </form>
  </div>
  <div class="db-card-body" style="padding:0">
    <div class="db-table-wrap">
      <table class="db-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Bill to</th>
            <th>Total</th>
            <th>Due</th>
            <th>Status</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @forelse($invoices as $inv)
          <tr>
            <td>
              <a href="{{ route('maint.payments.invoices.show', $inv) }}" class="db-table-link"><strong>{{ $inv->invoice_number }}</strong></a>
              @if($inv->description)<br><span style="font-size:13px;color:var(--text-light)">{{ Str::limit($inv->description, 40) }}</span>@endif
            </td>
            <td>{{ $inv->bill_to_name ?: ($inv->landlord?->fullName() ?? '—') }}</td>
            <td>{{ $inv->formattedAmount() }}</td>
            <td>{{ $inv->due_date?->format('d M Y') ?? '—' }}</td>
            <td><span class="badge badge-{{ $inv->statusBadgeClass() }}">{{ $inv->statusLabel() }}</span></td>
            <td>
              <a href="{{ route('maint.payments.invoices.show', $inv) }}" class="db-btn db-btn-ghost" style="font-size:13px;text-decoration:none">View</a>
              @if($inv->isDraft())
                <button type="button" class="db-btn db-btn-ghost" style="font-size:13px" onclick="openInvoicePanel('edit', '{{ $inv->id }}')">Edit</button>
              @endif
            </td>
          </tr>
          @empty
          <tr><td colspan="6" style="padding:24px;color:var(--text-light)">No invoices yet. <button type="button" class="db-table-link" style="background:none;border:none;cursor:pointer;padding:0;font:inherit;color:inherit;text-decoration:underline" onclick="openInvoicePanel('create')">Create your first invoice</button></td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
<p style="margin-top:14px;font-size:14px"><a href="{{ route('maint.payments') }}" class="db-table-link">Payments from landlords →</a></p>

@include('dashboard.maintenance-portal.payments.invoices.partials.invoice-panel')

@push('scripts')
<script>
function openInvoicePanel(mode, invoiceId) {
  var params = new URLSearchParams(window.location.search);
  params.set('panel', mode);
  if (invoiceId) params.set('invoice', invoiceId);
  else params.delete('invoice');
  window.location = '{{ route('maint.payments.invoices') }}?' + params.toString();
}
function closeInvoicePanel() {
  var params = new URLSearchParams(window.location.search);
  params.delete('panel');
  params.delete('invoice');
  params.delete('maintenance_request_id');
  var q = params.toString();
  window.location = '{{ route('maint.payments.invoices') }}' + (q ? '?' + q : '');
}
document.addEventListener('keydown', function (e) {
  if (e.key === 'Escape' && document.getElementById('invSlidePanel')?.classList.contains('open')) {
    closeInvoicePanel();
  }
});
</script>
@endpush
@endsection
