{{-- ── SLIDE PANEL ── --}}
<div id="panelOverlay" onclick="closePanel()"></div>
<div id="slidePanel">
  <div class="panel-header">
    <span class="panel-title" id="panelTitle">Property</span>
    <button class="panel-close" onclick="closePanel()">✕</button>
  </div>
  <div class="panel-tabs" id="panelTabs">
    <div class="panel-tab active" data-tab="details" onclick="showTab('details')">Details</div>
    <div class="panel-tab" data-tab="rent" onclick="showTab('rent')">Rent</div>
    <div class="panel-tab" data-tab="applications" onclick="showTab('applications')">Applications <span class="panel-tab-badge" id="appBadge"></span></div>
    <div class="panel-tab" data-tab="background" onclick="showTab('background')">Background</div>
    <div class="panel-tab" data-tab="lease" onclick="showTab('lease')" id="panelTabLease">Leases</div>
    <div class="panel-tab" data-tab="subleases" onclick="showTab('subleases')" id="panelTabSubleases">Sub-leases</div>
    <div class="panel-tab" data-tab="payments" onclick="showTab('payments')">Payments</div>
    <div class="panel-tab" data-tab="photos" onclick="showTab('photos')">Photos</div>
    <div class="panel-tab" data-tab="videos" onclick="showTab('videos')">Videos</div>
  </div>
  <div class="panel-body" id="panelBody">
    <div style="display:flex;align-items:center;justify-content:center;height:200px;color:var(--text-light)">Loading…</div>
  </div>
  <div class="panel-footer" id="panelFooter">
    <span id="panelFooterExtra"></span>
    <div id="panelFooterStd" style="display:flex;gap:8px;margin-left:auto">
      <button type="button" id="panelEditPropertyBtn" class="db-btn db-btn-ghost">Edit property</button>
      <button type="button" id="panelSaveBtn" class="db-btn db-btn-primary">Save</button>
    </div>
  </div>
</div>

{{-- Property data for JS --}}
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
if (!CSRF) {
  console.error('{{ config('app.name') }}: missing csrf-token meta tag in dashboard layout <head>.');
}
function rmNotify(message, variant, duration) {
  if (typeof window.rmToast === 'function') {
    window.rmToast(message, variant || 'info', duration);
  } else if (message) {
    alert(message);
  }
}
const PANEL_TAB_ORDER = ['details','rent','applications','background','lease','subleases','payments','photos','videos'];

function fmtUnitSeq(seq, isMulti) {
  if (!isMulti) return '—';
  const n = parseInt(String(seq), 10);
  return n > 0 ? String(n) : '—';
}
function fmtUnitLabel(label) {
  const s = String(label ?? '').trim();
  return (s === '' || s === '0') ? '—' : s;
}
function fmtDueOrdinal(due) {
  const d = parseInt(String(due), 10);
  if (d === 1) return '1st';
  if (d === 2) return '2nd';
  if (d === 3) return '3rd';
  return d + 'th';
}

const PROPS = {
  @foreach($panelProperties as $p)
  @php
    $isMultiProp = ($p->occupancy_mode ?? 'single') === 'multi';
    $activeLeases = $p->leases->where('status','active')->sortBy(fn ($l) => sprintf('%06d|%s', (int) $l->unit_seq, strtolower($l->unit_label ?? '')));
    $primaryLease = $isMultiProp ? null : $activeLeases->first();
    $lease = $primaryLease;
    $flag  = $flags[$p->country_code] ?? "🏠";
    $c     = config("countries.".$p->country_code, []);

    $leasePanelFields = fn ($l) => [
      'id' => $l->id,
      'unitSeq' => (int) $l->unit_seq,
      'unitLabel' => $l->unit_label,
      'unitSeqDisplay' => $l->displayUnitSeq(),
      'unitLabelDisplay' => $l->displayUnitLabel(),
      'rent' => \App\Support\CurrencyDisplay::formatMinorWithCode($l->rent_minor_units, $l->currency_code),
      'currency' => $l->currency_code,
      'due' => $l->due_day,
      'dueOrdinal' => \App\Models\Lease::ordinalDay((int) $l->due_day),
      'graceDays' => (int) $l->grace_period_days,
      'lateFeeDay' => $l->lateFeeDayOfMonth(),
      'lateFeeOrdinal' => \App\Models\Lease::ordinalDay($l->lateFeeDayOfMonth()),
      'lateFee' => $l->formattedLateFee(),
      'paymentSchedule' => $l->formattedPaymentSchedule(),
      'start' => $l->start_date->format('d M Y'),
      'end' => $l->end_date?->format('d M Y') ?? 'Rolling',
      'lessee' => $l->tenant?->fullName() ?? '—',
      'tenant' => $l->tenant?->fullName() ?? '—',
      'email' => $l->tenant?->email ?? '',
      'status' => $l->status,
      'showUrl' => route('leases.show', $l),
    ];

    $propLeasesJson = $activeLeases->map($leasePanelFields)->values();

    $propPrimaryLeaseJson = $primaryLease ? $leasePanelFields($primaryLease) : null;

    $propSubLeasesJson = $p->subLeases
        ->whereIn('status', ['active', 'pending_landlord_approval', 'draft'])
        ->map(fn ($s) => [
            'id' => $s->id,
            'label' => $s->label,
            'sublessee' => $s->subletter?->fullName() ?? '—',
            'email' => $s->subletter?->email ?? '',
            'rent' => number_format($s->rent_minor_units /100,2).' '.$s->currency_code,
            'start' => $s->start_date->format('d M Y'),
            'status' => $s->status,
            'parentLessee' => $s->parentLease?->tenant?->fullName() ?? '—',
            'unitLabel' => $s->parentLease?->unit_label,
            'approveUrl' => $s->status === 'pending_landlord_approval' ? route('sub-leases.approve', $s) : null,
            'rejectUrl' => in_array($s->status, ['pending_landlord_approval', 'draft'], true) ? route('sub-leases.reject', $s) : null,
        ])->values();

    $propPhotosJson = $p->getMedia('photos')->map(fn ($m) => [
      'id' => $m->id,
      'url' => $m->getUrl(),
      'name' => $m->file_name,
    ])->values();

    $propVideosJson = $p->getMedia('videos')->map(fn ($m) => [
      'id' => $m->id,
      'url' => $m->getUrl(),
      'name' => $m->file_name,
    ])->values();
    $disp = $p->displayPayload();
  @endphp
  "{{ $p->id }}": {
    id:       "{{ $p->id }}",
    name:     @json($p->name),
    flag:     "{{ $flag }}",
    country:  "{{ $p->country_code }}",
    currency: "{{ $p->currency_code }}",
    city:     @json($p->city),
    address:  @json($p->address_line1),
    postal:   @json($p->postal_code ?? ''),
    type:     "{{ $p->type }}",
    bedrooms: "{{ $p->bedrooms ?? '—' }}",
    occupancyMode: "{{ $p->occupancy_mode ?? 'single' }}",
    unitCapacity: {{ $p->unit_capacity ? (int)$p->unit_capacity : 'null' }},
    rentalMode: "{{ $p->rental_mode }}",
    listingVisibility: "{{ $p->listing_visibility }}",
    subletAllowed: {{ $p->sublet_allowed ? 'true' : 'false' }},
    subletBgCheck: true,
    subletApproval: {{ $p->sublet_landlord_approval_required ? 'true' : 'false' }},
    subLeases: @json($propSubLeasesJson),
    dbStatus: @json($disp['status_db']),
    statusLabel: @json($disp['status_label']),
    portfolioStatusLabel: @json($disp['portfolio_status_label']),
    statusBadge: @json($disp['status_badge']),
    leaseCount: {{ $disp['active_lease_count'] }},
    leases: @json($propLeasesJson),
    lease: @json($propPrimaryLeaseJson),
    createLeaseUrl: "{{ route('leases.create', $p) }}",
    messagesUrl: "{{ route('messages.property', $p) }}",
    unitsDirectoryUrl: @json($isMultiProp ? route('properties.show', $p) : null),
    editUrl:   "{{ route('properties.update', $p) }}",
    unitSlotBaseUrl: @json($isMultiProp ? url('/properties/'.$p->id.'/units/') : null),
    unitSlotsMeta: @json($p->unit_slots_meta ?? []),
    rentUpdateUrl: "{{ route('properties.rent.update', $p) }}",
    residenceCurrency: "{{ strtoupper($p->currency_code) }}",
    homeCurrency: @json(strtoupper(auth()->user()->home_currency ?? 'USD')),
    rentDecimals: {{ \App\Support\CurrencyDisplay::decimalPlaces($p->currency_code) }},
    rentAmountStep: @json(\App\Support\CurrencyDisplay::amountStep($p->currency_code)),
    baseRentMinor: {{ $p->base_rent_minor_units ?? 'null' }},
    rentTotalMinor: {{ $p->rent_minor_units ?? 'null' }},
    rentTotalDisplay: @json(\App\Support\CurrencyDisplay::formatMinor($p->rent_minor_units, $p->currency_code)),
    payoutGap: null,
    rentChargeLines: @json($p->normalizedRentChargeLines()),
    deleteUrl: "{{ route('properties.destroy', $p) }}",
    photos: @json($propPhotosJson),
    videos: @json($propVideosJson),
    photoUploadUrl: @json(route('properties.media.photos.store', $p)),
    videoUploadUrl: @json(route('properties.media.videos.store', $p)),
    mediaBaseUrl: @json(url('/properties/'.$p->id.'/media')),
  },
  @endforeach
};

@php
  $__countries = [];
  foreach (config('countries', []) as $__k => $__v) {
      $__countries[] = ['code' => $__k, 'label' => $__k.' — '.($__v['currency'] ?? '')];
  }
@endphp
const COUNTRIES = @json($__countries);

const APPS = {
  @foreach($panelProperties as $p)
  "{{ $p->id }}": [
    @foreach($p->applications->sortByDesc('created_at') as $app)
    {
      id:      "{{ $app->id }}",
      name:    @json($app->first_name.' '.$app->last_name),
      email:   @json($app->email),
      phone:   @json($app->phone ?? ''),
      moveIn:  "{{ $app->move_in_date?->format('d M Y') ?? '—' }}",
      income:  "{{ $app->monthly_income_minor_units ? number_format($app->monthly_income_minor_units/100,2).' '.($app->income_currency??'') : '—' }}",
      message: @json($app->message ?? ''),
      status:  "{{ $app->status }}",
      notes:   @json($app->landlord_notes ?? ''),
      targetUnitLabel: @json($app->target_unit_label ?? ''),
      checks:  [
        @foreach($app->backgroundChecks as $chk)
        { id:"{{ $chk->id }}", type:"{{ $chk->type }}", method:"{{ $chk->method }}", status:"{{ $chk->status }}", notes:@json($chk->notes??''), completed:"{{ $chk->completed_at?->format('d M Y')??'—' }}" },
        @endforeach
      ],
      statusUrl: "{{ route('applications.status', $app) }}",
      checkUrl:  "{{ route('background-checks.store', $app) }}",
    },
    @endforeach
  ],
  @endforeach
};

let activeTab = 'details';
let activeId  = null;
let detailsEditing = false;
/** When set (multi-unit), panel was opened from a licensed-slot row — title + Applications + Leases use this context */
let panelFocusUnitSeq = null;
let panelBuildingOnly = false;

function isBuildingPanelOnly(p) {
  return !!(p && p.occupancyMode === 'multi' && panelFocusUnitSeq == null && panelBuildingOnly);
}
function isUnitPanel(p) {
  return !!(p && p.occupancyMode === 'multi' && panelFocusUnitSeq != null);
}
function leaseForUnit(p, seq) {
  return (p.leases || []).find(u => parseInt(String(u.unitSeq), 10) === parseInt(String(seq), 10)) || null;
}

function applyPanelTabsForContext(p) {
  const buildingOnly = isBuildingPanelOnly(p);
  document.querySelectorAll('#panelTabs .panel-tab').forEach(el => {
    const tab = el.dataset.tab;
    if (buildingOnly) {
      el.style.display = tab === 'details' ? '' : 'none';
    } else {
      el.style.display = '';
    }
  });
  const leaseTab = document.getElementById('panelTabLease');
  if (leaseTab) {
    if (isUnitPanel(p)) leaseTab.textContent = 'Primary lease';
    else if (p.occupancyMode === 'multi') leaseTab.textContent = 'Units & leases';
    else leaseTab.textContent = 'Primary lease';
  }
}

function openPanel(id, opts) {
  if (typeof window.rmCloseUnitPropertyPanel === 'function') {
    window.rmCloseUnitPropertyPanel();
  }
  activeId = id;
  const overlay = document.getElementById('panelOverlay');
  const panel   = document.getElementById('slidePanel');
  const tabs    = document.getElementById('panelTabs');
  if (!overlay || !panel || !tabs) {
    console.error('{{ config('app.name') }}: property panel (#panelOverlay / #slidePanel) is missing.');
    return;
  }
  panelFocusUnitSeq = null;
  panelBuildingOnly = false;
  if (id !== 'new' && opts && opts.unitSeq != null && opts.unitSeq !== '') {
    const n = parseInt(String(opts.unitSeq), 10);
    if (!Number.isNaN(n)) panelFocusUnitSeq = n;
  }
  if (id !== 'new' && opts && opts.buildingOnly) panelBuildingOnly = true;
  overlay.classList.add('open');
  panel.classList.add('open');
  if (id === 'new') {
    document.getElementById('panelTitle').textContent = 'Add property';
    tabs.style.display = 'none';
    showNewForm();
  } else {
    const p = PROPS[id];
    if (!p) {
      console.error('{{ config('app.name') }}: unknown property id for panel:', id);
      closePanel();
      return;
    }
    let titleExtra = '';
    if (p.occupancyMode === 'multi' && panelFocusUnitSeq != null) {
      const row = (p.leases || []).find(u => parseInt(String(u.unitSeq), 10) === panelFocusUnitSeq);
      const lbl = row && row.unitLabel ? String(row.unitLabel) : '';
      titleExtra = ' · Unit #' + panelFocusUnitSeq + (lbl ? ' (' + lbl + ')' : '');
    }
    if (isUnitPanel(p)) {
      p.lease = leaseForUnit(p, panelFocusUnitSeq);
    }
    document.getElementById('panelTitle').innerHTML = p.flag + ' ' + p.name + titleExtra;
    tabs.style.display = 'flex';
    applyPanelTabsForContext(p);
    const appCount = (APPS[id] || []).length;
    const badge = document.getElementById('appBadge');
    badge.textContent = appCount > 0 ? appCount : '';
    badge.style.display = appCount > 0 ? 'inline-block' : 'none';
    detailsEditing = !!(opts && (opts.edit || opts.tab === 'edit'));
    let initialTab = (opts && opts.tab && opts.tab !== 'edit') ? opts.tab : 'details';
    if (opts && opts.applicationId) activeAppId = String(opts.applicationId);
    showTab(initialTab);
  }
  document.addEventListener('keydown', escHandler);
}

function closePanel() {
  const overlay = document.getElementById('panelOverlay');
  const slide = document.getElementById('slidePanel');
  if (overlay) overlay.classList.remove('open');
  if (slide) slide.classList.remove('open');
  document.removeEventListener('keydown', escHandler);
  activeId = null;
  panelFocusUnitSeq = null;
  panelBuildingOnly = false;
  detailsEditing = false;
}

function escHandler(e) { if (e.key === 'Escape') closePanel(); }

function updatePanelFooter() {
  const std = document.getElementById('panelFooterStd');
  const extra = document.getElementById('panelFooterExtra');
  const editBtn = document.getElementById('panelEditPropertyBtn');
  const saveBtn = document.getElementById('panelSaveBtn');
  if (!std || !editBtn || !saveBtn) return;
  if (activeId === 'new' || !activeId) {
    std.style.display = 'none';
    return;
  }
  if (extra) extra.innerHTML = '';
  std.style.display = 'flex';
  const p = PROPS[activeId];
  const buildingOnly = p && isBuildingPanelOnly(p);
  editBtn.textContent = detailsEditing ? 'Cancel' : (buildingOnly ? 'Edit building' : 'Edit property');
  saveBtn.textContent = 'Save';
  saveBtn.disabled = false;
  const hideSave = buildingOnly && !detailsEditing;
  saveBtn.style.display = hideSave ? 'none' : '';
}

function panelEditClick() {
  if (activeId === 'new' || !activeId) return;
  if (detailsEditing) cancelDetailsEdit();
  else startDetailsEdit();
}

function panelSaveClick() {
  const p = PROPS[activeId];
  if (!p || activeId === 'new') return;
  if (detailsEditing) {
    saveProperty(activeId);
    return;
  }
  if (isBuildingPanelOnly(p)) return;
  if (activeTab === 'rent' && document.getElementById('rentScheduleForm')) {
    saveRentSchedule(p);
  }
}

function displayRentForProperty(p) {
  const minor = p.rentTotalMinor;
  const code = residenceCurrencyCode(p);
  const dec = rentDecimalsFor(p);
  if (minor == null || minor <= 0) {
    return { cardHtml: '<span style="font-size:15px;color:var(--text-light)">—</span>', tableHtml: '<span style="color:var(--text-light)">—</span>' };
  }
  const amt = formatMoneyAmount(minor / 100, code, dec);
  const withCode = amt + ' ' + code;
  return {
    cardHtml: amt + ' <span style="font-size:15px;color:var(--text-light)">/mo</span>',
    tableHtml: '<strong>' + escHtml(withCode) + '</strong>',
  };
}

function refreshRentStatsFromProps() {
  const portfolio = new URLSearchParams(window.location.search).get('portfolio') || 'single';
  const totals = {};
  Object.values(PROPS).forEach(p => {
    if (portfolio === 'multi' && p.occupancyMode !== 'multi') return;
    if (portfolio === 'single' && p.occupancyMode !== 'single') return;
    const minor = p.rentTotalMinor;
    if (minor > 0) {
      const cc = residenceCurrencyCode(p).toUpperCase();
      totals[cc] = (totals[cc] || 0) + minor;
    }
  });
  const el = document.querySelector('.db-stats .db-stat:last-child .db-stat-value');
  if (!el) return;
  const entries = Object.entries(totals);
  if (!entries.length) {
    el.innerHTML = '—';
    return;
  }
  el.innerHTML = entries.map(([cc, minor]) => {
    const dec = ['JPY', 'KRW', 'VND'].includes(cc) ? 0 : 2;
    return '<div>' + escHtml(formatMoneyAmount(minor / 100, cc, dec)) + '</div>';
  }).join('');
}

function syncPropsRentToList(p) {
  const d = displayRentForProperty(p);
  document.querySelectorAll('[data-property-id="' + p.id + '"]').forEach(el => {
    const cardRent = el.querySelector('.prop-card-rent');
    if (cardRent) cardRent.innerHTML = d.cardHtml;
    const tableRent = el.querySelector('[data-property-rent]');
    if (tableRent) tableRent.innerHTML = d.tableHtml;
  });
  refreshRentStatsFromProps();
}

function applyPrimaryLeaseRentToProps(p, minor, display) {
  if (p.lease) {
    p.lease.rent = display || p.lease.rent;
    if (minor != null) {
      const code = residenceCurrencyCode(p);
      const dec = rentDecimalsFor(p);
      p.lease.rent = formatMoneyAmount(minor / 100, code, dec);
    }
  }
  if (Array.isArray(p.leases) && p.leases[0] && minor != null) {
    const code = residenceCurrencyCode(p);
    const dec = rentDecimalsFor(p);
    p.leases[0].rent = formatMoneyAmount(minor / 100, code, dec);
  }
}

function setActivePanelTab(tab) {
  document.querySelectorAll('.panel-tab').forEach(t => {
    t.classList.toggle('active', t.dataset.tab === tab);
  });
}

function startDetailsEdit() {
  detailsEditing = true;
  activeTab = 'details';
  setActivePanelTab('details');
  const p = PROPS[activeId];
  if (p) renderDetails(p);
  updatePanelFooter();
}

function cancelDetailsEdit() {
  detailsEditing = false;
  const p = PROPS[activeId];
  if (p) renderDetails(p);
  updatePanelFooter();
}

function showTab(tab) {
  const p = PROPS[activeId];
  if (p && isBuildingPanelOnly(p) && tab !== 'details') {
    tab = 'details';
  }
  if (tab !== 'details') detailsEditing = false;
  activeTab = tab;
  setActivePanelTab(tab);
  if (!p) return;
  applyPanelTabsForContext(p);

  if (tab === 'details')      renderDetails(p);
  if (tab === 'rent')         renderRent(p);
  if (tab === 'applications') renderApplications(p);
  if (tab === 'background')   renderBackground(p);
  if (tab === 'lease')        renderLease(p);
  if (tab === 'subleases')    renderSubleases(p);
  if (tab === 'payments')     renderPayments(p);
  if (tab === 'photos')       renderPhotos(p);
  if (tab === 'videos')       renderVideos(p);
  updatePanelFooter();
}

function primaryLeaseRecord(p) {
  if (isUnitPanel(p)) {
    return leaseForUnit(p, panelFocusUnitSeq) || null;
  }
  if (p.occupancyMode === 'multi') return null;
  if (p.lease && ((p.lease.lessee && p.lease.lessee !== '—') || p.lease.email)) return p.lease;
  const first = (p.leases || [])[0];
  return first || null;
}

function residenceCurrencyCode(p) {
  return (p && p.residenceCurrency) ? p.residenceCurrency : 'USD';
}
function homeCurrencyCode(p) {
  return (p && p.homeCurrency) ? p.homeCurrency : 'USD';
}
function rentDecimalsFor(p) {
  return (p && typeof p.rentDecimals === 'number') ? p.rentDecimals : 2;
}
function currencySymbol(code) {
  const map = { USD:'$', EUR:'€', GBP:'£', INR:'₹', IDR:'Rp', NGN:'₦', JPY:'¥', CAD:'CA$', AUD:'A$', SGD:'S$', CHF:'CHF ' };
  return map[code] || (code + ' ');
}
function formatMoneyAmount(amount, code, decimals) {
  const n = Number(amount);
  if (!n && n !== 0) return '—';
  const sym = currencySymbol(code);
  const dec = decimals != null ? decimals : (['JPY','KRW','VND'].includes(code) ? 0 : 2);
  return sym + n.toLocaleString('en-US', { minimumFractionDigits: dec, maximumFractionDigits: dec });
}
function rentAmountInputHtml(amount, sym, inputName, step) {
  const val = amount !== '' && amount != null ? amount : '';
  const nameAttr = inputName ? ` name="${inputName}"` : '';
  const stepAttr = step || '0.01';
  const minAttr = stepAttr === '1' ? '0' : '0';
  return `<span class="rent-amt-wrap"><span class="rent-amt-sym">${escHtml(sym)}</span><input type="number" class="rent-amt-input"${nameAttr} min="${minAttr}" step="${stepAttr}" value="${val}" placeholder="0"></span>`;
}
function rentChargeRowHtml(line, sym, idx, p) {
  const dec = rentDecimalsFor(p);
  const amt = line && line.amount_minor_units ? (line.amount_minor_units / 100).toFixed(dec) : '';
  const label = line && line.label ? line.label : '';
  const isPreset = line && line.key && ['trash', 'water_sewer'].includes(line.key);
  return `<tr class="rent-charge-row" data-idx="${idx}">
    <td>${isPreset
      ? `<span class="rent-line-label-text">${escHtml(label)}</span><input type="hidden" class="rent-line-label" value="${escHtml(label)}">`
      : `<input type="text" class="rent-line-label rent-table-input" value="${escHtml(label)}" placeholder="Charge name">`}</td>
    <td class="rent-amt-cell">${rentAmountInputHtml(amt, sym, '', p.rentAmountStep)}</td>
    <td class="rent-amt-actions">${isPreset ? '' : `<button type="button" class="db-btn db-btn-ghost rent-line-remove" title="Remove">✕</button>`}</td>
  </tr>`;
}

function collectRentChargeLinesFromForm() {
  const rows = document.querySelectorAll('.rent-charge-row');
  const lines = [];
  rows.forEach(row => {
    const label = (row.querySelector('.rent-line-label')?.value || '').trim();
    if (!label) return;
    const amount = parseFloat(row.querySelector('.rent-amt-input')?.value || '0') || 0;
    lines.push({ label, amount });
  });
  return lines;
}

function previewRentTotal(p) {
  const code = residenceCurrencyCode(p);
  const dec = rentDecimalsFor(p);
  const base = parseFloat(document.querySelector('#rentScheduleForm input[name="base_rent_amount"]')?.value || '0') || 0;
  let sum = base;
  collectRentChargeLinesFromForm().forEach(l => { sum += l.amount; });
  const el = document.getElementById('rentTotalPreview');
  if (el) {
    el.textContent = sum > 0 ? formatMoneyAmount(sum, code, dec) : '—';
  }
  return sum;
}

function wireRentTabEvents(p) {
  const form = document.getElementById('rentScheduleForm');
  if (!form) return;
  form.addEventListener('input', () => previewRentTotal(p));
  document.getElementById('addRentChargeBtn')?.addEventListener('click', () => {
    const wrap = document.getElementById('rentChargeLinesBody');
    const sym = currencySymbol(residenceCurrencyCode(p));
    const idx = wrap.querySelectorAll('.rent-charge-row').length;
    wrap.insertAdjacentHTML('beforeend', rentChargeRowHtml({ label: '', amount_minor_units: 0, key: 'custom_' + idx }, sym, idx + 1, p));
    wireRentRemoveButtons(p);
    previewRentTotal(p);
  });
  wireRentRemoveButtons(p);
}

function wireRentRemoveButtons(p) {
  document.querySelectorAll('.rent-line-remove').forEach(btn => {
    btn.onclick = () => {
      btn.closest('.rent-charge-row')?.remove();
      previewRentTotal(p);
    };
  });
}

async function saveRentSchedule(p) {
  const baseVal = document.querySelector('#rentScheduleForm input[name="base_rent_amount"]')?.value;
  const payload = {
    base_rent_amount: baseVal === '' ? null : baseVal,
    charge_lines: collectRentChargeLinesFromForm(),
  };
  const btn = document.getElementById('panelSaveBtn');
  if (btn) { btn.disabled = true; btn.textContent = 'Saving…'; }
  try {
    const res = await fetch(p.rentUpdateUrl, {
      method: 'PATCH',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': CSRF,
      },
      body: JSON.stringify(payload),
    });
    const data = await res.json();
    if (!res.ok) throw new Error(data.message || 'Save failed');
    p.baseRentMinor = data.base_rent_minor_units;
    p.rentTotalMinor = data.rent_minor_units;
    p.rentTotalDisplay = data.total_rent_display;
    p.rentChargeLines = data.rent_charge_lines || [];
    if (data.display_currency) p.residenceCurrency = data.display_currency;
    if (data.rent_decimals != null) p.rentDecimals = data.rent_decimals;
    if (data.rent_amount_step) p.rentAmountStep = data.rent_amount_step;
    if (data.rent_minor_units != null) {
      applyPrimaryLeaseRentToProps(p, data.rent_minor_units, data.total_rent_display);
    }
    syncPropsRentToList(p);
    rmNotify('Saved.', 'success');
    renderRent(p);
  } catch (e) {
    rmNotify(e.message || 'Save failed.', 'error');
  } finally {
    if (btn) { btn.disabled = false; btn.textContent = 'Save'; }
    updatePanelFooter();
  }
}

function renderRent(p) {
  if (isBuildingPanelOnly(p)) {
    document.getElementById('panelBody').innerHTML = `
      <div class="panel-section" style="max-width:28rem">
        <p style="font-size:14px;color:var(--text-mid);line-height:1.55;margin:0">
          Rent is set <strong>per unit</strong> (units can differ — e.g. 1-bed vs 3-bed).
          Close this panel and use <strong>Open unit →</strong> on the building page.
        </p>
      </div>`;
    return;
  }
  if (isUnitPanel(p)) {
    const l = leaseForUnit(p, panelFocusUnitSeq);
    if (!l) {
      const createUrl = p.createLeaseUrl + (p.createLeaseUrl.includes('?') ? '&' : '?') + 'unit_seq=' + panelFocusUnitSeq;
      document.getElementById('panelBody').innerHTML = `
        <div class="panel-section" style="text-align:center;padding:32px 0">
          <p style="color:var(--text-mid);margin-bottom:16px">No lease yet — set rent when you create the lease.</p>
          <a href="${createUrl}" class="db-btn db-btn-primary">+ Create lease</a>
        </div>`;
      return;
    }
    document.getElementById('panelBody').innerHTML = `
      <div class="panel-section">
        <div class="panel-section-title">Rent (this unit)</div>
        <div class="detail-rows">
          <div class="detail-row"><span class="detail-label">Monthly rent</span><span class="detail-value">${escHtml(l.rent)} ${escHtml(l.currency)}</span></div>
        </div>
        <p style="font-size:13px;color:var(--text-light);margin-top:12px">Change rent on the <strong>Primary lease</strong> tab or <a href="${l.showUrl}">lease record →</a>.</p>
      </div>`;
    return;
  }
  const code = residenceCurrencyCode(p);
  const home = homeCurrencyCode(p);
  const sym = currencySymbol(code);
  const dec = rentDecimalsFor(p);
  const baseAmt = p.baseRentMinor != null ? (p.baseRentMinor / 100).toFixed(dec) : '';
  const lines = p.rentChargeLines || [];
  const chargeRows = lines.map((line, i) => rentChargeRowHtml(line, sym, i, p)).join('');
  const totalDisplay = p.rentTotalDisplay || '—';
  const fxNote = home !== code
    ? `<p style="font-size:12px;color:var(--text-light);margin:0 0 12px">Residence currency <strong>${escHtml(code)}</strong> (tenant pays here). FX ledger &amp; reports use <strong>${escHtml(home)}</strong>.</p>`
    : '';

  document.getElementById('panelBody').innerHTML = `
    <div class="panel-section">
      <div class="panel-section-title">Rent schedule</div>
      <p style="font-size:13px;color:var(--text-mid);line-height:1.55;margin-bottom:12px">One line per charge in <strong>${escHtml(code)}</strong>. Total prefills leases; tenants pay in residence currency.</p>
      ${fxNote}
      <form id="rentScheduleForm">
        <div class="db-table-wrap">
          <table class="db-table rent-schedule-table">
            <thead>
              <tr>
                <th>Item</th>
                <th style="text-align:right;width:160px">Monthly (${escHtml(code)})</th>
                <th style="width:44px"></th>
              </tr>
            </thead>
            <tbody id="rentChargeLinesBody">
              <tr class="rent-row-base">
                <td><strong>Base rent</strong></td>
                <td class="rent-amt-cell">${rentAmountInputHtml(baseAmt, sym, 'base_rent_amount', p.rentAmountStep)}</td>
                <td></td>
              </tr>
              ${chargeRows}
            </tbody>
            <tfoot>
              <tr class="rent-row-total">
                <td><strong>Total monthly rent</strong></td>
                <td id="rentTotalPreview" style="text-align:right;font-family:'Fraunces',serif;font-size:20px;font-weight:500;color:var(--text-dark)">${escHtml(totalDisplay)}</td>
                <td></td>
              </tr>
            </tfoot>
          </table>
        </div>
        <button type="button" id="addRentChargeBtn" class="db-btn db-btn-ghost" style="margin-top:12px">+ Add line</button>
      </form>
    </div>
    <style>
      .rent-schedule-table .rent-table-input,
      .rent-schedule-table .rent-amt-input { width:100%; padding:6px 8px; border:1px solid var(--cream-dark); border-radius:6px; font-family:inherit; font-size:14px; background:var(--white); }
      .rent-schedule-table .rent-amt-cell { text-align:right; }
      .rent-amt-wrap { display:inline-flex; align-items:center; justify-content:flex-end; gap:4px; width:100%; max-width:140px; margin-left:auto; }
      .rent-amt-sym { font-weight:600; color:var(--text-mid); flex-shrink:0; }
      .rent-amt-input { max-width:100px; text-align:right; }
      .rent-schedule-table tfoot td { border-top:2px solid var(--cream-dark); padding-top:14px; }
      .rent-line-label-text { font-weight:500; color:var(--text-dark); }
      .rent-amt-actions { text-align:center; }
      .rent-amt-actions .db-btn { padding:4px 8px; font-size:13px; min-width:auto; }
    </style>`;

  wireRentTabEvents(p);
  previewRentTotal(p);
}

function renderDetails(p) {
  if (detailsEditing) {
    renderDetailsEdit(p);
    return;
  }
  const buildingOnly = isBuildingPanelOnly(p);
  const isMulti = p.occupancyMode === 'multi';
  const isUnit = isUnitPanel(p);
  const pl = primaryLeaseRecord(p);
  const lesseeLine = (!buildingOnly && !isMulti && pl && pl.lessee && pl.lessee !== '—')
    ? pl.lessee + (pl.email ? ' · ' + pl.email : '')
    : null;
  const slotBanner = isUnit
    ? `<div style="background:var(--gold-pale);border:1px solid rgba(201,150,58,0.35);border-radius:10px;padding:12px 14px;margin-bottom:18px;font-size:13px;color:var(--text-dark)">
        <strong>Unit #${panelFocusUnitSeq}</strong>${pl && pl.unitLabelDisplay && pl.unitLabelDisplay !== '—' ? ' · ' + escHtml(pl.unitLabelDisplay) : ''} — same tabs as a single-unit property.
      </div>`
    : (buildingOnly ? `<div style="background:var(--cream);border:1px solid var(--cream-dark);border-radius:10px;padding:12px 14px;margin-bottom:18px;font-size:13px;color:var(--text-mid)">
        <strong style="color:var(--text-dark)">Building only.</strong> Name, address, licensed capacity, listing. Rent and lessee are edited per unit on the building page.
      </div>` : '');
  let rows;
  if (buildingOnly) {
    rows = [
      ['Status', p.portfolioStatusLabel || p.statusLabel || '—'],
      ['Licensed units', p.unitCapacity ? String(p.unitCapacity) : '—'],
      ['Active leases', String(p.leaseCount ?? 0)],
      ['Country', p.country],
      ['Currency', p.currency],
      ['Address', p.address + (p.postal ? ', '+p.postal : '')],
      ['City', p.city],
      ['Type', p.type.charAt(0).toUpperCase()+p.type.slice(1)],
      ['Rental length', (p.rentalMode === 'long_term') ? 'Long-term (lease)' : 'Short-term (nightly / flexible)'],
      ['Directory', (p.listingVisibility === 'public') ? 'Public listing' : 'Private (not in directory)'],
      ['Sublet', p.subletAllowed ? 'Allowed' : 'Not allowed'],
    ];
  } else if (isUnit) {
    const unitStatus = pl ? 'Active' : 'Vacant';
    const slotMeta = (p.unitSlotsMeta && p.unitSlotsMeta[String(panelFocusUnitSeq)]) || {};
    const unitBeds = slotMeta.bedrooms != null
      ? (slotMeta.bedrooms === 0 ? 'Studio' : slotMeta.bedrooms + ' bed')
      : '—';
    rows = [
      ['Unit', '#'+panelFocusUnitSeq+(pl && pl.unitLabelDisplay && pl.unitLabelDisplay !== '—' ? ' · '+pl.unitLabelDisplay : '')],
      ['Status', unitStatus],
      ['Country', p.country],
      ['Currency', p.currency],
      ['Address', p.address + (p.postal ? ', '+p.postal : '')],
      ['City', p.city],
      ['Type', p.type.charAt(0).toUpperCase()+p.type.slice(1)],
      ['Bedrooms', unitBeds],
      ['Rental length', (p.rentalMode === 'long_term') ? 'Long-term (lease)' : 'Short-term'],
      ['Directory', (p.listingVisibility === 'public') ? 'Public' : 'Private'],
      ['Sublet', p.subletAllowed ? 'Allowed' : 'Not allowed'],
    ];
    if (pl && pl.lessee && pl.lessee !== '—') {
      rows.splice(2, 0, ['Lessee', pl.lessee + (pl.email ? ' · '+pl.email : '')]);
    }
  } else {
    const unitLine = isMulti
      ? (p.unitCapacity ? `${p.leaseCount}/${p.unitCapacity} units` : `${p.leaseCount} unit${p.leaseCount === 1 ? '' : 's'}`)
      : (p.statusLabel || '—');
    rows = [
      ['Status', p.statusLabel || '—'],
      ['Active leases', String(p.leaseCount ?? 0)],
      ['Country', p.country],
      ['Currency', p.currency],
      ['Address', p.address + (p.postal ? ', '+p.postal : '')],
      ['City', p.city],
      ['Type', p.type.charAt(0).toUpperCase()+p.type.slice(1)],
      ['Occupancy', isMulti ? 'Multi-unit building' : 'Single unit'],
      ['Units', unitLine],
      ['Bedrooms', p.bedrooms],
      ['Rental length', (p.rentalMode === 'long_term') ? 'Long-term (lease)' : 'Short-term (nightly / flexible)'],
      ['Total monthly rent', p.rentTotalDisplay || '—'],
      ['Directory', (p.listingVisibility === 'public') ? 'Public listing' : 'Private (not in directory)'],
      ['Sublet', p.subletAllowed ? ('Allowed · max ' + (p.bedrooms !== '—' ? p.bedrooms : '?') + ' sub-letters') : 'Not allowed'],
      ['Sublet rules', p.subletAllowed ? ('Background check required · ' + (p.subletApproval ? 'Landlord approval' : 'No landlord approval')) : '—'],
    ];
    if (lesseeLine) rows.splice(2, 0, ['Lessee', lesseeLine]);
  }
  const sectionTitle = buildingOnly ? 'Building info' : 'Property info';
  document.getElementById('panelBody').innerHTML = `
    ${slotBanner}
    <div class="panel-section">
      <div class="panel-section-title">${sectionTitle}</div>
      <div class="detail-rows">
      ${rows.map(([l,v])=>`<div class="detail-row"><span class="detail-label">${escHtml(l)}</span><span class="detail-value">${escHtml(String(v))}</span></div>`).join('')}
      </div>
    </div>`;
}

function renderDetailsEdit(p) {
  const buildingOnly = isBuildingPanelOnly(p);
  const countryOpts = COUNTRIES.map(c=>`<option value="${c.code}" ${c.code===p.country?'selected':''}>${c.label}</option>`).join('');
  const bedroomsField = buildingOnly ? '' : `
        <div class="db-form-group">
          <label style="font-size:12px;font-weight:600;color:var(--text-dark);margin-bottom:5px;display:block">Bedrooms</label>
          <input type="number" name="bedrooms" class="db-input" value="${p.bedrooms==='—'?'':p.bedrooms}" min="0" max="99">
        </div>`;
  const occupancyLocked = buildingOnly
    ? `<input type="hidden" name="occupancy_mode" value="multi">`
    : `<div class="db-form-group">
          <label style="font-size:12px;font-weight:600;color:var(--text-dark);margin-bottom:5px;display:block">Occupancy</label>
          <select name="occupancy_mode" class="db-select" onchange="var w=document.getElementById('editUnitCapacityWrap'); if(w) w.style.display=this.value==='multi'?'block':'none'">
            <option value="single" ${p.occupancyMode==='single'?'selected':''}>Single unit — one lease</option>
            <option value="multi" ${p.occupancyMode==='multi'?'selected':''}>Multi-unit — licensed slots</option>
          </select>
        </div>`;
  document.getElementById('panelBody').innerHTML = `
    ${buildingOnly ? '<p style="font-size:13px;color:var(--text-mid);margin:0 0 14px;max-width:32rem">Building-level fields only. Open a unit from the building page for rent and lease.</p>' : ''}
    <form id="editForm" class="panel-form">
      <div class="panel-form-row">
        <div class="db-form-group">
          <label style="font-size:12px;font-weight:600;color:var(--text-dark);margin-bottom:5px;display:block">Property name</label>
          <input type="text" name="name" class="db-input" value="${escHtml(p.name)}" required>
        </div>
        <div class="db-form-group">
          <label style="font-size:12px;font-weight:600;color:var(--text-dark);margin-bottom:5px;display:block">Country</label>
          <select name="country_code" class="db-select">${countryOpts}</select>
        </div>
      </div>
      <div class="db-form-group">
        <label style="font-size:12px;font-weight:600;color:var(--text-dark);margin-bottom:5px;display:block">Address</label>
        <input type="text" name="address_line1" class="db-input" value="${escHtml(p.address)}" required>
      </div>
      <div class="panel-form-row">
        <div class="db-form-group">
          <label style="font-size:12px;font-weight:600;color:var(--text-dark);margin-bottom:5px;display:block">City</label>
          <input type="text" name="city" class="db-input" value="${escHtml(p.city)}" required>
        </div>
        <div class="db-form-group">
          <label style="font-size:12px;font-weight:600;color:var(--text-dark);margin-bottom:5px;display:block">Postal code</label>
          <input type="text" name="postal_code" class="db-input" value="${escHtml(p.postal)}">
        </div>
      </div>
      <div class="panel-form-row">
        <div class="db-form-group">
          <label style="font-size:12px;font-weight:600;color:var(--text-dark);margin-bottom:5px;display:block">Type</label>
          <select name="type" class="db-select">
            ${['apartment','house','commercial','other'].map(t=>`<option value="${t}" ${t===p.type?'selected':''}>${t.charAt(0).toUpperCase()+t.slice(1)}</option>`).join('')}
          </select>
        </div>
        ${bedroomsField}
      </div>
      <div class="panel-form-row">
        <div class="db-form-group">
          <label style="font-size:12px;font-weight:600;color:var(--text-dark);margin-bottom:5px;display:block">Rental length</label>
          <select name="rental_mode" class="db-select">
            <option value="long_term" ${p.rentalMode==='long_term'?'selected':''}>Long-term (lease)</option>
            <option value="short_term" ${p.rentalMode==='short_term'?'selected':''}>Short-term (nightly / flexible)</option>
          </select>
        </div>
        <div class="db-form-group">
          <label style="font-size:12px;font-weight:600;color:var(--text-dark);margin-bottom:5px;display:block">Directory</label>
          <select name="listing_visibility" class="db-select">
            <option value="private" ${p.listingVisibility==='private'?'selected':''}>Private</option>
            <option value="public" ${p.listingVisibility==='public'?'selected':''}>Public listing</option>
          </select>
        </div>
      </div>
      <div class="panel-form-row" style="border-top:1px solid var(--cream-dark);padding-top:16px;margin-top:8px">
        ${occupancyLocked}
        <div class="db-form-group" id="editUnitCapacityWrap" style="display:${(buildingOnly || p.occupancyMode==='multi')?'block':'none'}">
          <label style="font-size:12px;font-weight:600;color:var(--text-dark);margin-bottom:5px;display:block">Licensed unit slots</label>
          <input type="number" name="unit_capacity" class="db-input" min="1" max="999" value="${p.unitCapacity != null && p.unitCapacity !== '' ? escHtml(String(p.unitCapacity)) : ''}" placeholder="e.g. 24">
          <span style="font-size:12px;color:var(--text-light)">Cannot be less than active leases (${p.leaseCount} now).</span>
        </div>
      </div>
      <div style="border-top:1px solid var(--cream-dark);padding-top:16px;margin-top:8px">
        <div class="panel-section-title" style="margin-bottom:12px">Sublet (long-term)</div>
        <label style="display:flex;align-items:center;gap:8px;margin-bottom:10px;font-size:14px;cursor:pointer">
          <input type="checkbox" name="sublet_allowed" value="1" ${p.subletAllowed?'checked':''}>
          Sublet allowed (up to bedroom count)
        </label>
        <input type="hidden" name="sublet_bg_check_required" value="1">
        <label style="display:flex;align-items:center;gap:8px;font-size:14px;cursor:pointer">
          <input type="checkbox" name="sublet_landlord_approval_required" value="1" ${p.subletApproval?'checked':''}>
          Landlord must approve each sub-letter
        </label>
      </div>
      <p style="margin-top:20px"><button type="button" onclick="deleteProperty('${p.id}')" class="db-table-link" style="color:var(--terra);font-size:13px">Delete property</button></p>
    </form>`;
}

function renderApplications(p) {
  const apps = APPS[p.id] || [];
  const isMulti = p.occupancyMode === 'multi';
  const statusColors = { pending:'gold', reviewing:'navy', approved:'green', rejected:'red' };
  const statusLabels = { pending:'Pending', reviewing:'Reviewing', approved:'Approved', rejected:'Rejected' };

  let html = `<div class="panel-section">`;

  if (isMulti) {
    html += `<div style="background:var(--cream);border-radius:10px;padding:12px 14px;margin-bottom:16px;font-size:13px;color:var(--text-mid);border:1px solid var(--cream-dark)">
      <strong style="color:var(--text-dark)">Two-step flow (multi-unit):</strong>
      <strong> Step 1</strong> — one application per unit/door here.
      <strong> Step 2</strong> — open the <strong>Background</strong> tab and request screening for that same applicant (tied to the unit you entered).
    </div>`;
  }

  if (isMulti && panelFocusUnitSeq != null) {
    const row = (p.leases || []).find(u => parseInt(String(u.unitSeq), 10) === panelFocusUnitSeq);
    const door = row && row.unitLabel ? String(row.unitLabel) : '';
    html += `<div style="background:var(--gold-pale);border:1px solid rgba(201,150,58,0.35);border-radius:10px;padding:12px 14px;margin-bottom:16px;font-size:13px;color:var(--text-dark)">
      <strong>Licensed slot #${panelFocusUnitSeq}</strong>${door ? ' · <span style="color:var(--text-mid)">Door / label:</span> ' + escHtml(door) : ''}.
      Use the tabs above (Applications, Background, Units &amp; leases, …) for this unit. Vacant slots: add the door label in the applicant’s <strong>Unit / door</strong> field so it lines up with this slot.
    </div>`;
  }

  html += `<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px">
      <div class="panel-section-title" style="margin:0">${isMulti ? 'Step 1 · Tenant applications (per unit)' : 'Tenant applications'}</div>
      <button onclick="showAddAppForm('${p.id}')" class="db-btn db-btn-primary" style="font-size:12px;padding:6px 12px">+ Add applicant</button>
    </div>`;

  if (apps.length === 0) {
    html += `<div style="text-align:center;padding:40px 0;color:var(--text-light)">
      <div style="font-size:32px;margin-bottom:10px">📋</div>
      <div style="font-size:14px">${isMulti ? 'No applications yet. Each unit gets its own application before background checks.' : 'No applications yet. Add one manually or share a link.'}</div>
    </div>`;
  } else {
    apps.forEach(app => {
      html += `<div style="border:1px solid var(--cream-dark);border-radius:10px;padding:16px;margin-bottom:12px">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
          <div>
            <div style="font-size:15px;font-weight:600;color:var(--text-dark)">${escHtml(app.name)}</div>
            <div style="font-size:12px;color:var(--text-light)">${escHtml(app.email)}${app.phone?' · '+escHtml(app.phone):''}</div>
          </div>
          <span class="badge badge-${statusColors[app.status]}">${statusLabels[app.status]}</span>
        </div>
        <div style="display:grid;grid-template-columns:${isMulti ? '1fr 1fr 1fr' : '1fr 1fr'};gap:6px;font-size:13px;margin-bottom:12px">
          ${isMulti ? `<div><span style="color:var(--text-light)">Unit: </span>${app.targetUnitLabel ? escHtml(app.targetUnitLabel) : '—'}</div>` : ''}
          <div><span style="color:var(--text-light)">Move-in: </span>${app.moveIn}</div>
          <div><span style="color:var(--text-light)">Income: </span>${app.income}</div>
        </div>
        ${app.message ? `<div style="font-size:13px;color:var(--text-mid);background:var(--cream);border-radius:8px;padding:10px;margin-bottom:12px">"${escHtml(app.message)}"</div>` : ''}
        <div style="display:flex;gap:8px;flex-wrap:wrap">
          ${['reviewing','approved','rejected'].map(s =>
            s !== app.status ? `<button onclick="updateAppStatus('${app.statusUrl}','${s}',this)" class="db-btn db-btn-ghost" style="font-size:12px;padding:5px 10px">${({ reviewing: 'Mark reviewing', approved: 'Approve', rejected: 'Reject' })[s]}</button>` : ''
          ).join('')}
          <button onclick="activeAppId='${app.id}';showTab('background')" class="db-btn db-btn-ghost" style="font-size:12px;padding:5px 10px">${isMulti ? 'Step 2 · Background →' : 'Background check →'}</button>
        </div>
      </div>`;
    });
  }

  html += `</div>
  <div id="addAppForm" style="display:none;border-top:1px solid var(--cream-dark);padding-top:20px;margin-top:8px">
    <div class="panel-section-title">${isMulti ? 'New applicant (Step 1)' : 'New applicant'}</div>
    <div class="panel-form" id="appFormInner">
      ${isMulti ? `<div class="panel-form-row" style="grid-template-columns:1fr">
        <div><label style="font-size:12px;font-weight:600;display:block;margin-bottom:5px">Unit / door <span style="color:var(--terra)">*</span></label>
        <input type="text" id="appUnit" class="db-input" placeholder="e.g. 2B, Apt 4, Suite 101">
        <div style="font-size:11px;color:var(--text-light);margin-top:4px">Required for multi-unit: each application is for one unit. Screening in Step 2 stays with this applicant record.</div></div>
      </div>` : ''}
      <div class="panel-form-row">
        <div><label style="font-size:12px;font-weight:600;display:block;margin-bottom:5px">First name</label><input type="text" id="appFirst" class="db-input" placeholder="First name"></div>
        <div><label style="font-size:12px;font-weight:600;display:block;margin-bottom:5px">Last name</label><input type="text" id="appLast" class="db-input" placeholder="Last name"></div>
      </div>
      <div class="panel-form-row">
        <div><label style="font-size:12px;font-weight:600;display:block;margin-bottom:5px">Email</label><input type="email" id="appEmail" class="db-input" placeholder="email@@example.com"></div>
        <div><label style="font-size:12px;font-weight:600;display:block;margin-bottom:5px">Phone</label><input type="text" id="appPhone" class="db-input" placeholder="Optional"></div>
      </div>
      <div class="panel-form-row">
        <div><label style="font-size:12px;font-weight:600;display:block;margin-bottom:5px">Move-in date</label><input type="date" id="appMoveIn" class="db-input"></div>
        <div><label style="font-size:12px;font-weight:600;display:block;margin-bottom:5px">Monthly income (${p.currency})</label><input type="number" id="appIncome" class="db-input" placeholder="e.g. 5000"></div>
      </div>
      <div><label style="font-size:12px;font-weight:600;display:block;margin-bottom:5px">Message / notes</label><textarea id="appMessage" class="db-textarea" placeholder="Any notes about this applicant…"></textarea></div>
      <div style="display:flex;gap:8px">
        <button onclick="document.getElementById('addAppForm').style.display='none'" class="db-btn db-btn-ghost">Cancel</button>
        <button onclick="submitApp('${p.id}')" class="db-btn db-btn-primary">${isMulti ? 'Save applicant · Step 2 next' : 'Save applicant'}</button>
      </div>
    </div>
  </div>`;


  document.getElementById('panelBody').innerHTML = html;
}

function showAddAppForm(pid) {
  const f = document.getElementById('addAppForm');
  f.style.display = f.style.display === 'none' ? 'block' : 'none';
}

async function submitApp(pid) {
  try {
    const p = PROPS[pid];
    const data = new FormData();
    data.append('_token', CSRF);
    data.append('first_name', document.getElementById('appFirst').value);
    data.append('last_name',  document.getElementById('appLast').value);
    data.append('email',      document.getElementById('appEmail').value);
    data.append('phone',      document.getElementById('appPhone').value);
    data.append('move_in_date', document.getElementById('appMoveIn').value);
    if (p && p.occupancyMode === 'multi') {
      const unitEl = document.getElementById('appUnit');
      const u = unitEl ? unitEl.value.trim() : '';
      if (!u) {
        rmNotify('Enter the unit or door label for this application (Step 1).', 'error');
        return;
      }
      data.append('target_unit_label', u);
    }
    const income = document.getElementById('appIncome').value;
    if (income) { data.append('monthly_income_minor_units', parseInt(income, 10) * 100); data.append('income_currency', PROPS[pid].currency); }
    data.append('message', document.getElementById('appMessage').value);
    const res = await fetch(`/properties/${pid}/applications`, {
      method: 'POST',
      body: data,
      credentials: 'same-origin',
      headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' },
    });
    const raw = await res.text();
    let json = null;
    try { json = raw ? JSON.parse(raw) : null; } catch (e) { json = null; }
    if (res.ok) {
      rmNotify('Applicant saved.', 'success');
      if (p && p.occupancyMode === 'multi') {
        try {
          sessionStorage.setItem('rmOpenPropertyPanel', JSON.stringify({
            id: pid,
            tab: 'background',
            applicationId: json && json.application_id ? json.application_id : null,
          }));
        } catch (e) {}
      }
      setTimeout(() => location.reload(), 600);
      return;
    }
    const err = (json && json.errors) ? Object.values(json.errors).flat().join(' ') : ((json && json.message) || 'Could not save applicant.');
    rmNotify(err, 'error');
  } catch (e) {
    rmNotify('Network error while saving applicant.', 'error');
  }
}

async function updateAppStatus(url, status, btn) {
  try {
    if (btn) { btn.disabled = true; }
    const data = new FormData();
    data.append('_token', CSRF);
    data.append('_method', 'PATCH');
    data.append('status', status);
    const res = await fetch(url, {
      method: 'POST',
      body: data,
      credentials: 'same-origin',
      headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' },
    });
    const raw = await res.text();
    let json = null;
    try { json = raw ? JSON.parse(raw) : null; } catch (e) { json = null; }
    if (res.ok) {
      rmNotify('Application status updated.', 'success');
      setTimeout(() => location.reload(), 600);
      return;
    }
    const err = (json && json.errors) ? Object.values(json.errors).flat().join(' ') : ((json && json.message) || 'Could not update status.');
    rmNotify(err, 'error');
  } catch (e) {
    rmNotify('Network error.', 'error');
  } finally {
    if (btn) { btn.disabled = false; }
  }
}

let activeAppId = null;

function highlightLeaseRowForPanelUnit() {
  if (panelFocusUnitSeq == null) return;
  document.querySelectorAll('#panelBody tr[data-unit-seq]').forEach(function(tr) {
    tr.style.background = '';
  });
  const tr = document.querySelector('#panelBody tr[data-unit-seq="' + panelFocusUnitSeq + '"]');
  if (tr) {
    tr.style.background = 'var(--gold-pale)';
    tr.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
  }
}

function renderBackground(p) {
  const apps = APPS[p.id] || [];
  const isMulti = p.occupancyMode === 'multi';
  const allChecks = apps.flatMap(app => app.checks.map(c => ({
    ...c,
    appName: app.name,
    appId: app.id,
    checkUrl: app.checkUrl,
    targetUnitLabel: app.targetUnitLabel || '',
  })));
  const statusColors = { requested:'grey', pending:'gold', passed:'green', failed:'red', manual_review:'navy' };
  const typeLabels = { credit:'Credit', criminal:'Criminal', eviction:'Eviction history', right_to_rent:'Right to Rent', employment:'Employment', references:'References', document_upload:'Document upload' };

  let html = `<div class="panel-section">`;

  if (isMulti) {
    html += `<div style="background:var(--cream);border-radius:10px;padding:12px 14px;margin-bottom:16px;font-size:13px;color:var(--text-mid);border:1px solid var(--cream-dark)">
      <strong style="color:var(--text-dark)">Step 2 · Screening</strong> — checks are tied to each applicant record (and the unit from Step 1). Pick the right applicant when requesting a new check.
    </div>`;
  }

  if (isMulti && panelFocusUnitSeq != null) {
    html += `<div style="background:var(--gold-pale);border:1px solid rgba(201,150,58,0.35);border-radius:10px;padding:12px 14px;margin-bottom:16px;font-size:13px;color:var(--text-dark)">
      Context: <strong>Licensed slot #${panelFocusUnitSeq}</strong>. Match applicants whose <strong>Unit / door</strong> matches this slot.
    </div>`;
  }

  html += `<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px">
      <div class="panel-section-title" style="margin:0">${isMulti ? 'Background checks (per applicant / unit)' : 'Background checks'}</div>
    </div>`;

  if (allChecks.length === 0) {
    html += `<div style="text-align:center;padding:32px 0;color:var(--text-light)">
      <div style="font-size:32px;margin-bottom:10px">🔍</div>
      <div style="font-size:14px;margin-bottom:6px">No background checks yet.</div>
      <div style="font-size:12px">Request a check from an applicant on the Applications tab.</div>
    </div>`;
  } else {
    allChecks.forEach(chk => {
      html += `<div style="border:1px solid var(--cream-dark);border-radius:10px;padding:14px;margin-bottom:10px;display:flex;align-items:center;gap:14px">
        <div style="flex:1">
          <div style="font-size:14px;font-weight:600;color:var(--text-dark)">${typeLabels[chk.type] || chk.type}</div>
          <div style="font-size:12px;color:var(--text-light)">${escHtml(chk.appName)}${chk.targetUnitLabel ? ' · Unit ' + escHtml(chk.targetUnitLabel) : ''} · ${chk.method}</div>
          ${chk.notes ? `<div style="font-size:12px;color:var(--text-mid);margin-top:4px">${escHtml(chk.notes)}</div>` : ''}
        </div>
        <div style="text-align:right;flex-shrink:0">
          <span class="badge badge-${statusColors[chk.status]}">${chk.status.replace('_',' ')}</span>
          <div style="font-size:11px;color:var(--text-light);margin-top:4px">${chk.completed}</div>
        </div>
      </div>`;
    });
  }

  // Request new check — show if there are applicants
  if (apps.length > 0) {
    const country = p.country;
    const westernCountries = ['US','CA','GB','AU','NZ','IE','DE','FR','NL','SE','NO','DK','BE','AT','CH'];
    const isWestern = westernCountries.includes(country);
    const methods = isWestern
      ? [{v:'checkr',l:'Checkr (US)'},{v:'experian',l:'Experian'},{v:'transunion',l:'TransUnion'},{v:'document_upload',l:'Document upload'}]
      : [{v:'document_upload',l:'Document upload'}];
    const types = isWestern
      ? ['credit','criminal','eviction','right_to_rent','employment','references']
      : ['employment','references','document_upload'];

    html += `<div style="border-top:1px solid var(--cream-dark);padding-top:20px;margin-top:8px">
      <div class="panel-section-title">Request new check</div>
      <div class="panel-form" style="gap:12px">
        <div><label style="font-size:12px;font-weight:600;display:block;margin-bottom:5px">Applicant</label>
          <select id="chkApp" class="db-select">${apps.map(a=>`<option value="${a.id}" data-url="${a.checkUrl}">${escHtml(a.name)}${a.targetUnitLabel ? ' — Unit ' + escHtml(a.targetUnitLabel) : ''}</option>`).join('')}</select></div>
        <div class="panel-form-row">
          <div><label style="font-size:12px;font-weight:600;display:block;margin-bottom:5px">Check type</label>
            <select id="chkType" class="db-select">${types.map(t=>`<option value="${t}">${typeLabels[t]||t}</option>`).join('')}</select></div>
          <div><label style="font-size:12px;font-weight:600;display:block;margin-bottom:5px">Method</label>
            <select id="chkMethod" class="db-select">${methods.map(m=>`<option value="${m.v}">${m.l}</option>`).join('')}</select></div>
        </div>
        <div><label style="font-size:12px;font-weight:600;display:block;margin-bottom:5px">Notes</label><input type="text" id="chkNotes" class="db-input" placeholder="Optional"></div>
        <div><button onclick="submitCheck()" class="db-btn db-btn-primary">Request check</button></div>
      </div>
    </div>`;
  }

  html += '</div>';
  document.getElementById('panelBody').innerHTML = html;
  const chkSel = document.getElementById('chkApp');
  if (chkSel && activeAppId && apps.some(a => String(a.id) === String(activeAppId))) {
    chkSel.value = String(activeAppId);
  }
}

async function submitCheck() {
  try {
    const sel = document.getElementById('chkApp');
    const url = sel.options[sel.selectedIndex].dataset.url;
    const data = new FormData();
    data.append('_token', CSRF);
    data.append('type',   document.getElementById('chkType').value);
    data.append('method', document.getElementById('chkMethod').value);
    data.append('notes',  document.getElementById('chkNotes').value);
    const res = await fetch(url, {
      method: 'POST',
      body: data,
      credentials: 'same-origin',
      headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' },
    });
    const raw = await res.text();
    let json = null;
    try { json = raw ? JSON.parse(raw) : null; } catch (e) { json = null; }
    if (res.ok) {
      rmNotify('Background check requested.', 'success');
      setTimeout(() => location.reload(), 600);
      return;
    }
    const err = (json && json.errors) ? Object.values(json.errors).flat().join(' ') : ((json && json.message) || 'Could not request check.');
    rmNotify(err, 'error');
  } catch (e) {
    rmNotify('Network error.', 'error');
  }
}

function renderLease(p) {
  if (isUnitPanel(p)) {
    const l = leaseForUnit(p, panelFocusUnitSeq);
    if (!l) {
      const createUrl = p.createLeaseUrl + (p.createLeaseUrl.includes('?') ? '&' : '?') + 'unit_seq=' + panelFocusUnitSeq;
      document.getElementById('panelBody').innerHTML = `
        <div style="text-align:center;padding:48px 0;color:var(--text-light)">
          <div style="font-size:36px;margin-bottom:12px">📋</div>
          <div style="font-size:15px;margin-bottom:20px">No active lease for this unit.</div>
          <a href="${createUrl}" class="db-btn db-btn-primary">+ Create lease</a>
        </div>`;
      return;
    }
    renderPrimaryLeaseDetail(p, l);
    return;
  }
  if (p.occupancyMode === 'multi') {
    const list = p.leases || [];
    if (!list.length) {
      document.getElementById('panelBody').innerHTML = `
        <div style="text-align:center;padding:48px 0;color:var(--text-light)">
          <div style="font-size:36px;margin-bottom:12px">📋</div>
          <div style="font-size:15px;margin-bottom:20px">No active unit leases yet.</div>
          <a href="${p.createLeaseUrl}" class="db-btn db-btn-primary">+ Add unit lease</a>
        </div>`;
      return;
    }
    const capLabel = p.unitCapacity ? `${p.leaseCount} / ${p.unitCapacity} licensed slots` : `${p.leaseCount} unit(s)`;
    const bodyRows = list.map(u => `
      <tr data-unit-seq="${u.unitSeq}">
        <td><strong>#${escHtml(fmtUnitSeq(u.unitSeq, true))} · ${escHtml(u.unitLabelDisplay || fmtUnitLabel(u.unitLabel))}</strong></td>
        <td>${escHtml(u.tenant)}</td>
        <td>${escHtml(u.rent)} ${escHtml(u.currency)}</td>
        <td>${escHtml(u.dueOrdinal || fmtDueOrdinal(u.due))}</td>
        <td style="text-align:right"><a href="${String(u.showUrl).replace(/"/g,'&quot;')}" class="db-table-link">Lease →</a></td>
      </tr>`).join('');
    document.getElementById('panelBody').innerHTML = `
      <div class="panel-section">
        <div class="panel-section-title">Units &amp; leases</div>
        <p style="font-size:13px;color:var(--text-light);margin-bottom:12px">${capLabel}. Internal # is fixed; the label is what you assign (e.g. door number).</p>
        <div class="db-table-wrap">
          <table class="db-table">
            <thead><tr><th>Unit</th><th>Primary lessee</th><th>Rent</th><th>Due</th><th></th></tr></thead>
            <tbody>${bodyRows}</tbody>
          </table>
        </div>
      </div>`;
    requestAnimationFrame(highlightLeaseRowForPanelUnit);
    return;
  }
  const l = primaryLeaseRecord(p);
  if (!l) {
    document.getElementById('panelBody').innerHTML = `
      <div style="text-align:center;padding:48px 0;color:var(--text-light)">
        <div style="font-size:36px;margin-bottom:12px">📋</div>
        <div style="font-size:15px;margin-bottom:20px">No active primary lease on this property.</div>
        <a href="${p.createLeaseUrl}" class="db-btn db-btn-primary">+ Create lease</a>
      </div>`;
    return;
  }
  renderPrimaryLeaseDetail(p, l);
}

function renderPrimaryLeaseDetail(p, l) {
  const lesseeName = l.lessee || l.tenant || '—';
  const isMulti = p.occupancyMode === 'multi';
  const lateFeeLine = l.lateFee
    ? l.lateFee + ' from ' + (l.lateFeeOrdinal || fmtDueOrdinal(l.lateFeeDay)) + ' of month'
    : 'From ' + (l.lateFeeOrdinal || fmtDueOrdinal(l.lateFeeDay)) + ' of month (not set)';
  const rows = [
    ['Unit #', l.unitSeqDisplay || fmtUnitSeq(l.unitSeq, isMulti)],
    ['Door / label', l.unitLabelDisplay || fmtUnitLabel(l.unitLabel)],
    ['Primary lessee', lesseeName],
    ['Primary lessee email', l.email || '—'],
    ['Rent', l.rent + ' ' + l.currency + '/mo'],
    ['Due day', (l.dueOrdinal || fmtDueOrdinal(l.due)) + ' of month'],
    ['Grace period', (l.graceDays ?? 5) + ' days after due'],
    ['Late fee', lateFeeLine],
    ['Start', l.start],
    ['End', l.end],
    ['Status', l.status.charAt(0).toUpperCase() + l.status.slice(1)],
  ];
  document.getElementById('panelBody').innerHTML = `
    <div class="panel-section">
      <div style="background:var(--cream);border:1px solid var(--cream-dark);border-radius:10px;padding:14px 18px;margin-bottom:18px">
        <p style="margin:0 0 4px;font-size:11px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:var(--text-light)">Primary lessee</p>
        <p style="margin:0;font-size:18px;font-weight:600;color:var(--text-dark)">${escHtml(lesseeName)}</p>
        ${l.email ? '<p style="margin:6px 0 0;font-size:14px;color:var(--text-mid)">' + escHtml(l.email) + '</p>' : ''}
      </div>
      <div class="panel-section-title">Primary lease</div>
      <div class="detail-rows">
      ${rows.map(([lbl, val]) => `<div class="detail-row"><span class="detail-label">${escHtml(lbl)}</span><span class="detail-value">${escHtml(String(val))}</span></div>`).join('')}
      </div>
      <div style="margin-top:16px">
        <a href="${l.showUrl}" class="db-btn db-btn-ghost" style="font-size:12px">View lease →</a>
      </div>
    </div>`;
}

function renderSubleases(p) {
  if (!p.subletAllowed) {
    document.getElementById('panelBody').innerHTML = `
      <div style="text-align:center;padding:48px 0;color:var(--text-light)">
        <div style="font-size:36px;margin-bottom:12px">🚪</div>
        <div style="font-size:15px;margin-bottom:8px">Sublet is not enabled for this property.</div>
        <p style="font-size:13px;max-width:22rem;margin:0 auto">Turn on <strong>Sublet allowed</strong> under Details → Edit property.</p>
      </div>`;
    return;
  }
  const list = p.subLeases || [];
  const cap = p.bedrooms !== '—' ? parseInt(p.bedrooms, 10) : null;
  const activeN = list.filter(s => s.status === 'active').length;
  if (!list.length) {
    document.getElementById('panelBody').innerHTML = `
      <div style="text-align:center;padding:48px 0;color:var(--text-light)">
        <div style="font-size:36px;margin-bottom:12px">📋</div>
        <div style="font-size:15px">No sub-leases on file yet.</div>
        <p style="font-size:13px;margin-top:10px">Primary lessee creates sub-leases from their tenant portal (coming soon).</p>
      </div>`;
    return;
  }
  const rows = list.map(s => {
    const actions = [];
    if (s.approveUrl) {
      actions.push(`<button type="button" class="db-btn db-btn-primary" style="font-size:11px;padding:4px 10px" onclick="subLeaseAction('${s.approveUrl}','approve',this)">Approve</button>`);
    }
    if (s.rejectUrl) {
      actions.push(`<button type="button" class="db-btn db-btn-ghost" style="font-size:11px;padding:4px 10px" onclick="subLeaseAction('${s.rejectUrl}','reject',this)">Reject</button>`);
    }
    return `
    <tr>
      <td>${escHtml(s.label || '—')}</td>
      <td><strong>${escHtml(s.sublessee)}</strong><br><span style="font-size:12px;color:var(--text-light)">${escHtml(s.email)}</span></td>
      <td>${escHtml(s.parentLessee)}${s.unitLabel ? '<br><span style="font-size:12px;color:var(--text-light)">Unit '+escHtml(s.unitLabel)+'</span>' : ''}</td>
      <td>${escHtml(s.rent)}</td>
      <td>${escHtml(s.start)}</td>
      <td><span class="badge badge-${s.status==='active'?'green':'gold'}">${escHtml(s.status.replace(/_/g,' '))}</span></td>
      <td style="white-space:nowrap">${actions.join(' ') || '—'}</td>
    </tr>`;
  }).join('');
  document.getElementById('panelBody').innerHTML = `
    <div class="panel-section">
      <div class="panel-section-title">Sub-leases · sub-lessees</div>
      <p style="font-size:13px;color:var(--text-light);margin-bottom:12px">Background check always required. ${p.subletApproval ? 'Landlord approves each sub-letter.' : 'No landlord approval step.'}</p>
      <div class="db-table-wrap">
        <table class="db-table">
          <thead><tr><th>Label</th><th>Sub-lessee</th><th>Primary lessee</th><th>Sub-rent</th><th>Start</th><th>Status</th><th></th></tr></thead>
          <tbody>${rows}</tbody>
        </table>
      </div>
    </div>`;
}

function renderPayments(p) {
  document.getElementById('panelBody').innerHTML = `
    <div style="text-align:center;padding:48px 0;color:var(--text-light)">
      <div style="font-size:36px;margin-bottom:12px">💳</div>
      <div style="font-size:14px">Payment history visible on the <a href="/payments" style="color:var(--terra)">Payments page</a>.</div>
    </div>`;
}

function renderPhotos(p) {
  const photos = p.photos || [];
  const items = photos.map(ph => `
    <div class="panel-media-tile">
      <img src="${String(ph.url).replace(/"/g,'&quot;')}" alt="">
      <button type="button" class="panel-media-del" onclick="deleteMediaItem('${p.id}',${ph.id})" title="Remove">×</button>
    </div>`).join('');
  document.getElementById('panelBody').innerHTML = `
    <div class="panel-section">
      <div class="panel-section-title">Photos</div>
      <p style="font-size:13px;color:var(--text-light);margin-bottom:16px">JPEG, PNG, WebP or GIF, up to 10 MB each. Public listings show these when the property is set to <strong>public</strong>.</p>
      <input type="file" id="photoFileInput" accept="image/jpeg,image/png,image/gif,image/webp" style="display:none" onchange="uploadPropertyPhoto('${p.id}', this)">
      ${items ? `<div class="panel-media-grid" style="margin-bottom:20px">${items}</div>` : `<div style="text-align:center;padding:24px;color:var(--text-light);margin-bottom:16px;border:1px dashed var(--cream-dark);border-radius:10px">No photos yet.</div>`}
      <button type="button" onclick="document.getElementById('photoFileInput').click()" class="db-btn db-btn-primary">+ Upload photo</button>
      <div id="photoUploadMsg" style="margin-top:12px;font-size:13px;display:none"></div>
    </div>`;
}

function renderVideos(p) {
  const videos = p.videos || [];
  const items = videos.map(v => `
    <div class="panel-video-tile">
      <video src="${String(v.url).replace(/"/g,'&quot;')}" controls playsinline preload="metadata"></video>
      <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 10px;background:var(--cream);font-size:12px;color:var(--text-mid)">
        <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:70%">${escHtml(v.name)}</span>
        <button type="button" class="db-btn db-btn-ghost" style="font-size:11px;padding:4px 8px" onclick="deleteMediaItem('${p.id}',${v.id})">Remove</button>
      </div>
    </div>`).join('');
  document.getElementById('panelBody').innerHTML = `
    <div class="panel-section">
      <div class="panel-section-title">Videos</div>
      <p style="font-size:13px;color:var(--text-light);margin-bottom:16px">MP4, WebM or MOV, up to 100 MB each.</p>
      <input type="file" id="videoFileInput" accept="video/mp4,video/webm,video/quicktime" style="display:none" onchange="uploadPropertyVideo('${p.id}', this)">
      ${items ? `<div class="panel-video-list" style="margin-bottom:20px">${items}</div>` : `<div style="text-align:center;padding:24px;color:var(--text-light);margin-bottom:16px;border:1px dashed var(--cream-dark);border-radius:10px">No videos yet.</div>`}
      <button type="button" onclick="document.getElementById('videoFileInput').click()" class="db-btn db-btn-primary">+ Upload video</button>
      <div id="videoUploadMsg" style="margin-top:12px;font-size:13px;display:none"></div>
    </div>`;
}

async function uploadPropertyPhoto(pid, inputEl) {
  const file = inputEl.files && inputEl.files[0];
  inputEl.value = '';
  if (!file) return;
  rmNotify('Uploading photo…', 'info', 3500);
  const msg = document.getElementById('photoUploadMsg');
  const fd = new FormData();
  fd.append('file', file);
  fd.append('_token', CSRF);
  try {
    const res = await fetch(PROPS[pid].photoUploadUrl, {
      method: 'POST',
      body: fd,
      credentials: 'same-origin',
      headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' },
    });
    const data = await res.json().catch(() => ({}));
    if (!res.ok) {
      const t = data.message || (data.errors && Object.values(data.errors).flat().join(' ')) || 'Upload failed.';
      rmNotify(t, 'error');
      if (msg) { msg.style.display = 'block'; msg.style.color = 'var(--red)'; msg.textContent = t; }
      return;
    }
    if (!data.media) return;
    PROPS[pid].photos = PROPS[pid].photos || [];
    PROPS[pid].photos.push(data.media);
    renderPhotos(PROPS[pid]);
    rmNotify('Photo added.', 'success');
  } catch (e) {
    rmNotify('Network error while uploading.', 'error');
    if (msg) { msg.style.display = 'block'; msg.style.color = 'var(--red)'; msg.textContent = 'Network error.'; }
  }
}

async function uploadPropertyVideo(pid, inputEl) {
  const file = inputEl.files && inputEl.files[0];
  inputEl.value = '';
  if (!file) return;
  rmNotify('Uploading video (may take a moment)…', 'info', 8000);
  const msg = document.getElementById('videoUploadMsg');
  const fd = new FormData();
  fd.append('file', file);
  fd.append('_token', CSRF);
  try {
    const res = await fetch(PROPS[pid].videoUploadUrl, {
      method: 'POST',
      body: fd,
      credentials: 'same-origin',
      headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' },
    });
    const data = await res.json().catch(() => ({}));
    if (!res.ok) {
      const t = data.message || (data.errors && Object.values(data.errors).flat().join(' ')) || 'Upload failed.';
      rmNotify(t, 'error');
      if (msg) { msg.style.display = 'block'; msg.style.color = 'var(--red)'; msg.textContent = t; }
      return;
    }
    if (!data.media) return;
    PROPS[pid].videos = PROPS[pid].videos || [];
    PROPS[pid].videos.push(data.media);
    renderVideos(PROPS[pid]);
    rmNotify('Video added.', 'success');
  } catch (e) {
    rmNotify('Network error while uploading.', 'error');
    if (msg) { msg.style.display = 'block'; msg.style.color = 'var(--red)'; msg.textContent = 'Network error.'; }
  }
}

async function deleteMediaItem(pid, mediaId) {
  if (!confirm('Remove this file?')) return;
  try {
    const res = await fetch(`${PROPS[pid].mediaBaseUrl}/${mediaId}`, {
      method: 'DELETE',
      credentials: 'same-origin',
      headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
    });
    if (!res.ok) {
      rmNotify('Could not remove file.', 'error');
      return;
    }
    PROPS[pid].photos = (PROPS[pid].photos || []).filter(x => x.id !== mediaId);
    PROPS[pid].videos = (PROPS[pid].videos || []).filter(x => x.id !== mediaId);
    if (activeTab === 'photos') renderPhotos(PROPS[pid]);
    else if (activeTab === 'videos') renderVideos(PROPS[pid]);
    rmNotify('File removed.', 'success');
  } catch (e) {
    rmNotify('Network error.', 'error');
  }
}

async function subLeaseAction(url, action, btn) {
  if (action === 'reject' && !confirm('Reject this sub-lease?')) return;
  if (btn) { btn.disabled = true; }
  const data = new FormData();
  data.set('_method', 'PATCH');
  data.set('_token', CSRF);
  if (action === 'reject') {
    const reason = prompt('Rejection reason (optional):', '');
    if (reason) data.set('reason', reason);
  }
  try {
    const res = await fetch(url, {
      method: 'POST',
      body: data,
      credentials: 'same-origin',
      headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' },
    });
    const json = await res.json().catch(() => ({}));
    if (res.ok && json.success) {
      rmNotify(action === 'approve' ? 'Sub-lease approved.' : 'Sub-lease rejected.', 'success');
      setTimeout(() => location.reload(), 600);
      return;
    }
    rmNotify(json.message || 'Action failed.', 'error');
  } catch (e) {
    rmNotify('Network error.', 'error');
  } finally {
    if (btn) btn.disabled = false;
  }
}

let newFormMode = 'single';

function showNewForm(mode) {
  newFormMode = mode || newFormMode || 'single';
  const countryOpts = COUNTRIES.map(c=>`<option value="${c.code}">${c.label}</option>`).join('');
  const isSingle = newFormMode === 'single';
  const modeSpecificField = isSingle
    ? `<div class="db-form-group">
        <label class="new-form-label">Bedrooms</label>
        <input type="number" name="bedrooms" class="db-input" placeholder="e.g. 2" min="0" max="99">
      </div>`
    : `<div class="db-form-group">
        <label class="new-form-label">Licensed units <span style="color:var(--terra)">*</span></label>
        <input type="number" name="unit_capacity" class="db-input" placeholder="e.g. 12" min="1" max="999" required>
        <span style="font-size:11px;color:var(--text-light);margin-top:3px;display:block">Total number of rentable units in this building.</span>
      </div>`;

  document.getElementById('panelBody').innerHTML = `
    <style>
      .new-form-label { font-size:12px;font-weight:600;color:var(--text-dark);margin-bottom:5px;display:block }
      .new-mode-btn { flex:1;padding:9px 0;border:1px solid var(--cream-dark);background:var(--white);border-radius:8px;font-size:13px;font-weight:600;color:var(--text-light);cursor:pointer;transition:all .15s }
      .new-mode-btn.active { background:var(--navy);color:#fff;border-color:var(--navy) }
    </style>
    <div style="display:flex;gap:8px;margin-bottom:24px">
      <button type="button" class="new-mode-btn ${isSingle?'active':''}" onclick="showNewForm('single')">Single-unit</button>
      <button type="button" class="new-mode-btn ${!isSingle?'active':''}" onclick="showNewForm('multi')">Multi-unit building</button>
    </div>
    <form id="newForm" class="panel-form" method="POST" action="{{ route('properties.store') }}">
      <input type="hidden" name="_token" value="{{ csrf_token() }}">
      <input type="hidden" name="occupancy_mode" value="${newFormMode}">
      <input type="hidden" name="rental_mode" value="long_term">
      <input type="hidden" name="listing_visibility" value="private">
      <input type="hidden" name="sublet_allowed" value="0">
      <div class="panel-form-row">
        <div class="db-form-group">
          <label class="new-form-label">${isSingle ? 'Property' : 'Building'} name <span style="color:var(--terra)">*</span></label>
          <input type="text" name="name" class="db-input" placeholder="${isSingle ? 'e.g. Oak Street Duplex' : 'e.g. Harbour View Apartments'}" required>
        </div>
        <div class="db-form-group">
          <label class="new-form-label">Country <span style="color:var(--terra)">*</span></label>
          <select name="country_code" class="db-select"><option value="">Select country…</option>${countryOpts}</select>
        </div>
      </div>
      <div class="db-form-group">
        <label class="new-form-label">Address <span style="color:var(--terra)">*</span></label>
        <input type="text" name="address_line1" class="db-input" placeholder="Street address" required>
      </div>
      <div class="panel-form-row">
        <div class="db-form-group">
          <label class="new-form-label">City <span style="color:var(--terra)">*</span></label>
          <input type="text" name="city" class="db-input" placeholder="City" required>
        </div>
        <div class="db-form-group">
          <label class="new-form-label">Postal code</label>
          <input type="text" name="postal_code" class="db-input" placeholder="Optional">
        </div>
      </div>
      <div class="panel-form-row">
        <div class="db-form-group">
          <label class="new-form-label">Type</label>
          <select name="type" class="db-select">
            ${isSingle ? `
            <option value="house" selected>House / Single Family Residence</option>
            <option value="apartment">Apartment / Condo</option>
            <option value="other">Other</option>
            ` : `
            <option value="apartment" selected>Apartments</option>
            <option value="house">Townhouses</option>
            <option value="commercial">Condos</option>
            <option value="other">Other</option>
            `}
          </select>
        </div>
        ${modeSpecificField}
      </div>
    </form>
    <p style="font-size:12px;color:var(--text-light);margin-top:20px;line-height:1.6">
      ${isSingle
        ? 'Rent, lease, directory and sublet settings can be configured after creation.'
        : 'Rental mode, directory, and per-unit details can be configured after creation from the building page.'}
    </p>`;

  const std = document.getElementById('panelFooterStd');
  const extra = document.getElementById('panelFooterExtra');
  if (std) std.style.display = 'none';
  if (extra) {
    extra.innerHTML = `<div style="display:flex;gap:8px;margin-left:auto">
      <button type="button" onclick="closePanel()" class="db-btn db-btn-ghost">Cancel</button>
      <button type="submit" form="newForm" class="db-btn db-btn-primary">Create ${newFormMode === 'multi' ? 'building' : 'property'}</button>
    </div>`;
  }
}

async function saveProperty(id) {
  const form = document.getElementById('editForm');
  if (!form) {
    rmNotify('Use Edit property on the Details tab, then save.', 'error');
    return;
  }
  const saveBtn = document.getElementById('panelSaveBtn');
  const origLabel = saveBtn ? saveBtn.textContent : 'Save';
  if (saveBtn) {
    saveBtn.disabled = true;
    saveBtn.textContent = 'Saving…';
  }
  const data = new FormData(form);
  const p = PROPS[id];

  // If editing a unit panel, save bedrooms to the unit slot separately
  if (isUnitPanel(p) && p.unitSlotBaseUrl && panelFocusUnitSeq) {
    const bedsVal = data.get('bedrooms');
    const slotData = new FormData();
    slotData.set('_method', 'PATCH');
    slotData.set('_token', CSRF);
    if (bedsVal !== null && bedsVal !== '') slotData.set('bedrooms', bedsVal);
    else slotData.set('bedrooms', '');
    await fetch(p.unitSlotBaseUrl + panelFocusUnitSeq, {
      method: 'POST', body: slotData, credentials: 'same-origin',
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': CSRF },
    });
    data.delete('bedrooms'); // don't overwrite property-level bedrooms
  } else if (data.get('bedrooms') === '') {
    data.delete('bedrooms');
  }

  data.set('_method', 'PUT');
  data.set('_token', CSRF);
  try {
    const res = await fetch(p.editUrl, {
      method: 'POST',
      body: data,
      credentials: 'same-origin',
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': CSRF,
      },
    });
    const raw = await res.text();
    let payload = null;
    try {
      payload = raw ? JSON.parse(raw) : null;
    } catch (e) {
      payload = null;
    }
    if (res.ok && payload && payload.success === true) {
      rmNotify('Property updated.', 'success');
      setTimeout(() => location.reload(), 700);
      return;
    }
    let errText =
      (payload && payload.message) ||
      (payload && payload.errors ? Object.values(payload.errors).flat().join(' ') : '') ||
      '';
    if (!errText && raw && raw.length < 400 && !raw.trim().startsWith('<')) {
      errText = raw.trim();
    }
    if (!errText) {
      errText = 'Save failed (HTTP ' + res.status + '). Try refreshing the page.';
    }
    rmNotify(errText, 'error');
  } catch (e) {
    console.error(e);
    rmNotify('Could not reach the server. Check your connection and try again.', 'error');
  } finally {
    if (saveBtn) {
      saveBtn.disabled = false;
      saveBtn.textContent = origLabel;
    }
  }
}

function deleteProperty(id) {
  if (!confirm('Delete this property? This cannot be undone.')) return;
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = PROPS[id].deleteUrl;
  form.innerHTML = `<input name="_token" value="${CSRF}"><input name="_method" value="DELETE">`;
  document.body.appendChild(form);
  form.submit();
}

function setView(v) {
  document.getElementById('cardView').style.display  = v==='card'  ? 'grid'  : 'none';
  document.getElementById('tableView').style.display = v==='table' ? 'block' : 'none';
  document.getElementById('btnCard').classList.toggle('active',  v==='card');
  document.getElementById('btnTable').classList.toggle('active', v==='table');
  localStorage.setItem('propView', v);
}

function escHtml(str) {
  return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

const saved = localStorage.getItem('propView');
if (document.getElementById('cardView') && document.getElementById('tableView')) {
  if (saved) setView(saved);
}

document.getElementById('panelEditPropertyBtn')?.addEventListener('click', panelEditClick);
document.getElementById('panelSaveBtn')?.addEventListener('click', panelSaveClick);

(function restorePropertyPanel(){
  try {
    const raw = sessionStorage.getItem('rmOpenPropertyPanel');
    if (raw) {
      sessionStorage.removeItem('rmOpenPropertyPanel');
      const o = JSON.parse(raw);
      if (o && o.id && PROPS[o.id]) {
        openPanel(o.id, { tab: o.tab || 'details', applicationId: o.applicationId });
        return;
      }
    }
    const params = new URLSearchParams(window.location.search);
    const openId = params.get('open');
    if (openId && PROPS[openId]) {
      openPanel(openId, {
        tab: params.get('tab') || 'details',
        applicationId: params.get('application') || null,
      });
    }
  } catch (e) {}
})();
</script>
