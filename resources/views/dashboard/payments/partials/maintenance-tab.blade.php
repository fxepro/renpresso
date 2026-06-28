@php
  $currency = $maintenanceInvoices->first()?->currency_code ?? 'USD';
@endphp

<div class="db-stats" style="margin-bottom:20px">
  <div class="db-stat green">
    <div class="db-stat-label">Paid this month</div>
    <div class="db-stat-value">{{ $currency }} {{ number_format($maintPaidThisMonth /100,2) }}</div>
    <div class="db-stat-sub">{{ now()->format('M Y') }}</div>
  </div>
  <div class="db-stat">
    <div class="db-stat-label">Paid this year</div>
    <div class="db-stat-value">{{ $currency }} {{ number_format($maintPaidThisYear /100,2) }}</div>
    <div class="db-stat-sub">{{ now()->year }}</div>
  </div>
  <div class="db-stat terra">
    <div class="db-stat-label">Awaiting approval</div>
    <div class="db-stat-value">{{ $awaitingApprovalCount }}</div>
    <div class="db-stat-sub"><a href="{{ route('landlord.invoices.index', ['status' => 'awaiting']) }}" class="db-table-link">View on Invoices</a></div>
  </div>
</div>

<p class="db-form-hint" style="margin:0 0 16px;line-height:1.55">Maintenance invoices and platform payments. Open an invoice for details; approve and pay from the <a href="{{ route('landlord.invoices.index') }}" class="db-table-link">Invoices</a> screen.</p>

<div class="db-table-wrap">
  <table class="db-table">
    <thead>
      <tr>
        <th>Property</th>
        <th>Team</th>
        <th>Invoice</th>
        <th>Due</th>
        <th>Amount</th>
        <th>Paid</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      @forelse($maintenanceInvoices as $inv)
      <tr>
        <td>
          <a href="{{ route('landlord.invoices.show', $inv) }}" class="db-table-link" style="text-decoration:none;color:inherit">
            <div class="db-flag-name">
              <span class="db-flag">{{ config('countries.'.$inv->property?->country_code.'.flag', '🏠') }}</span>
              <div>
                <div class="db-name">{{ $inv->property?->name ?: $inv->property?->address_line1 ?? '—' }}</div>
                <div class="db-sub">{{ $inv->property?->city ?? $inv->team?->name }}</div>
              </div>
            </div>
          </a>
        </td>
        <td>{{ $inv->team?->name ?? '—' }}</td>
        <td>
          <a href="{{ route('landlord.invoices.show', $inv) }}" class="db-table-link"><strong>{{ $inv->invoice_number }}</strong></a>
        </td>
        <td>{{ $inv->due_date?->format('d M Y') ?? '—' }}</td>
        <td><strong>{{ $inv->formattedAmount() }}</strong></td>
        <td>{{ $inv->paid_at?->format('d M Y') ?? '—' }}</td>
        <td><span class="badge badge-{{ $inv->statusBadgeClass() }}">{{ $inv->landlordStatusLabel() }}</span></td>
      </tr>
      @empty
      <tr><td colspan="7" style="text-align:center;padding:32px;color:var(--text-light)">No maintenance invoices yet.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
@if($maintenanceInvoices->hasPages())
<div style="padding:16px 0;border-top:1px solid var(--cream-dark);margin-top:16px">
  {{ $maintenanceInvoices->links() }}
</div>
@endif
