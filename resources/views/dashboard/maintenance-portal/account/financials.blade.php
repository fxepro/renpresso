@php
  $financialSecs = [
    'receiving' => ['label' => 'Receiving account', 'count' => $docCounts['financial_receiving'] ?? 0],
    'billing'   => ['label' => 'Billing & invoices', 'count' => $invoiceCount ?? 0],
  ];
  $financialSection = $complianceSections['financial'] ?? null;
  $receivingDocs = [
    'bank_account_verification' => $financialSection['documents']['bank_account_verification'] ?? null,
    'tax_identification' => $financialSection['documents']['tax_identification'] ?? null,
  ];
@endphp
<p class="db-form-hint" style="margin:0 0 16px;max-width:52rem;line-height:1.55">Payout bank verification and how you bill landlords.</p>

@include('dashboard.maintenance-portal.account.partials.sub-nav', [
  'tab' => 'financials',
  'activeKey' => $financialSec,
  'items' => $financialSecs,
  'ariaLabel' => 'Financial sections',
])

@if($financialSec === 'receiving')
  @if(! $team)
    <div class="db-alert" style="background:var(--gold-pale);color:var(--gold)">Set up your company profile first.</div>
  @else
    <p class="db-form-hint" style="margin-bottom:12px">Bank account and tax ID required to receive payouts from landlords.</p>
    @foreach($receivingDocs as $docType => $docDef)
      @if($docDef)
        @include('dashboard.maintenance-portal.account.partials.document-card', [
          'docType' => $docType,
          'docDef' => $docDef,
          'documentsByType' => $documentsByType,
          'accountTab' => 'financials',
          'accountSec' => 'receiving',
        ])
      @endif
    @endforeach
  @endif
@endif

@if($financialSec === 'billing')
  <div class="db-card" style="margin-bottom:18px">
    <div class="db-card-header"><span class="db-card-title">Billing methods</span></div>
    <div class="db-card-body">
      <p style="font-size:15px;color:var(--text-mid);line-height:1.6;margin:0 0 14px">
        Saved cards and ACH for paying platform fees will be added with payment processor integration.
        Use invoices below to bill landlords for completed work.
      </p>
      <p style="font-size:14px;color:var(--text-light);margin:0">Card · Bank (ACH) · PayPal — coming soon</p>
    </div>
  </div>

  <div class="db-card">
    <div class="db-card-header" style="flex-wrap:wrap;gap:8px">
      <span class="db-card-title">Recent invoices ({{ $invoiceCount ?? 0 }})</span>
      <a href="{{ route('maint.payments.invoices') }}" class="db-btn db-btn-ghost" style="font-size:13px;text-decoration:none">Full invoices →</a>
    </div>
    <div class="db-card-body" style="padding:0">
      @if(($recentInvoices ?? collect())->isEmpty())
        <p style="padding:22px;margin:0;color:var(--text-light)">No invoices yet. <a href="{{ route('maint.payments.invoices', ['panel' => 'create']) }}" class="db-table-link">Create an invoice</a></p>
      @else
        <div class="db-table-wrap">
          <table class="db-table">
            <thead><tr><th>#</th><th>Amount</th><th>Status</th><th>Landlord</th><th>Due</th></tr></thead>
            <tbody>
              @foreach($recentInvoices as $inv)
                <tr>
                  <td><a href="{{ route('maint.payments.invoices.show', $inv) }}" class="db-table-link"><strong>{{ $inv->invoice_number }}</strong></a></td>
                  <td>{{ $inv->formattedAmount() }}</td>
                  <td><span class="badge badge-{{ $inv->statusBadgeClass() }}">{{ $inv->statusLabel() }}</span></td>
                  <td>{{ $inv->landlord?->fullName() ?? '—' }}</td>
                  <td>{{ $inv->due_date?->format('d M Y') ?? '—' }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        <p style="padding:14px 22px;margin:0;font-size:13px">
          <a href="{{ route('maint.payments') }}" class="db-table-link">Payments from landlords →</a>
        </p>
      @endif
    </div>
  </div>
@endif
