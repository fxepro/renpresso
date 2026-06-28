@php
  $invoice = $panelInvoice ?? null;
  $isEdit = ($panelMode ?? null) === 'edit' && $invoice;
  $formAction = $isEdit
    ? route('maint.payments.invoices.update', $invoice)
    : route('maint.payments.invoices.store');
@endphp
<div id="invPanelOverlay" class="inv-panel-overlay {{ ($panelMode ?? null) ? 'open' : '' }}" onclick="closeInvoicePanel()"></div>
<div id="invSlidePanel" class="inv-slide-panel {{ ($panelMode ?? null) ? 'open' : '' }}">
  <div class="panel-header">
    <span class="panel-title">{{ $isEdit ? 'Edit '.$invoice->invoice_number : 'New invoice' }}</span>
    <button type="button" class="panel-close" onclick="closeInvoicePanel()" aria-label="Close">✕</button>
  </div>
  <div class="panel-body">
    @if($errors->any())
      <div class="db-alert" style="background:var(--red-pale);color:var(--red);margin-bottom:16px">
        <ul style="margin:0;padding-left:18px">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
      </div>
    @endif

    <form method="POST" action="{{ $formAction }}" class="db-form" id="invPanelForm">
      @csrf
      @if($isEdit) @method('PUT') @endif
      @include('dashboard.maintenance-portal.payments.invoices.partials.form-fields')
      @include('dashboard.maintenance-portal.payments.invoices.partials.line-items', ['invoice' => $invoice])
    </form>
    @include('dashboard.maintenance-portal.payments.invoices.partials.landlord-options-script')
  </div>
  <div class="panel-footer">
    <button type="button" class="db-btn db-btn-ghost" onclick="closeInvoicePanel()">Cancel</button>
    <button type="submit" form="invPanelForm" class="db-btn db-btn-primary">{{ $isEdit ? 'Save changes' : 'Save draft' }}</button>
  </div>
</div>
