@php
  $canLog = ! $invoice->isDraft() && ! $invoice->isCancelled() && $invoice->amountDueMinor() > 0;
  $dueMajor = number_format($invoice->amountDueMinor() / 100, 2, '.', '');
@endphp
@if($canLog)
<div class="db-card" id="log-payment" style="margin-bottom:18px">
  <div class="db-card-header"><span class="db-card-title">Payment from landlord</span></div>
  <div class="db-card-body">
    <p class="db-form-hint" style="margin:0 0 14px">Balance due: <strong>{{ $invoice->formattedAmountDue() }}</strong></p>
    <form method="POST" action="{{ route('maint.payments.invoices.pay', $invoice) }}" class="db-form" style="max-width:100%">
      @csrf
      <div class="db-form-row">
        <div class="db-form-group">
          <label>Amount <span style="color:var(--terra)">*</span></label>
          <input type="number" name="amount" class="db-input" step="0.01" min="0.01" max="{{ $dueMajor }}" value="{{ old('amount', $dueMajor) }}" required>
          @error('amount')<span class="db-form-error">{{ $message }}</span>@enderror
        </div>
        <div class="db-form-group">
          <label>Paid on <span style="color:var(--terra)">*</span></label>
          <input type="date" name="paid_on" class="db-input" value="{{ old('paid_on', date('Y-m-d')) }}" required>
        </div>
      </div>
      <div class="db-form-row">
        <div class="db-form-group">
          <label>Method</label>
          <input type="text" name="method" class="db-input" value="{{ old('method') }}" placeholder="Bank transfer, check…">
        </div>
        <div class="db-form-group">
          <label>Reference</label>
          <input type="text" name="reference" class="db-input" value="{{ old('reference') }}" placeholder="Transaction ID">
        </div>
      </div>
      <div class="db-form-group">
        <label>Notes (optional)</label>
        <textarea name="notes" class="db-textarea" rows="2">{{ old('notes') }}</textarea>
      </div>
      <button type="submit" class="db-form-submit">Log payment</button>
    </form>
  </div>
</div>
@endif
