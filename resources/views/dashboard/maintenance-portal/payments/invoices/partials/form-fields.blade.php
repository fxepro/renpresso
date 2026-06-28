@php
  $invoice = $invoice ?? ($panelInvoice ?? null);
  $oldLandlord = old('landlord_id', isset($invoice) ? $invoice->landlord_id : ($prefillRequest?->lease?->property?->landlord_id));
  $oldRequest = old('maintenance_request_id', isset($invoice) ? $invoice->maintenance_request_id : ($prefillRequest?->id));
  $oldProperty = old('property_id', isset($invoice) ? $invoice->property_id : ($prefillRequest?->lease?->property_id));
@endphp
<div class="inv-panel-stack">
  <div class="db-card">
    <div class="db-card-header"><span class="db-card-title">Bill to</span></div>
    <div class="db-card-body db-form">
      <div class="db-form-group">
        <label>Landlord <span style="color:var(--terra)">*</span></label>
        <select name="landlord_id" class="db-select" id="inv-landlord" required>
          <option value="">Select landlord…</option>
          @forelse($landlords as $ll)
            <option value="{{ $ll->id }}" data-name="{{ $ll->fullName() }}" data-email="{{ $ll->email }}" @selected($oldLandlord == $ll->id)>{{ $ll->fullName() }} · {{ $ll->email }}</option>
          @empty
            <option value="" disabled>No landlords on your roster — they must add you from their maintenance team page or send an invite.</option>
          @endforelse
        </select>
        <p class="db-form-hint" style="margin-top:6px">Only landlords who have engaged your team on their roster.</p>
      </div>
      <div class="db-form-row">
        <div class="db-form-group">
          <label>Bill-to name</label>
          <input type="text" name="bill_to_name" id="inv-bill-name" class="db-input" maxlength="200" value="{{ old('bill_to_name', isset($invoice) ? $invoice->bill_to_name : '') }}">
        </div>
        <div class="db-form-group">
          <label>Bill-to email</label>
          <input type="email" name="bill_to_email" id="inv-bill-email" class="db-input" maxlength="200" value="{{ old('bill_to_email', isset($invoice) ? $invoice->bill_to_email : '') }}">
        </div>
      </div>
      <div class="db-form-group">
        <label>Property</label>
        <select name="property_id" class="db-select" id="inv-property" disabled>
          <option value="">Select landlord first…</option>
        </select>
        <p id="inv-property-hint" class="db-form-hint" style="margin-top:6px;display:none"></p>
      </div>
      <div class="db-form-group">
        <label>Related maintenance request</label>
        <select name="maintenance_request_id" class="db-select" id="inv-request" disabled>
          <option value="">Select landlord first…</option>
        </select>
      </div>
    </div>
  </div>
  <div class="db-card">
    <div class="db-card-header"><span class="db-card-title">Terms</span></div>
    <div class="db-card-body db-form">
      <div class="db-form-row">
        <div class="db-form-group">
          <label>Currency</label>
          <input type="text" name="currency_code" class="db-input" maxlength="3" value="{{ old('currency_code', isset($invoice) ? $invoice->currency_code : $defaultCurrency) }}" required>
        </div>
        <div class="db-form-group">
          <label>Due date</label>
          <input type="date" name="due_date" class="db-input" value="{{ old('due_date', isset($invoice) && $invoice->due_date ? $invoice->due_date->format('Y-m-d') : '') }}">
        </div>
      </div>
      <div class="db-form-group">
        <label>Summary (optional)</label>
        <input type="text" name="description" class="db-input" maxlength="2000" value="{{ old('description', isset($invoice) ? $invoice->description : '') }}" placeholder="Short title on invoice list">
      </div>
      <div class="db-form-group">
        <label>Tax amount</label>
        <input type="number" name="tax" id="inv-tax" class="db-input" step="0.01" min="0" value="{{ old('tax', isset($invoice) ? number_format($invoice->tax_minor / 100, 2, '.', '') : '0') }}">
      </div>
      <p style="font-size:14px;color:var(--text-mid);margin:12px 0 0">
        Subtotal: <strong id="inv-subtotal-preview">0.00</strong> ·
        Total: <strong id="inv-total-preview">0.00</strong> {{ old('currency_code', isset($invoice) ? $invoice->currency_code : $defaultCurrency) }}
      </p>
    </div>
  </div>
  <div class="db-card">
    <div class="db-card-header"><span class="db-card-title">Notes for customer</span></div>
    <div class="db-card-body">
      <textarea name="notes_customer" class="db-textarea" rows="3" placeholder="Shown on invoice to landlord">{{ old('notes_customer', isset($invoice) ? $invoice->notes_customer : '') }}</textarea>
    </div>
  </div>
  <div class="db-card">
    <div class="db-card-header"><span class="db-card-title">Internal notes</span></div>
    <div class="db-card-body">
      <textarea name="notes_internal" class="db-textarea" rows="3" placeholder="Team only">{{ old('notes_internal', isset($invoice) ? $invoice->notes_internal : '') }}</textarea>
    </div>
  </div>
</div>
