<div class="db-stats" style="margin-bottom:20px">
  <div class="db-stat {{ $nextDue ? 'terra' : 'green' }}">
    <div class="db-stat-label">Amount due</div>
    @if($nextDue)
      <div class="db-stat-value">{{ number_format($nextDue->amount_minor_units /100,2) }} {{ $nextDue->currency_code }}</div>
      <div class="db-stat-sub">Due {{ $nextDue->due_date?->format('d M Y') }}</div>
    @else
      <div class="db-stat-value">—</div>
      <div class="db-stat-sub">Nothing due right now</div>
    @endif
  </div>
  <div class="db-stat">
    <div class="db-stat-label">Pending</div>
    <div class="db-stat-value">{{ $pendingCount }}</div>
  </div>
  <div class="db-stat">
    <div class="db-stat-label">Failed</div>
    <div class="db-stat-value">{{ $failedCount }}</div>
  </div>
  <div class="db-stat">
    <div class="db-stat-label">Monthly rent</div>
    <div class="db-stat-value">{{ number_format($lease->rent_minor_units /100,2) }}</div>
    <div class="db-stat-sub">{{ $lease->currency_code }}</div>
  </div>
</div>

<div class="db-card">
  <div class="db-card-header">
    <span class="db-card-title">Payment history</span>
    <span style="display:flex;gap:8px;flex-wrap:wrap">
      <a href="{{ route('tenant.account-ledger') }}" class="db-btn db-btn-ghost" style="font-size:12px;padding:5px 10px;text-decoration:none">Account ledger →</a>
      <a href="{{ route('tenant.payments', ['tab' => 'current']) }}" class="db-btn db-btn-ghost" style="font-size:12px;padding:5px 10px;text-decoration:none">Pay current rent →</a>
    </span>
  </div>
  <div class="db-table-wrap">
    <table class="db-table">
      <thead><tr><th>Due</th><th>Amount</th><th>Collected</th><th>Status</th></tr></thead>
      <tbody>
        @forelse($payments as $pay)
        <tr>
          <td>{{ $pay->due_date?->format('d M Y') }}</td>
          <td><strong>{{ number_format($pay->amount_minor_units / 100, 2) }} {{ $pay->currency_code }}</strong></td>
          <td>{{ $pay->collected_at?->format('d M Y') ?? '—' }}</td>
          <td><span class="badge badge-{{ $pay->status === 'success' ? 'green' : ($pay->status === 'failed' ? 'red' : 'gold') }}">{{ ucfirst($pay->status) }}</span></td>
        </tr>
        @empty
        <tr><td colspan="4" style="text-align:center;padding:32px;color:var(--text-light)">No payments on record yet.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($payments->hasPages())
    <div style="padding:16px 20px">{{ $payments->links() }}</div>
  @endif
</div>
