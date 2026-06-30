@extends('layouts.marketing', ['page' => 'features'])

@section('title', 'Features')
@section('meta_description', 'Everything independent landlords need — rent collection, maintenance, documents, tax export, and more for your portfolio.')

@section('content')

@include('partials.sections.marketing-hero', [
  'label' => 'Platform features',
  'title' => 'Built for the landlord<br><em>nobody else serves.</em>',
  'lead' => '12 landlord capabilities and 9 tenant features — all designed for properties in any country, collected in any currency, managed in one place.',
  'ctas' => [
    ['href' => url('/waitlist'), 'label' => 'Join the waitlist', 'class' => 'rm-btn rm-btn-primary btn-lg'],
    ['href' => url('/pricing'), 'label' => 'See pricing', 'class' => 'btn-outline-light'],
  ],
])

<!-- ══ LANDLORD / TENANT SWITCHER ══ -->
<div class="type-switcher" id="featureSwitcher" role="tablist" aria-label="Feature view">
  <div class="container">
    <div class="type-switcher__track type-switcher__track--2">
      <button type="button" class="type-switcher__tab active" role="tab" aria-selected="true" data-feature-view="landlord">Landlord</button>
      <button type="button" class="type-switcher__tab" role="tab" aria-selected="false" data-feature-view="tenant">Tenant</button>
    </div>
  </div>
</div>

<!-- ══ LANDLORD FEATURES ══ -->
<section class="landlord-section feature-view-section" id="featureLandlord">
  <div class="container">

    <div class="reveal" style="margin-bottom: 48px;">
      <p class="section-label">Landlord features</p>
      <h2 class="section-title">Everything from lease<br>to tax export.</h2>
      <p class="section-sub">The full lifecycle of a tenancy — from lease creation to tax export — in one platform built for independent landlords.</p>
    </div>

    <!-- feat 1: Add property + lease -->
    <div class="feat-row reveal" id="collection">
      <div class="feat-content">
        <div class="feat-eyebrow"><span class="feat-num">01 — 02</span><span class="feat-tag">Setup</span></div>
        <h3>Add a property &amp; create a lease</h3>
        <p class="lead">Select the country, set rent in local currency, upload the signed lease, and send your tenant an invite — all from one flow. The right local payment method is connected automatically.</p>
        <ul class="feat-bullets">
          <li>Supported across single-family, multi-family, and small portfolios</li>
          <li>Address fields adapt to each country (PIN, postcode, ZIP etc.)</li>
          <li>Rent set in local currency — USD equivalent shown alongside</li>
          <li>Tenant invite email sent automatically on lease activation</li>
          <li>Security deposit amount logged — app never holds deposit funds</li>
        </ul>
      </div>
      <div class="feat-visual-pane">
        <div class="lease-card">
          <div class="lease-prop">
            <span class="lease-flag">🇺🇸</span>
            <div>
              <div class="lease-name">Oak Street, Austin TX</div>
              <div class="lease-addr">78701 · Texas · United States</div>
            </div>
          </div>
          <div class="lease-rows">
            <div class="lease-row"><span class="lease-row-label">Monthly rent</span><span class="lease-row-val rent">$ 2,400</span></div>
            <div class="lease-row"><span class="lease-row-label">Payment method</span><span class="lease-row-val">ACH</span></div>
            <div class="lease-row"><span class="lease-row-label">Due date</span><span class="lease-row-val">1st of month</span></div>
            <div class="lease-row"><span class="lease-row-label">Lease term</span><span class="lease-row-val">Jun 2025 – May 2026</span></div>
          </div>
          <div class="lease-status">
            <div class="lease-status-dot"></div>
            <span class="lease-status-text">Active — tenant mandate confirmed</span>
          </div>
        </div>
      </div>
    </div>

    <!-- feat 2: Rent collection -->
    <div class="feat-row flip reveal">
      <div class="feat-content">
        <div class="feat-eyebrow"><span class="feat-num">03</span><span class="feat-tag">Payments</span></div>
        <h3>Automated rent collection</h3>
        <p class="lead">Rent is pulled on the due date via ACH or card. You're notified on success or failure. Every payment is logged permanently — for your tax records.</p>
        <ul class="feat-bullets">
          <li>ACH and card payments supported</li>
          <li>Push notification and email on every payment event</li>
          <li>Automatic 3-day retry on failure · arrears flagged after second miss</li>
          <li>Complete payment history — never recalculated</li>
          <li>Balance visible per property at any time</li>
        </ul>
      </div>
      <div class="feat-visual-pane dark">
        <div class="dash-widget">
          <div class="dash-widget-header">
            <div><div class="dw-title">Monthly collections</div><div class="dw-sub">May 2025 · 3 properties</div></div>
            <div style="text-align:right"><div class="dw-total">$6,900</div><div class="dw-sub">Total collected</div></div>
          </div>
          <div class="dw-bars">
            <div class="dw-bar-row"><span class="dw-bar-flag">🇺🇸</span><div class="dw-bar-track"><div class="dw-bar-fill fr"></div></div><span class="dw-bar-val">$2,400 · Austin</span></div>
            <div class="dw-bar-row"><span class="dw-bar-flag">🇺🇸</span><div class="dw-bar-track"><div class="dw-bar-fill in"></div></div><span class="dw-bar-val">$1,850 · Denver</span></div>
            <div class="dw-bar-row"><span class="dw-bar-flag">🇺🇸</span><div class="dw-bar-track"><div class="dw-bar-fill gb" style="width:0%;background:rgba(255,255,255,0.15)"></div></div><span class="dw-bar-val" style="color:var(--terra-light)">$1,650 · Due Jun 1</span></div>
          </div>
          <div class="dw-export">
            <span class="dw-export-label">📤 Export May report</span>
            <span class="dw-export-btn">CSV + PDF →</span>
          </div>
        </div>
      </div>
    </div>

    <!-- feat 3: Arrears -->
    <div class="feat-row reveal" id="dashboard">
      <div class="feat-content">
        <div class="feat-eyebrow"><span class="feat-num">04</span><span class="feat-tag navy">Arrears</span></div>
        <h3>Arrears management</h3>
        <p class="lead">When rent isn't paid, the app handles escalation automatically. Reminders go to the tenant on day 1, 5, and 10 — tone escalates from polite to formal. You log manual payments or mark disputes from your dashboard.</p>
        <ul class="feat-bullets">
          <li>Reminder emails with escalating tone</li>
          <li>Escalating cadence: polite reminder → firm notice → formal demand</li>
          <li>Log cash or external bank transfer payments manually</li>
          <li>Mark arrears as waived or disputed with a note</li>
          <li>Outstanding arrears shown prominently with days overdue</li>
        </ul>
      </div>
      <div class="feat-visual-pane dark">
        <div class="arrears-widget">
          <div class="arrears-header">
            <span class="arrears-title">Arrears tracker</span>
            <span class="arrears-badge">2 overdue</span>
          </div>
          <div class="arrears-item">
            <div class="arr-left"><span class="arr-flag">🇺🇸</span><div><div class="arr-name">Maple Dr, Phoenix AZ</div><div class="arr-days">12 days overdue</div></div></div>
            <span class="arr-amount">$ 1,650</span>
          </div>
          <div class="arrears-item">
            <div class="arr-left"><span class="arr-flag">🇺🇸</span><div><div class="arr-name">Pine Ave, Denver CO</div><div class="arr-days">4 days overdue</div></div></div>
            <span class="arr-amount">$ 1,850</span>
          </div>
          <div class="reminder-sent">⚡ Formal reminder sent to Phoenix tenant · Day 10 escalation</div>
        </div>
      </div>
    </div>

    <!-- feat 4: Maintenance -->
    <div class="feat-row flip reveal" id="maintenance">
      <div class="feat-content">
        <div class="feat-eyebrow"><span class="feat-num">05</span><span class="feat-tag green">Maintenance</span></div>
        <h3>Maintenance requests</h3>
        <p class="lead">Tenants raise issues with a description and photos from their phone. You receive a notification, respond, and close the request. Full maintenance history stored per property — invaluable for deposit disputes.</p>
        <ul class="feat-bullets">
          <li>Tenant selects category: plumbing, electrical, structural, appliance, other</li>
          <li>Photos attached from camera or gallery</li>
          <li>Status: submitted → acknowledged → in progress → resolved</li>
          <li>Landlord adds resolution notes and closes with optional invoice attachment</li>
          <li>All history retained — timestamped and searchable</li>
        </ul>
      </div>
      <div class="feat-visual-pane gold">
        <div class="maint-widget">
          <div class="maint-header">
            <span class="maint-title">Maintenance requests</span>
            <span class="maint-new">1 new</span>
          </div>
          <div class="maint-item open">
            <div class="maint-item-top"><span class="maint-item-title">Boiler not heating</span><span class="maint-item-cat">Heating</span></div>
            <div class="maint-item-prop">🇺🇸 Oak Street, Austin TX · Raised 2h ago</div>
            <div class="maint-item-status s-open">● New — awaiting acknowledgement</div>
          </div>
          <div class="maint-item">
            <div class="maint-item-top"><span class="maint-item-title">Kitchen tap dripping</span><span class="maint-item-cat">Plumbing</span></div>
            <div class="maint-item-prop">🇺🇸 Pine Ave, Denver CO · 3 days ago</div>
            <div class="maint-item-status s-prog">● In progress</div>
          </div>
          <div class="maint-item">
            <div class="maint-item-top"><span class="maint-item-title">Light fitting replaced</span><span class="maint-item-cat">Electrical</span></div>
            <div class="maint-item-prop">🇺🇸 Maple Dr, Phoenix AZ · 2 weeks ago</div>
            <div class="maint-item-status s-done">● Resolved</div>
          </div>
        </div>
      </div>
    </div>

    <!-- feat 5: Documents -->
    <div class="feat-row reveal" id="documents">
      <div class="feat-content">
        <div class="feat-eyebrow"><span class="feat-num">07</span><span class="feat-tag">Documents</span></div>
        <h3>Document management</h3>
        <p class="lead">Store leases, inspection reports, insurance certificates, and compliance documents per property. Share individual docs with tenants via secure time-limited links. Everything retained for 7 years.</p>
        <ul class="feat-bullets">
          <li>Documents organised by property and document type</li>
          <li>Secure signed download links — 15-minute expiry, no permanent public URLs</li>
          <li>Tenants can upload documents on request (ID, employer letters, references)</li>
          <li>7-year minimum retention — encrypted in transit and at rest</li>
        </ul>
      </div>
      <div class="feat-visual-pane">
        <div class="doc-widget">
          <div class="doc-header">📁 Austin — Oak Street</div>
          <div class="doc-list">
            <div class="doc-item">
              <div class="doc-item-row">
                <span class="doc-icon" aria-hidden="true">📄</span>
                <div class="doc-item-body">
                  <div class="doc-name">Signed lease agreement</div>
                  <div class="doc-meta">PDF · 2.4 MB · Jun 2025</div>
                </div>
                <a href="#" class="doc-link">Share →</a>
              </div>
            </div>
            <div class="doc-item">
              <div class="doc-item-row">
                <span class="doc-icon" aria-hidden="true">🏠</span>
                <div class="doc-item-body">
                  <div class="doc-name">Move-in inspection report</div>
                  <div class="doc-meta">PDF · 8.1 MB · Jun 2025</div>
                </div>
                <a href="#" class="doc-link">Share →</a>
              </div>
            </div>
            <div class="doc-item">
              <div class="doc-item-row">
                <span class="doc-icon" aria-hidden="true">🛡️</span>
                <div class="doc-item-body">
                  <div class="doc-name">Building insurance certificate</div>
                  <div class="doc-meta">PDF · 1.2 MB · Jan 2025</div>
                </div>
                <a href="#" class="doc-link">Share →</a>
              </div>
            </div>
          </div>
          <div class="doc-secure">🔒 Shared links expire after 15 minutes</div>
        </div>
      </div>
    </div>

    <!-- feat 6: Tax export -->
    <div class="feat-row flip reveal" id="tax">
      <div class="feat-content">
        <div class="feat-eyebrow"><span class="feat-num">08 — 09</span><span class="feat-tag gold">Tax &amp; reporting</span></div>
        <h3>Financial dashboard<br>&amp; tax-ready export</h3>
        <p class="lead">Unified view of all properties in one dashboard. Every payment stored permanently. At year end, export a clean income report per property for your CPA. Schedule E just got simpler.</p>
        <ul class="feat-bullets">
          <li>Annual income report per property — CSV and PDF</li>
          <li>Complete payment history — never recalculated</li>
          <li>Income and expense tracking per unit</li>
          <li>Income trend chart across your portfolio</li>
          <li>Structured for US CPA use — not a tax filing service</li>
        </ul>
      </div>
      <div class="feat-visual-pane dark">
        <div class="tax-export-widget">
          <div class="tax-export-header">📤 Tax export — FY 2025</div>
          <div class="tax-export-list">
            <div class="tax-export-item">
              <div class="tax-export-item-body">
                <div class="tax-export-property">🇺🇸 Austin — Oak Street</div>
                <div class="tax-export-meta">12 payments · $28,800 total</div>
              </div>
              <span class="tax-export-format">CSV + PDF</span>
            </div>
            <div class="tax-export-item">
              <div class="tax-export-item-body">
                <div class="tax-export-property">🇺🇸 Denver — Pine Ave</div>
                <div class="tax-export-meta">12 payments · $22,200 total</div>
              </div>
              <span class="tax-export-format">CSV + PDF</span>
            </div>
          </div>
          <div class="tax-export-note">✓ Complete payment history · Schedule E-ready export included</div>
        </div>
      </div>
    </div>

    <!-- feat 7: Messaging -->
    <div class="feat-row reveal">
      <div class="feat-content">
        <div class="feat-eyebrow"><span class="feat-num">10 — 11</span><span class="feat-tag">Communication</span></div>
        <h3>Messaging &amp;<br>lease renewal</h3>
        <p class="lead">All landlord-tenant communication happens on-platform — timestamped, stored, and available for dispute resolution. No more WhatsApp threads. Lease renewal alerts at 90, 60, and 30 days before expiry.</p>
        <ul class="feat-bullets">
          <li>In-app message thread per property with full conversation history</li>
          <li>All messages timestamped and admissible as correspondence record</li>
          <li>Broadcast message to all tenants in a property for building notices</li>
          <li>Lease expiry alerts at 90, 60, and 30 days</li>
          <li>Renew with updated rent or close — tenant notified automatically</li>
        </ul>
      </div>
      <div class="feat-visual-pane dark">
        <div class="msg-widget">
          <div class="msg-thread-title">💬 Oak Street, Austin · James R.</div>
          <div class="msg-bubble from"><div><div class="msg-text">Hi, the water pressure in the bathroom seems low since yesterday. Is there something I can do?</div><div class="msg-time">James · 10:14am</div></div></div>
          <div class="msg-bubble to"><div><div class="msg-text">Thanks for flagging — I'll ask the building manager to check the pump. Should be sorted by end of day.</div><div class="msg-time">You · 10:31am</div></div></div>
          <div class="msg-bubble from"><div><div class="msg-text">Great, thank you!</div><div class="msg-time">James · 10:33am</div></div></div>
          <div class="msg-input-row"><input class="msg-input" placeholder="Type a message..." readonly><button class="msg-send">↑</button></div>
        </div>
      </div>
    </div>

  </div>
</section>

<!-- ══ TENANT FEATURES ══ -->
<section class="tenant-section feature-view-section" id="featureTenant" hidden>
  <div class="container">
    <div class="reveal">
      <p class="section-label">Tenant features</p>
      <h2 class="section-title" style="color:var(--white)">Simple and<br>transparent.</h2>
      <p class="section-sub">Tenants get a straightforward experience — clear invites, automatic payments, and receipts they can access anytime.</p>
    </div>
    <div class="tenant-grid-full reveal" id="payments">
      <div class="tenant-feat-card">
        <div class="tenant-feat-icon">📧</div>
        <h3>Receive invite &amp; set up account</h3>
        <p>Invite email in their language. Account creation takes under 2 minutes. No ID verification required for tenants.</p>
        <div class="feat-detail">
          <div class="feat-detail-item">Email invite from landlord with personalised link</div>
          <div class="feat-detail-item">Localised to tenant's country — language, currency, date format</div>
          <div class="feat-detail-item">Google or Apple sign-in supported</div>
        </div>
      </div>
      <div class="tenant-feat-card">
        <div class="tenant-feat-icon">📋</div>
        <h3>Review lease &amp; confirm</h3>
        <p>Tenant sees lease summary and downloads the full PDF. Can message landlord with questions before confirming.</p>
        <div class="feat-detail">
          <div class="feat-detail-item">Rent amount, due date, and lease dates shown clearly</div>
          <div class="feat-detail-item">Download signed lease PDF at any time</div>
          <div class="feat-detail-item">Must confirm before payment setup unlocks</div>
        </div>
      </div>
      <div class="tenant-feat-card">
        <div class="tenant-feat-icon">💳</div>
        <h3>Set up payment authorization</h3>
        <p>Connect their bank account or card once. Authorization covers recurring monthly collection — no action needed each month.</p>
        <div class="feat-detail">
          <div class="feat-detail-item">ACH and card payments supported</div>
          <div class="feat-detail-item">Pre-collection notification 3 days before each payment</div>
          <div class="feat-detail-item">Mandate cancellation notifies landlord immediately</div>
        </div>
      </div>
      <div class="tenant-feat-card" id="receipts">
        <div class="tenant-feat-icon">🧾</div>
        <h3>Payment history &amp; receipts</h3>
        <p>Full record of every payment with downloadable PDF receipts. Useful for expense claims and rental references.</p>
        <div class="feat-detail">
          <div class="feat-detail-item">Date, amount, status, and payment method per transaction</div>
          <div class="feat-detail-item">PDF receipt downloadable for each payment</div>
          <div class="feat-detail-item">12-month read-only access after lease ends</div>
        </div>
      </div>
      <div class="tenant-feat-card">
        <div class="tenant-feat-icon">🔧</div>
        <h3>Raise maintenance requests</h3>
        <p>Report issues with photos and description. Track status from submitted to resolved — all in one thread.</p>
        <div class="feat-detail">
          <div class="feat-detail-item">Category selection: plumbing, electrical, structural, appliance</div>
          <div class="feat-detail-item">Photo attachments from camera or gallery</div>
          <div class="feat-detail-item">Status updates and notifications at each stage</div>
        </div>
      </div>
      <div class="tenant-feat-card">
        <div class="tenant-feat-icon">💬</div>
        <h3>Message landlord in-app</h3>
        <p>All communication on-platform — no WhatsApp, no personal email needed. Every message timestamped and stored.</p>
        <div class="feat-detail">
          <div class="feat-detail-item">Full message history per property</div>
          <div class="feat-detail-item">Attach photos or documents to messages</div>
          <div class="feat-detail-item">Landlord read receipts — know when message was seen</div>
        </div>
      </div>
      <div class="tenant-feat-card">
        <div class="tenant-feat-icon">📁</div>
        <h3>Access documents anytime</h3>
        <p>Lease, receipts, building notices, and landlord-shared documents — always available in one place.</p>
        <div class="feat-detail">
          <div class="feat-detail-item">Lease PDF downloadable at any time</div>
          <div class="feat-detail-item">Landlord-shared docs: inspection reports, building rules</div>
          <div class="feat-detail-item">Upload requested documents (renewal ID, employer letter)</div>
        </div>
      </div>
      <div class="tenant-feat-card">
        <div class="tenant-feat-icon">⚙️</div>
        <h3>Manage payment method</h3>
        <p>Update bank account or card, pause autopay, or manage authorization — with landlord notified of any changes.</p>
        <div class="feat-detail">
          <div class="feat-detail-item">Update bank account before next due date</div>
          <div class="feat-detail-item">Pause autopay with 7-day notice to landlord</div>
          <div class="feat-detail-item">One-off manual payment to clear arrears</div>
        </div>
      </div>
      <div class="tenant-feat-card">
        <div class="tenant-feat-icon">🏁</div>
        <h3>End of tenancy</h3>
        <p>Confirm renewal or flag intention to vacate. Final payment reconciliation shown. Read-only access to history for 12 months after lease ends.</p>
        <div class="feat-detail">
          <div class="feat-detail-item">Notifications at 60 and 30 days before lease end</div>
          <div class="feat-detail-item">Accept renewal terms or confirm vacating intention</div>
          <div class="feat-detail-item">Download full payment history as rental reference PDF</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ══ V1 / V2 GRID ══ -->
<section class="versions">
  <div class="container">
    <div class="reveal">
      <p class="section-label">Roadmap</p>
      <h2 class="section-title">What's in V1.<br>What's coming next.</h2>
    </div>
    <div class="version-grid reveal">
      <div class="version-card">
        <h3>Launching at launch <span class="version-pill pill-v1">V1</span></h3>
        <p>Everything needed to onboard your first tenant, collect rent, manage maintenance, and export for tax.</p>
        <ul class="version-list">
          <li><span class="vl-dot v1-dot"></span>Add properties across your portfolio</li>
          <li><span class="vl-dot v1-dot"></span>Full lease lifecycle — create, activate, renew, close</li>
          <li><span class="vl-dot v1-dot"></span>Automated rent collection via local rails</li>
          <li><span class="vl-dot v1-dot"></span>Arrears management with escalating reminders</li>
          <li><span class="vl-dot v1-dot"></span>Maintenance requests with photo evidence</li>
          <li><span class="vl-dot v1-dot"></span>Document storage with 7-year retention</li>
          <li><span class="vl-dot v1-dot"></span>Unified multi-currency financial dashboard</li>
          <li><span class="vl-dot v1-dot"></span>Annual tax export (CSV + PDF) per property</li>
          <li><span class="vl-dot v1-dot"></span>In-app landlord-tenant messaging</li>
          <li><span class="vl-dot v1-dot"></span>All 9 tenant features</li>
        </ul>
      </div>
      <div class="version-card">
        <h3>Coming post-launch <span class="version-pill pill-v2">V2</span></h3>
        <p>Features planned within 6 months of launch, informed by early user feedback.</p>
        <ul class="version-list">
          <li><span class="vl-dot v2-dot"></span>Portfolio analytics — occupancy, income trends</li>
          <li><span class="vl-dot v2-dot"></span>Contractor management and invoice tracking</li>
          <li><span class="vl-dot v2-dot"></span>Inspection report templates with photo evidence</li>
          <li><span class="vl-dot v2-dot"></span>Deposit tracking and end-of-tenancy decisions</li>
          <li><span class="vl-dot v2-dot"></span>Agency sub-accounts — manage on behalf of owners</li>
          <li><span class="vl-dot v2-dot"></span>Rent split for flatmates / co-tenants</li>
          <li><span class="vl-dot v2-dot"></span>Utility bill tracking separate from rent</li>
          <li><span class="vl-dot v2-dot"></span>Portable renter profile — rental reference for next landlord</li>
          <li><span class="vl-dot v2-dot"></span>Middle East and Eastern Europe payment rail support</li>
          <li><span class="vl-dot v2-dot"></span>Mobile apps (iOS and Android)</li>
        </ul>
      </div>
    </div>
  </div>
</section>
@endsection
