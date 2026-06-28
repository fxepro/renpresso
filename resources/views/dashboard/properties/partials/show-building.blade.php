@php
  use App\Support\CurrencyDisplay;
  use App\Models\Lease;
  $activeLeases = $property->leases->where('status', 'active')
    ->sortBy(fn ($l) => sprintf('%06d|%s', (int) $l->unit_seq, strtolower($l->unit_label ?? '')));
  $payments = $property->leases->flatMap->payments->sortByDesc('due_date')->take(20);
  $startEditing = request()->boolean('edit');
  $countries = collect(config('countries', []))->map(fn ($v, $k) => ['code' => $k, 'label' => $k.' — '.($v['currency'] ?? '')])->values();
@endphp

<div class="db-card" style="margin-bottom:20px">
  <div class="db-card-header" style="align-items:flex-start;flex-direction:column;gap:6px">
    <span class="db-card-title">{{ $property->name }}</span>
    <p style="font-size:14px;color:var(--text-mid);line-height:1.5;margin:0;max-width:48rem">
      Use the unit panel for lease, rent, payments, and media per unit.
    </p>
  </div>
</div>

<div class="db-grid-2" style="margin-bottom:16px">

  {{-- ── Building info card ── --}}
  <div class="db-card">
    <div class="db-card-header">
      <span class="db-card-title">Building info</span>
      <div id="buildingViewActions" @if($startEditing) style="display:none" @endif>
        <button type="button" class="db-btn db-btn-ghost" style="font-size:13px" onclick="buildingEditToggle(true)">Edit</button>
      </div>
      <div id="buildingEditActions" style="display:none;gap:8px" @if($startEditing) style="display:flex;gap:8px" @endif>
        <button type="button" class="db-btn db-btn-ghost" style="font-size:13px" onclick="buildingEditToggle(false)">Cancel</button>
        <button type="button" class="db-btn db-btn-primary" style="font-size:13px" id="buildingSaveBtn" onclick="buildingSave()">Save</button>
      </div>
    </div>

    {{-- View mode --}}
    <div class="db-card-body" id="buildingViewBody" @if($startEditing) style="display:none" @endif>
      <div class="detail-rows">
        @foreach([
          ['Status',         $property->portfolioStatusLabel()],
          ['Licensed units', $property->unit_capacity ? (string)(int)$property->unit_capacity : '—'],
          ['Active leases',  (string)$activeLeases->count()],
          ['Country',        $property->country_code],
          ['Currency',       $property->currency_code],
          ['Address',        $property->address_line1.', '.$property->city],
        ] as [$label, $val])
        <div class="detail-row">
          <span class="detail-label">{{ $label }}</span>
          <span class="detail-value">{{ $val }}</span>
        </div>
        @endforeach
      </div>
    </div>

    {{-- Edit mode --}}
    <div class="db-card-body" id="buildingEditBody" @if(!$startEditing) style="display:none" @endif>
      <form id="buildingEditForm">
        <input type="hidden" name="occupancy_mode" value="multi">
        <div class="panel-form-row">
          <div class="db-form-group">
            <label class="db-label">Building name</label>
            <input type="text" name="name" class="db-input" value="{{ $property->name }}" required>
          </div>
          <div class="db-form-group">
            <label class="db-label">Country</label>
            <select name="country_code" class="db-select" required>
              @foreach($countries as $c)
                <option value="{{ $c['code'] }}" @selected($property->country_code === $c['code'])>{{ $c['label'] }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="db-form-group">
          <label class="db-label">Address</label>
          <input type="text" name="address_line1" class="db-input" value="{{ $property->address_line1 }}" required>
        </div>
        <div class="panel-form-row">
          <div class="db-form-group">
            <label class="db-label">City</label>
            <input type="text" name="city" class="db-input" value="{{ $property->city }}" required>
          </div>
          <div class="db-form-group">
            <label class="db-label">Postal code</label>
            <input type="text" name="postal_code" class="db-input" value="{{ $property->postal_code }}">
          </div>
        </div>
        <div class="db-form-group">
          <label class="db-label">Licensed units</label>
          <input type="number" name="unit_capacity" class="db-input" min="1" max="999" value="{{ $property->unit_capacity }}" placeholder="e.g. 12" required>
          <span style="font-size:12px;color:var(--text-light)">At least {{ $activeLeases->count() }} (active leases).</span>
        </div>
      </form>
    </div>
  </div>

  {{-- ── Units card ── --}}
  <div class="db-card">
    <div class="db-card-header"><span class="db-card-title">Units</span></div>
    @if(!empty($unitSlotsPayload))
    <div class="db-table-wrap">
      <table class="db-table">
        <thead>
          <tr><th>#</th><th>Label</th><th>Lessee</th><th>Rent</th><th>Status</th><th></th></tr>
        </thead>
        <tbody>
          @foreach($unitSlotsPayload as $u)
          @php $lease = $u['lease'] ?? null; @endphp
          <tr>
            <td style="color:var(--text-light);font-size:13px">#{{ $u['seq'] }}</td>
            <td><strong>{{ $u['displayLabel'] }}</strong></td>
            <td style="font-size:13px">{{ $lease?->tenant?->fullName() ?? '—' }}</td>
            <td style="font-size:13px">{{ $lease ? number_format($lease->rent_minor_units / 100, 2).' '.$lease->currency_code : '—' }}</td>
            <td>
              @if($u['leased'])
                <span class="badge badge-green">Active</span>
              @else
                <span class="badge badge-grey">Vacant</span>
              @endif
            </td>
            <td style="text-align:right">
              <button type="button" class="db-table-link" onclick="openPanel('{{ $property->id }}', { unitSeq: {{ $u['seq'] }} })">Open →</button>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    @else
    <div class="db-empty" style="padding:28px">
      <p style="margin:0 0 12px;color:var(--text-mid)">Set licensed unit capacity to list units here.</p>
      <button type="button" class="db-btn db-btn-primary" style="font-size:13px" onclick="buildingEditToggle(true)">Edit building</button>
    </div>
    @endif
  </div>
</div>

{{-- ── Payment history ── --}}
<div class="db-card">
  <div class="db-card-header"><span class="db-card-title">Payment history</span></div>
  @if($payments->isEmpty())
    <div class="db-empty" style="padding:28px"><p>No payments yet.</p></div>
  @else
  <div class="db-table-wrap">
    <table class="db-table">
      <thead><tr><th>Date</th><th>Unit</th><th>Tenant</th><th>Amount</th><th>Status</th></tr></thead>
      <tbody>
        @foreach($payments as $pay)
        <tr>
          <td>{{ $pay->due_date?->format('d M Y') }}</td>
          <td>{{ $pay->lease->displayUnit() }}</td>
          <td>{{ $pay->lease->tenant->first_name ?? '—' }}</td>
          <td><strong>{{ CurrencyDisplay::formatMinor($pay->amount_minor_units, $pay->currency_code) }}</strong></td>
          <td><span class="badge badge-{{ $pay->status === 'success' ? 'green' : ($pay->status === 'failed' ? 'red' : 'gold') }}">{{ ucfirst($pay->status) }}</span></td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @endif
</div>

@push('scripts')
<script>
(function () {
  const updateUrl = @json(route('properties.update', $property));
  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

  window.buildingEditToggle = function (editing) {
    document.getElementById('buildingViewBody').style.display   = editing ? 'none' : '';
    document.getElementById('buildingEditBody').style.display   = editing ? '' : 'none';
    document.getElementById('buildingViewActions').style.display = editing ? 'none' : '';
    document.getElementById('buildingEditActions').style.display = editing ? 'flex' : 'none';
  };

  @if($startEditing)
  document.addEventListener('DOMContentLoaded', function () { buildingEditToggle(true); });
  @endif

  window.buildingSave = async function () {
    const form = document.getElementById('buildingEditForm');
    const btn  = document.getElementById('buildingSaveBtn');
    if (!form || !btn) return;
    const data = new FormData(form);
    data.set('_method', 'PUT');
    data.set('_token', csrf);
    if (!data.get('sublet_allowed')) data.set('sublet_allowed', '0');
    if (!data.get('sublet_landlord_approval_required')) data.set('sublet_landlord_approval_required', '0');
    btn.disabled = true; btn.textContent = 'Saving…';
    try {
      const res = await fetch(updateUrl, {
        method: 'POST', body: data, credentials: 'same-origin',
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf },
      });
      const raw = await res.text();
      let payload = null;
      try { payload = raw ? JSON.parse(raw) : null; } catch (e) {}
      if (res.ok && payload?.success) {
        if (typeof window.rmToast === 'function') window.rmToast('Building updated.', 'success');
        else alert('Building updated.');
        location.reload();
        return;
      }
      const err = payload?.message || (payload?.errors ? Object.values(payload.errors).flat().join(' ') : '') || 'Save failed.';
      if (typeof window.rmToast === 'function') window.rmToast(err, 'error');
      else alert(err);
    } catch (e) {
      if (typeof window.rmToast === 'function') window.rmToast('Network error.', 'error');
      else alert('Network error.');
    } finally {
      btn.disabled = false; btn.textContent = 'Save';
    }
  };
})();
</script>
@endpush
