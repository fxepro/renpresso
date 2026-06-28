@extends('admin.layout')
@section('title', 'Maintenance invoices')
@section('page-title', 'Maintenance invoices')
@section('breadcrumb', 'Operations')

@push('styles')
<style>
.inv-status {
  display: inline-flex; font-size: 11px; font-weight: 600;
  padding: 2px 9px; border-radius: 20px; white-space: nowrap;
}
.inv-status.draft          { background: #f5f5f5; color: #9e9e9e; }
.inv-status.sent           { background: #e3f2fd; color: #1565c0; }
.inv-status.partially_paid { background: #fff8e1; color: #f57f17; }
.inv-status.paid           { background: #e8f5e9; color: #2e7d32; }
.inv-status.cancelled      { background: #fce4ec; color: #b71c1c; }
.inv-status.overdue        { background: #fce4ec; color: #b71c1c; }
.t-name { font-weight: 600; color: var(--text-dark); text-decoration: none; }
.t-name:hover { color: var(--terra); }
.t-sub  { font-size: 12px; color: var(--text-light); margin-top: 1px; }
.db-table tbody tr { cursor: pointer; }
.db-table tbody tr:hover td { background: var(--cream); }
.filter-tabs { display: flex; gap: 4px; padding: 14px 20px 0; }
.filter-tab {
  font-size: 12px; font-weight: 600; padding: 5px 14px;
  border-radius: 20px; cursor: pointer; border: 1px solid var(--cream-dark);
  background: var(--white); color: var(--text-mid);
}
.filter-tab.active { background: var(--text-dark); color: var(--white); border-color: var(--text-dark); }
.filter-tab:hover:not(.active) { background: var(--cream); }
</style>
@endpush

@section('content')

<p class="admin-portal-note">
  Maintenance invoices raised by service teams, sent to landlords, and paid through the platform.
  The 5% commission on platform-processed payments feeds into platform revenue.
</p>

<div class="db-stats">
  <div class="db-stat terra">
    <div class="db-stat-label">Total invoices</div>
    <div class="db-stat-value">{{ $stats['total'] }}</div>
    <div class="db-stat-sub">All time</div>
  </div>
  <div class="db-stat">
    <div class="db-stat-label">Sent · awaiting</div>
    <div class="db-stat-value" style="{{ $stats['sent'] > 0 ? 'color:#1565c0' : '' }}">
      {{ $stats['sent'] }}
    </div>
    <div class="db-stat-sub">Pending landlord payment</div>
  </div>
  <div class="db-stat green">
    <div class="db-stat-label">Paid</div>
    <div class="db-stat-value">{{ $stats['paid'] }}</div>
    <div class="db-stat-sub">Commission earned</div>
  </div>
  <div class="db-stat {{ $stats['overdue'] > 0 ? '' : 'green' }}">
    <div class="db-stat-label">Overdue</div>
    <div class="db-stat-value" style="{{ $stats['overdue'] > 0 ? 'color:#b71c1c' : '' }}">
      {{ $stats['overdue'] }}
    </div>
    <div class="db-stat-sub">Past due date, unpaid</div>
  </div>
</div>

<div class="db-card">
  <div class="db-card-header">
    <span class="db-card-title">Invoice register ({{ $stats['total'] }})</span>
  </div>

  @if($invoices->isEmpty())
    <div class="db-card-body">
      <div class="db-empty" style="padding:48px 20px">
        <div class="db-empty-icon">📄</div>
        <h3>No invoices yet</h3>
        <p style="max-width:400px;margin:0 auto">
          Maintenance invoices will appear here once service teams raise them against maintenance requests.
          Each invoice paid through the platform generates a 5% commission.
        </p>
      </div>
    </div>
  @else

  <div class="filter-tabs">
    @foreach([
      'all'           => ['All',           $stats['total']],
      'draft'         => ['Draft',         $stats['draft']],
      'sent'          => ['Sent',          $stats['sent']],
      'partially_paid'=> ['Partial',       $invoices->where('status','partially_paid')->count()],
      'paid'          => ['Paid',          $stats['paid']],
      'overdue'       => ['Overdue',       $stats['overdue']],
    ] as $key => [$label, $cnt])
    <button class="filter-tab {{ $key === 'all' ? 'active' : '' }}"
            onclick="filterInv('{{ $key }}')" data-filter="{{ $key }}">
      {{ $label }} <span style="opacity:.6;margin-left:3px">{{ $cnt }}</span>
    </button>
    @endforeach
  </div>

  <div class="db-card-body" style="padding:0;padding-top:14px">
    <div class="db-table-wrap">
      <table class="db-table" id="invTable">
        <thead>
          <tr>
            <th>Invoice #</th>
            <th>Team</th>
            <th>Property</th>
            <th>Landlord</th>
            <th style="text-align:right">Amount</th>
            <th>Status</th>
            <th>Due date</th>
            <th>Issued</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @foreach($invoices as $inv)
          @php
            $detailUrl = route('admin.maintenance-invoices.show', $inv);
            $isOverdue = $inv->status === 'sent' && $inv->due_date?->isPast();
            $displayStatus = $isOverdue ? 'overdue' : $inv->status;
          @endphp
          <tr onclick="window.location='{{ $detailUrl }}'"
              data-status="{{ $inv->status }}"
              data-overdue="{{ $isOverdue ? '1' : '0' }}">
            <td>
              <a href="{{ $detailUrl }}" class="t-name" style="font-family:monospace;font-size:13px" onclick="event.stopPropagation()">
                {{ $inv->invoice_number ?? 'INV-'.$inv->id }}
              </a>
            </td>
            <td>
              <div style="font-size:13px;font-weight:500;color:var(--text-dark)">{{ $inv->team?->name ?? '—' }}</div>
              @if($inv->team?->country_code)
                <div class="t-sub">{{ strtoupper($inv->team->country_code) }}</div>
              @endif
            </td>
            <td>
              @if($inv->property)
                <a href="{{ route('admin.properties.show', $inv->property) }}"
                   class="t-name" style="font-weight:500;font-size:13px" onclick="event.stopPropagation()">
                  {{ $inv->property->name }}
                </a>
              @else
                <span style="color:var(--text-light)">—</span>
              @endif
            </td>
            <td>
              @if($inv->landlord)
                <a href="{{ route('admin.landlords.show', $inv->landlord) }}"
                   style="font-size:13px;color:var(--text-mid);text-decoration:none" onclick="event.stopPropagation()">
                  {{ $inv->landlord->first_name }} {{ $inv->landlord->last_name }}
                </a>
              @else
                <span style="color:var(--text-light)">—</span>
              @endif
            </td>
            <td style="text-align:right;font-weight:600">
              {{ strtoupper($inv->currency_code) }} {{ number_format($inv->amount_minor / 100, 2) }}
            </td>
            <td>
              <span class="inv-status {{ $displayStatus }}">
                {{ $displayStatus === 'overdue' ? 'Overdue' : ucwords(str_replace('_', ' ', $inv->status)) }}
              </span>
            </td>
            <td style="font-size:12px;color:var(--text-mid);white-space:nowrap">
              {{ $inv->due_date?->format('d M Y') ?? '—' }}
            </td>
            <td style="font-size:12px;color:var(--text-light);white-space:nowrap">
              {{ $inv->issued_at?->format('d M Y') ?? $inv->created_at->format('d M Y') }}
            </td>
            <td style="text-align:right" onclick="event.stopPropagation()">
              <a href="{{ $detailUrl }}" class="db-table-link" style="font-size:12px">View →</a>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>

  @push('scripts')
  <script>
  function filterInv(filter) {
    document.querySelectorAll('.filter-tab').forEach(btn => {
      btn.classList.toggle('active', btn.dataset.filter === filter);
    });
    document.querySelectorAll('#invTable tbody tr').forEach(row => {
      let show = true;
      if (filter === 'overdue') show = row.dataset.overdue === '1';
      else if (filter !== 'all') show = row.dataset.status === filter;
      row.style.display = show ? '' : 'none';
    });
  }
  </script>
  @endpush

  @endif
</div>

@endsection
