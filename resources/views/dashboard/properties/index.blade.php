@extends('dashboard.layout')
@section('page-title', 'Properties')

@section('topbar-actions')
<div style="display:flex;align-items:center;gap:8px">
  <div style="display:flex;background:var(--cream-dark);border-radius:8px;padding:3px;gap:2px">
    <button onclick="setView('card')" id="btnCard" class="view-btn active" title="Card view">
      <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><rect x="1" y="1" width="6" height="6" rx="1.5" fill="currentColor"/><rect x="9" y="1" width="6" height="6" rx="1.5" fill="currentColor"/><rect x="1" y="9" width="6" height="6" rx="1.5" fill="currentColor"/><rect x="9" y="9" width="6" height="6" rx="1.5" fill="currentColor"/></svg>
    </button>
    <button onclick="setView('table')" id="btnTable" class="view-btn" title="Table view">
      <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><rect x="1" y="2" width="14" height="2.5" rx="1" fill="currentColor"/><rect x="1" y="6.5" width="14" height="2.5" rx="1" fill="currentColor" opacity=".5"/><rect x="1" y="11" width="14" height="2.5" rx="1" fill="currentColor" opacity=".5"/></svg>
    </button>
  </div>
  <button onclick="openPanel('new')" class="db-btn db-btn-primary">+ Add property</button>
</div>
@endsection

@push('styles')
<style>
/* ── VIEW TOGGLE ── */
.view-btn { display:flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:6px;border:none;background:transparent;color:var(--text-light);cursor:pointer;transition:all 0.15s; }
.view-btn.active { background:var(--white);color:var(--text-dark);box-shadow:0 1px 3px rgba(0,0,0,0.1); }
.view-btn:hover:not(.active) { color:var(--text-mid); }

.portfolio-tab { display:inline-block;padding:8px 16px;border-radius:6px;font-size:14px;font-weight:600;text-decoration:none;color:var(--text-mid);transition:all 0.15s; }
.portfolio-tab:hover { color:var(--text-dark); }
.portfolio-tab.active { background:var(--white);color:var(--text-dark);box-shadow:0 1px 3px rgba(0,0,0,0.08); }

/* ── CARD VIEW ── */
#cardView { display:grid;grid-template-columns:repeat(3,1fr);gap:16px; }
#tableView { display:none; }
@@media (max-width:1200px) { #cardView { grid-template-columns:repeat(2,1fr); } }
@@media (max-width:800px)  { #cardView { grid-template-columns:1fr; } }

/* ── PROPERTY CARD ── */
.prop-card { background:var(--white);border:1px solid var(--cream-dark);border-radius:var(--radius);padding:22px;transition:all 0.15s;cursor:pointer;position:relative;overflow:hidden;text-align:left; width:100%; font-family:'Outfit',sans-serif; }
.prop-card::before { content:'';position:absolute;top:0;left:0;right:0;height:3px;background:var(--terra);opacity:0;transition:opacity 0.2s; }
.prop-card:hover { border-color:rgba(196,98,45,0.3);box-shadow:0 4px 16px rgba(0,0,0,0.06);transform:translateY(-1px); }
.prop-card:hover::before { opacity:1; }
.prop-card-top { display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:14px; }
.prop-card-flag { font-size:28px;line-height:1; }
.prop-card-name { font-size:16px;font-weight:600;color:var(--text-dark);margin-bottom:3px;line-height:1.3; }
.prop-card-addr { font-size:13px;color:var(--text-light); }
.prop-card-divider { height:1px;background:var(--cream-dark);margin:14px 0; }
.prop-card-rent { font-family:'Fraunces',serif;font-size:22px;color:var(--text-dark);letter-spacing:-0.02em;margin-bottom:3px; }
.prop-card-meta { font-size:12px;color:var(--text-light);display:flex;gap:12px;flex-wrap:wrap;align-items:center; }
.prop-card-footer { display:flex;align-items:center;justify-content:space-between;margin-top:14px;padding-top:14px;border-top:1px solid var(--cream-dark);font-size:12px;color:var(--text-light); }

/* ── SLIDE PANEL ── */
#panelOverlay { position:fixed;inset:0;background:rgba(13,31,53,0.35);z-index:300;opacity:0;pointer-events:none;transition:opacity 0.25s; }
#panelOverlay.open { opacity:1;pointer-events:all; }

#slidePanel { position:fixed;top:0;right:0;bottom:0;width:62%;background:var(--white);z-index:301;transform:translateX(100%);transition:transform 0.28s cubic-bezier(.4,0,.2,1);display:flex;flex-direction:column;box-shadow:-8px 0 40px rgba(0,0,0,0.12); }
#slidePanel.open { transform:translateX(0); }

.panel-header { display:flex;align-items:center;justify-content:space-between;padding:20px 28px;border-bottom:1px solid var(--cream-dark);flex-shrink:0; }
.panel-title { font-family:'Fraunces',serif;font-size:20px;font-weight:500;color:var(--text-dark); }
.panel-close { width:34px;height:34px;border-radius:8px;border:1px solid var(--cream-dark);background:transparent;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:18px;color:var(--text-light);transition:all 0.15s; }
.panel-close:hover { background:var(--cream-dark);color:var(--text-dark); }

.panel-tabs { display:flex;border-bottom:1px solid var(--cream-dark);padding:0 28px;flex-shrink:0; }
.panel-tab { padding:12px 0;margin-right:24px;font-size:14px;font-weight:500;color:var(--text-light);cursor:pointer;border-bottom:2px solid transparent;transition:all 0.15s; }
.panel-tab.active { color:var(--terra);border-bottom-color:var(--terra); }
.panel-tab-badge { display:inline-block;background:var(--terra);color:#fff;font-size:10px;font-weight:700;padding:1px 5px;border-radius:100px;margin-left:4px;vertical-align:middle; }

.panel-media-grid { display:grid;grid-template-columns:repeat(auto-fill,minmax(130px,1fr));gap:12px; }
.panel-media-tile { position:relative;border-radius:10px;overflow:hidden;border:1px solid var(--cream-dark);aspect-ratio:4/3;background:var(--cream); }
.panel-media-tile img { width:100%;height:100%;object-fit:cover;display:block; }
.panel-media-del { position:absolute;top:6px;right:6px;width:28px;height:28px;border-radius:8px;border:none;background:rgba(13,31,53,.65);color:#fff;cursor:pointer;font-size:16px;line-height:1;display:flex;align-items:center;justify-content:center; }
.panel-media-del:hover { background:rgba(196,98,45,.9); }
.panel-video-list { display:flex;flex-direction:column;gap:16px; }
.panel-video-tile { border-radius:10px;overflow:hidden;border:1px solid var(--cream-dark);background:#0d1f35; }
.panel-video-tile video { width:100%;max-height:240px;display:block; }

.panel-body { flex:1;overflow-y:auto;padding:28px; }
.panel-section { margin-bottom:28px; }
.panel-section-title { font-size:11px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-light);margin-bottom:14px; }

.panel-footer { padding:16px 28px;border-top:1px solid var(--cream-dark);display:flex;align-items:center;justify-content:space-between;flex-shrink:0;background:var(--cream); }

/* Definition list — label column + value column (aligned, not stretched edge-to-edge) */
.detail-rows {
  max-width: 36rem;
  border: 1px solid var(--cream-dark);
  border-radius: 10px;
  padding: 2px 18px;
  background: var(--white);
}
.detail-row {
  display: grid;
  grid-template-columns: minmax(9rem, 40%) 1fr;
  column-gap: 1rem;
  align-items: baseline;
  padding: 11px 0;
  border-bottom: 1px solid var(--cream-dark);
}
.detail-row:last-child { border-bottom: none; }
.detail-label {
  font-size: 13px;
  font-weight: 500;
  color: var(--text-light);
  text-align: left;
}
.detail-value {
  font-size: 14px;
  font-weight: 500;
  color: var(--text-dark);
  text-align: left;
  line-height: 1.45;
  word-break: break-word;
}

/* Edit form */
.panel-form { display:flex;flex-direction:column;gap:16px; }
.panel-form-row { display:grid;grid-template-columns:1fr 1fr;gap:14px; }
.db-col-num { width:2.5rem;text-align:right;color:var(--text-light);font-variant-numeric:tabular-nums;font-size:12px;font-weight:600; }

/* Multi-unit accordion rows */
.multi-acc-toggle { min-width:32px; }
.multi-acc-chevron { display:inline-block;transition:transform 0.2s;font-size:10px;color:var(--text-light); }
.multi-acc-chevron.open { transform:rotate(180deg); }
.multi-acc-body { padding:16px 20px 20px; }
/* accordion edit form */
.bacc-form-grid { display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:12px 20px;margin-top:2px; }
.bacc-field { display:flex;flex-direction:column;gap:4px; }
.bacc-label { font-size:11px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:var(--text-light); }
</style>
@endpush

@push('scripts')
<script>
window.toggleMultiAcc = function (id) {
  const row     = document.getElementById('multi-acc-' + id);
  const chevron = document.getElementById('chevron-' + id);
  if (!row) return;
  const open = row.style.display === 'none' || row.style.display === '';
  row.style.display = open ? 'table-row' : 'none';
  if (chevron) chevron.classList.toggle('open', open);
};

window.bEdit = function (id) {
  document.getElementById('bview-' + id).style.display = 'none';
  document.getElementById('bedit-' + id).style.display = 'block';
  document.getElementById('bact-view-' + id).style.display = 'none';
  document.getElementById('bact-edit-' + id).style.display = 'flex';
};

window.bCancel = function (id) {
  document.getElementById('bview-' + id).style.display = 'block';
  document.getElementById('bedit-' + id).style.display = 'none';
  document.getElementById('bact-view-' + id).style.display = 'block';
  document.getElementById('bact-edit-' + id).style.display = 'none';
  const errEl = document.getElementById('bsave-err-' + id);
  if (errEl) { errEl.style.display = 'none'; errEl.textContent = ''; }
};

window.bSave = async function (id) {
  const form   = document.getElementById('bedit-' + id);
  const errEl  = document.getElementById('bsave-err-' + id);
  const saveBtn = form.closest('.multi-acc-body').querySelector('[onclick^="bSave"]');
  const data   = new FormData(form);
  data.set('sublet_allowed', form.querySelector('[name="sublet_allowed"]').checked ? '1' : '0');
  const body   = Object.fromEntries(data.entries());
  errEl.style.display = 'none';
  if (saveBtn) { saveBtn.disabled = true; saveBtn.textContent = 'Saving…'; }
  try {
    const res = await fetch('/properties/' + id, {
      method: 'PATCH',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        'Accept': 'application/json',
      },
      body: JSON.stringify(body),
    });
    const json = await res.json();
    if (!res.ok || !json.success) {
      const msgs = json.errors ? Object.values(json.errors).flat().join(' ') : (json.message || 'Save failed.');
      errEl.textContent = msgs;
      errEl.style.display = 'block';
    } else {
      window.location.reload();
    }
  } catch (e) {
    errEl.textContent = 'Network error. Please try again.';
    errEl.style.display = 'block';
  } finally {
    if (saveBtn) { saveBtn.disabled = false; saveBtn.textContent = 'Save'; }
  }
};
</script>
@endpush

@section('content')

@php
use App\Support\CurrencyDisplay;
$flags = ["FR"=>"🇫🇷","GB"=>"🇬🇧","US"=>"🇺🇸","IN"=>"🇮🇳","DE"=>"🇩🇪","AU"=>"🇦🇺","CA"=>"🇨🇦","NG"=>"🇳🇬","ID"=>"🇮🇩","PH"=>"🇵🇭","BR"=>"🇧🇷","MX"=>"🇲🇽","ZA"=>"🇿🇦","KE"=>"🇰🇪","SG"=>"🇸🇬","JP"=>"🇯🇵","ES"=>"🇪🇸","IT"=>"🇮🇹","NL"=>"🇳🇱","PT"=>"🇵🇹","BE"=>"🇧🇪","SE"=>"🇸🇪","NO"=>"🇳🇴","DK"=>"🇩🇰","PL"=>"🇵🇱","CH"=>"🇨🇭","MY"=>"🇲🇾","TH"=>"🇹🇭","VN"=>"🇻🇳","SG"=>"🇸🇬","HK"=>"🇭🇰","NZ"=>"🇳🇿"];
$portfolio = $portfolio ?? 'single';
$stats = $stats ?? ['total' => 0, 'single_unit' => 0, 'multi_unit' => 0, 'in_view' => 0, 'rent_in_view' => []];
$rentInView = $stats['rent_in_view'] ?? [];
@endphp

@if($stats['total'] > 0)
<div class="db-stats">
  <div class="db-stat">
    <div class="db-stat-label">Properties</div>
    <div class="db-stat-value">{{ $stats['total'] }}</div>
  </div>
  <div class="db-stat">
    <div class="db-stat-label">Single-unit</div>
    <div class="db-stat-value">{{ $stats['single_unit'] }}</div>
  </div>
  <div class="db-stat">
    <div class="db-stat-label">Multi-unit</div>
    <div class="db-stat-value">{{ $stats['multi_unit'] }}</div>
  </div>
  <div class="db-stat">
    <div class="db-stat-label">Monthly rent</div>
    <div class="db-stat-value" style="font-size:15px">
      {{ $totalHomeRentMinor > 0 ? CurrencyDisplay::formatMinor($totalHomeRentMinor, $homeCurrency) : '—' }}
    </div>
    <div style="font-size:11px;color:var(--text-light);margin-top:2px">{{ $homeCurrency }}</div>
  </div>
</div>
@endif

<div style="display:flex;align-items:center;flex-wrap:wrap;gap:12px;margin-bottom:20px">
  <div style="display:flex;background:var(--cream-dark);border-radius:8px;padding:3px;gap:2px">
    <a href="{{ route('properties.index', ['portfolio' => 'single']) }}" class="portfolio-tab {{ $portfolio === 'single' ? 'active' : '' }}">Single-unit</a>
    <a href="{{ route('properties.index', ['portfolio' => 'multi']) }}" class="portfolio-tab {{ $portfolio === 'multi' ? 'active' : '' }}">Multi-unit</a>
  </div>
</div>

@if($properties->isEmpty())
  <div class="db-empty" style="min-height:50vh">
    <div class="db-empty-icon">🏠</div>
    @if($portfolio === 'multi')
      <h3>No multi-unit buildings yet.</h3>
      <p>Apartment buildings and properties with multiple licensed units show in this tab. Switch to Single-unit for houses and standalone rentals.</p>
    @else
      <h3>No single-unit properties yet.</h3>
      <p>Houses, condos, and other standalone rentals show here. Use the Multi-unit tab for buildings with per-unit leasing.</p>
    @endif
    <button onclick="openPanel('new')" class="db-btn db-btn-primary">+ Add property</button>
  </div>
@else

{{-- ── CARD VIEW ── --}}
<div id="cardView">
  @foreach($properties as $p)
  @php
    $flag = $flags[$p->country_code] ?? "🏠";
    $isMulti = $portfolio === 'multi';
    $statusBadge = $p->displayStatusBadgeClass();
    $statusLabel = $p->portfolioStatusLabel();
    $lease = $isMulti ? null : $p->leases->where('status','active')->first();
    $rentMinor = $isMulti ? null : $p->displayMonthlyRentMinor();
  @endphp
  <button type="button" class="prop-card" data-property-id="{{ $p->id }}" @if($isMulti) onclick="window.location.href='{{ route('properties.show', $p) }}'" @else onclick="openPanel('{{ $p->id }}')" @endif>
    <div class="prop-card-top">
      <span class="prop-card-flag">{{ $flag }}</span>
      <span class="badge {{ $statusBadge }}">{{ $statusLabel }}</span>
    </div>
    <div class="prop-card-name">{{ $p->name }}</div>
    <div class="prop-card-addr">{{ $p->city }}, {{ $p->country_code }}</div>
    @if(!$isMulti)
    <div style="font-size:11px;color:var(--text-light);margin-top:4px">{{ $p->rental_mode === 'long_term' ? 'LTR' : 'STR' }} · {{ $p->listing_visibility === 'public' ? 'Public' : 'Private' }}</div>
    @endif
    <div class="prop-card-divider"></div>
    @if($isMulti)
      <div class="prop-card-rent" style="font-size:15px;color:var(--text-mid)">{{ $p->portfolioUnitsLabel() }} units</div>
    @elseif($rentMinor)
      <div class="prop-card-rent">{{ CurrencyDisplay::formatMinor($rentMinor, $p->currency_code) }} <span style="font-size:15px;color:var(--text-light)">/mo</span></div>
      @if($lease)
      <div class="prop-card-meta">{{ $lease->tenant->first_name ?? '—' }} {{ $lease->tenant->last_name ?? '' }}</div>
      @endif
    @else
      <div class="prop-card-rent" style="font-size:15px;color:var(--text-light)">—</div>
    @endif
    <div class="prop-card-footer">
      <span>{{ $isMulti ? $p->portfolioUnitsLabel().' units' : '1 unit' }}</span>
      @if(!$isMulti)<span>{{ ucfirst($p->type) }}{{ $p->bedrooms ? ' · '.$p->bedrooms.'bd' : '' }}</span>@endif
    </div>
  </button>
  @endforeach
</div>

{{-- ── TABLE VIEW ── --}}
<div id="tableView">
  <div class="db-card">
    <div class="db-table-wrap">
      <table class="db-table">
        <thead>
          @if($portfolio === 'multi')
          <tr><th class="db-col-num">#</th><th>Property</th><th>Units</th><th>Country</th><th>Status</th><th></th></tr>
          @else
          <tr><th class="db-col-num">#</th><th>Property</th><th>Type</th><th>Listing</th><th>Units</th><th>Country</th><th>Lessee</th><th>Rent</th><th>Status</th></tr>
          @endif
        </thead>
        <tbody>
          @foreach($properties as $p)
          @php
            $isMulti = $portfolio === 'multi';
            $lease = $isMulti ? null : $p->leases->where('status','active')->first();
            $rentMinor = $isMulti ? null : $p->displayMonthlyRentMinor();
          @endphp
          @if($isMulti)
          <tr data-property-id="{{ $p->id }}" style="cursor:default">
            <td class="db-col-num">{{ $loop->iteration }}</td>
            <td>
              <div style="font-weight:600;color:var(--text-dark)">{{ $p->name }}</div>
              <div style="font-size:12px;color:var(--text-light)">{{ $p->city }}</div>
            </td>
            <td>{{ $p->portfolioUnitsLabel() }}</td>
            <td>{{ $p->country_code }}</td>
            <td><span class="badge {{ $p->displayStatusBadgeClass() }}">{{ $p->portfolioStatusLabel() }}</span></td>
            <td style="text-align:right;white-space:nowrap">
              <button type="button" class="db-btn db-btn-ghost multi-acc-toggle" style="font-size:12px;padding:4px 10px" onclick="toggleMultiAcc('{{ $p->id }}')" title="Building info">
                <span class="multi-acc-chevron" id="chevron-{{ $p->id }}">▼</span>
              </button>
              <a href="{{ route('properties.show', $p) }}" class="db-btn db-btn-ghost" style="font-size:12px;padding:4px 10px" onclick="event.stopPropagation()">View →</a>
            </td>
          </tr>
          <tr class="multi-acc-row" id="multi-acc-{{ $p->id }}" style="display:none;background:var(--cream)">
            <td colspan="6" style="padding:0">
              <div class="multi-acc-body">
                {{-- header --}}
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px">
                  <span style="font-size:12px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--text-light)">Property info</span>
                  <div style="display:flex;gap:6px">
                    <div id="bact-view-{{ $p->id }}">
                      <button type="button" class="db-btn db-btn-ghost" style="font-size:12px;padding:4px 12px" onclick="bEdit('{{ $p->id }}')">Edit</button>
                    </div>
                    <div id="bact-edit-{{ $p->id }}" style="display:none;gap:6px">
                      <button type="button" class="db-btn db-btn-ghost" style="font-size:12px;padding:4px 12px" onclick="bCancel('{{ $p->id }}')">Cancel</button>
                      <button type="button" class="db-btn db-btn-primary" style="font-size:12px;padding:4px 12px" onclick="bSave('{{ $p->id }}')">Save</button>
                    </div>
                  </div>
                </div>

                {{-- view mode --}}
                <div id="bview-{{ $p->id }}" class="detail-rows" style="max-width:100%">
                  @foreach([
                    ['Status',          $p->portfolioStatusLabel()],
                    ['Licensed units',  $p->unit_capacity ?? '—'],
                    ['Active leases',   $p->leases->count()],
                    ['Country',         $p->country_code],
                    ['Currency',        $p->currency_code],
                    ['Address',         $p->address_line1.', '.$p->city],
                    ['Type',            ucfirst($p->type)],
                    ['Sublet',          $p->sublet_allowed ? 'Allowed' : 'Not allowed'],
                  ] as [$lbl, $val])
                  <div class="detail-row">
                    <span class="detail-label">{{ $lbl }}</span>
                    <span class="detail-value">{{ $val }}</span>
                  </div>
                  @endforeach
                </div>

                {{-- edit form --}}
                <form id="bedit-{{ $p->id }}" style="display:none" onsubmit="return false">
                  <input type="hidden" name="occupancy_mode" value="multi">
                  <input type="hidden" name="country_code"   value="{{ $p->country_code }}">
                  <div class="bacc-form-grid">
                    <div class="bacc-field">
                      <label class="bacc-label">Name</label>
                      <input class="db-input" name="name" value="{{ $p->name }}">
                    </div>
                    <div class="bacc-field">
                      <label class="bacc-label">Licensed units</label>
                      <input class="db-input" type="number" name="unit_capacity" min="1" max="999" value="{{ $p->unit_capacity }}">
                    </div>
                    <div class="bacc-field">
                      <label class="bacc-label">Address</label>
                      <input class="db-input" name="address_line1" value="{{ $p->address_line1 }}">
                    </div>
                    <div class="bacc-field">
                      <label class="bacc-label">City</label>
                      <input class="db-input" name="city" value="{{ $p->city }}">
                    </div>
                    <div class="bacc-field">
                      <label class="bacc-label">Postal code</label>
                      <input class="db-input" name="postal_code" value="{{ $p->postal_code }}">
                    </div>
                    <div class="bacc-field">
                      <label class="bacc-label">Type</label>
                      <select class="db-input" name="type">
                        @foreach(['apartment'=>'Apartment','house'=>'House','commercial'=>'Commercial','other'=>'Other'] as $v=>$l)
                        <option value="{{ $v }}" {{ $p->type === $v ? 'selected' : '' }}>{{ $l }}</option>
                        @endforeach
                      </select>
                    </div>
                    <div class="bacc-field">
                      <label class="bacc-label">Rental mode</label>
                      <select class="db-input" name="rental_mode">
                        <option value="long_term"  {{ $p->rental_mode === 'long_term'  ? 'selected' : '' }}>Long-term</option>
                        <option value="short_term" {{ $p->rental_mode === 'short_term' ? 'selected' : '' }}>Short-term</option>
                      </select>
                    </div>
                    <div class="bacc-field">
                      <label class="bacc-label">Directory</label>
                      <select class="db-input" name="listing_visibility">
                        <option value="public"  {{ $p->listing_visibility === 'public'  ? 'selected' : '' }}>Public</option>
                        <option value="private" {{ $p->listing_visibility === 'private' ? 'selected' : '' }}>Private</option>
                      </select>
                    </div>
                    <div class="bacc-field" style="flex-direction:row;align-items:center;gap:8px;padding-top:6px">
                      <input type="checkbox" id="bsubl-{{ $p->id }}" name="sublet_allowed" value="1" {{ $p->sublet_allowed ? 'checked' : '' }} style="width:16px;height:16px;accent-color:var(--terra)">
                      <label for="bsubl-{{ $p->id }}" class="bacc-label" style="margin:0;text-transform:none;font-size:14px;font-weight:500;color:var(--text-dark)">Allow sublet</label>
                    </div>
                  </div>
                  <div id="bsave-err-{{ $p->id }}" style="display:none;margin-top:10px;color:#c0392b;font-size:13px"></div>
                </form>
              </div>
            </td>
          </tr>
          @else
          <tr data-property-id="{{ $p->id }}" onclick="openPanel('{{ $p->id }}')" style="cursor:pointer">
            <td class="db-col-num">{{ $loop->iteration }}</td>
            <td>
              <div style="font-weight:600;color:var(--text-dark)">{{ $p->name }}</div>
              <div style="font-size:12px;color:var(--text-light)">{{ $p->city }}</div>
            </td>
            <td>{{ $p->rental_mode === 'long_term' ? 'LTR' : 'STR' }}</td>
            <td>{{ $p->listing_visibility === 'public' ? 'Public' : 'Private' }}</td>
            <td>{{ $p->portfolioUnitsLabel() }}</td>
            <td>{{ $p->country_code }}</td>
            <td>
              @if($lease)
                <strong>{{ $lease->tenant->first_name }} {{ $lease->tenant->last_name }}</strong>
              @else
                <span style="color:var(--text-light)">—</span>
              @endif
            </td>
            <td data-property-rent>@if($rentMinor)<strong>{{ CurrencyDisplay::formatMinorWithCode($rentMinor, $p->currency_code) }}</strong>@else<span style="color:var(--text-light)">—</span>@endif</td>
            <td><span class="badge {{ $p->displayStatusBadgeClass() }}">{{ $p->displayStatusLabel() }}</span></td>
          </tr>
          @endif
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>

@endif


@include('dashboard.properties.partials.property-manage-panel', ['panelProperties' => $properties, 'flags' => $flags])

@endsection

