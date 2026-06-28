@extends('dashboard.layout')
@section('page-title', 'Invoices')
@section('breadcrumb', 'Finance')
@section('content')
<p class="db-form-hint" style="margin:0 0 18px;max-width:42rem;line-height:1.55">Maintenance invoices sent to you. Review details and approve payment here. Payments → Maintenance shows the same ledger with status only.</p>

@if(session('success'))
  <div class="db-alert" style="background:var(--green-pale);color:var(--green);margin-bottom:16px">{{ session('success') }}</div>
@endif
@if(session('error'))
  <div class="db-alert" style="background:var(--red-pale);color:var(--red);margin-bottom:16px">{{ session('error') }}</div>
@endif

<div class="db-card">
  <div class="db-card-header" style="flex-wrap:wrap;gap:10px">
    <span class="db-card-title">Received invoices ({{ $invoices->count() }})</span>
    <form method="GET" style="display:flex;gap:8px;margin-left:auto">
      <select name="status" class="db-select" style="font-size:13px;width:auto" onchange="this.form.submit()">
        <option value="">All</option>
        <option value="awaiting" @selected(request('status') === 'awaiting')>Awaiting approval</option>
        @foreach(['sent','partially_paid','paid','cancelled'] as $s)
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
            <th>Invoice</th>
            <th>From</th>
            <th>Property</th>
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
              <a href="{{ route('landlord.invoices.show', $inv) }}" class="db-table-link"><strong>{{ $inv->invoice_number }}</strong></a>
              @if($inv->description)<br><span style="font-size:13px;color:var(--text-light)">{{ Str::limit($inv->description, 36) }}</span>@endif
            </td>
            <td>{{ $inv->team?->name ?? '—' }}</td>
            <td>{{ $inv->property?->name ?: $inv->property?->address_line1 ?? '—' }}</td>
            <td>{{ $inv->formattedAmount() }}</td>
            <td>{{ $inv->due_date?->format('d M Y') ?? '—' }}</td>
            <td><span class="badge badge-{{ $inv->statusBadgeClass() }}">{{ $inv->landlordStatusLabel() }}</span></td>
            <td>
              <a href="{{ route('landlord.invoices.show', $inv) }}" class="db-btn db-btn-ghost" style="font-size:13px;text-decoration:none">View</a>
              @if($inv->needsLandlordApproval())
                <a href="{{ route('landlord.invoices.show', $inv) }}#approve" class="db-btn db-btn-primary" style="font-size:13px;text-decoration:none">Approve</a>
              @endif
            </td>
          </tr>
          @empty
          <tr><td colspan="7" style="padding:24px;color:var(--text-light)">No maintenance invoices yet.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
