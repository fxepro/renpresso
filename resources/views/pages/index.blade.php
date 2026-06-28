@extends('layouts.marketing', ['page' => 'home'])

@section('title', config('site.tagline'))
@section('meta_description', 'Property management for US landlords. Collect rent via ACH, track every unit, and export tax-ready reports — with support for additional markets as you grow.')

@section('content')

<section class="hero">
  <div class="hero-grid"></div>
  <div class="hero-glow"></div>
  <div class="hero-badge"><span class="hero-badge-dot"></span>Now accepting early access applications</div>
  <h1>Collect rent <em>reliably.</em><br>Manage every unit.</h1>
  <p class="hero-sub">Built for US landlords first. Tenants pay by ACH or card. You get a clear dashboard, automated reminders, and tax-ready exports — without juggling spreadsheets.</p>
  <div class="hero-ctas">
    <a href="{{ url('/waitlist') }}" class="rm-btn rm-btn-primary btn-lg">Join the waitlist</a>
    <a href="{{ url('/how-it-works') }}" class="btn-outline-light">See how it works</a>
  </div>
  <div class="ticker-wrap">
    <p class="ticker-label">Rent collected this month</p>
    <div class="ticker">
      <div class="ticker-item"><span class="flag">🇺🇸</span><span class="amount">$ 2,400</span><span class="method">USD · ACH</span></div>
      <div class="ticker-item"><span class="flag">🇺🇸</span><span class="amount">$ 1,850</span><span class="method">USD · ACH</span></div>
      <div class="ticker-item"><span class="flag">🇺🇸</span><span class="amount">$ 3,100</span><span class="method">USD · Card</span></div>
      <div class="ticker-item"><span class="flag">🇺🇸</span><span class="amount">$ 1,650</span><span class="method">USD · ACH</span></div>
      <div class="ticker-item"><span class="flag">🇺🇸</span><span class="amount">$ 2,950</span><span class="method">USD · ACH</span></div>
      <div class="ticker-item"><span class="flag">🇺🇸</span><span class="amount">$ 2,200</span><span class="method">USD · Card</span></div>
      <div class="ticker-item"><span class="flag">🇺🇸</span><span class="amount">$ 1,975</span><span class="method">USD · ACH</span></div>
      <div class="ticker-item"><span class="flag">🇺🇸</span><span class="amount">$ 2,750</span><span class="method">USD · ACH</span></div>
      <div class="ticker-item"><span class="flag">🇺🇸</span><span class="amount">$ 2,400</span><span class="method">USD · ACH</span></div>
      <div class="ticker-item"><span class="flag">🇺🇸</span><span class="amount">$ 1,850</span><span class="method">USD · ACH</span></div>
      <div class="ticker-item"><span class="flag">🇺🇸</span><span class="amount">$ 3,100</span><span class="method">USD · Card</span></div>
      <div class="ticker-item"><span class="flag">🇺🇸</span><span class="amount">$ 1,650</span><span class="method">USD · ACH</span></div>
      <div class="ticker-item"><span class="flag">🇺🇸</span><span class="amount">$ 2,950</span><span class="method">USD · ACH</span></div>
      <div class="ticker-item"><span class="flag">🇺🇸</span><span class="amount">$ 2,200</span><span class="method">USD · Card</span></div>
      <div class="ticker-item"><span class="flag">🇺🇸</span><span class="amount">$ 1,975</span><span class="method">USD · ACH</span></div>
      <div class="ticker-item"><span class="flag">🇺🇸</span><span class="amount">$ 2,750</span><span class="method">USD · ACH</span></div>
    </div>
  </div>
</section>

<section class="problem">
  <div class="container">
    <div class="problem-inner reveal">
      <div>
        <p class="section-label">Sound familiar?</p>
        <blockquote class="problem-quote">"You own five units across two states. You're juggling bank alerts, a spreadsheet, and a patient accountant."</blockquote>
      </div>
      <div>
        <p class="lead-muted">US landlords deserve better tools. Until now, the options were too basic or too enterprise.</p>
        <ul class="problem-list">
          <li><span class="prob-icon">😤</span>Chasing rent by text and email every month</li>
          <li><span class="prob-icon">📊</span>Reconciling payments manually in a spreadsheet</li>
          <li><span class="prob-icon">🗂️</span>No single record of what you've collected or are owed</li>
          <li><span class="prob-icon">🚫</span>Software that wasn't built for independent landlords</li>
          <li class="resolved"><span class="prob-icon">✅</span>One app for every property you manage</li>
          <li class="resolved"><span class="prob-icon">✅</span>ACH and card collection — set up once</li>
          <li class="resolved"><span class="prob-icon">✅</span>Unified dashboard across your portfolio</li>
          <li class="resolved"><span class="prob-icon">✅</span>Tax-ready export for your CPA, every year</li>
        </ul>
      </div>
    </div>
  </div>
</section>

@include('partials.sections.step-grid', [
  'sectionClass' => 'how',
  'eyebrow' => 'Simple by design',
  'title' => "Three steps. That's it.",
  'inverseTitle' => true,
  'lead' => 'Complex regulations and payment rails handled invisibly. <a href="' . url('/how-it-works') . '" class="link-terra-light">See the full walkthrough →</a>',
  'steps' => [
    ['number' => '01', 'icon' => '🏠', 'title' => 'Add your property', 'body' => 'Enter the address, rent amount, and tenant details. We connect the right payment method for your market automatically.'],
    ['number' => '02', 'icon' => '💳', 'title' => 'Your tenant pays on autopilot', 'body' => 'Tenants authorize ACH or card once. Rent is collected on the due date every month — no chasing required.'],
    ['number' => '03', 'icon' => '📊', 'title' => 'You see it all in one place', 'body' => 'Every property, every payment, one dashboard. Annual report ready for your accountant.'],
  ],
])

<section class="features">
  <div class="container">
    <div class="reveal section-header">
      <p class="section-label">For landlords</p>
      <h2 class="section-title">Everything you need. Nothing you don't.</h2>
      <p class="section-sub">12 core processes — from lease creation to tax export. <a href="{{ url('/features') }}" class="link-terra">See all features →</a></p>
    </div>
    <div class="features-layout reveal">
      <div class="feature-tabs" id="featureTabs">
        <button class="feature-tab active" data-panel="0"><span class="feature-tab-icon">🏠</span><span class="feature-tab-label">Add a property</span></button>
        <button class="feature-tab" data-panel="1"><span class="feature-tab-icon">📋</span><span class="feature-tab-label">Create a lease</span></button>
        <button class="feature-tab" data-panel="2"><span class="feature-tab-icon">💳</span><span class="feature-tab-label">Rent collection</span></button>
        <button class="feature-tab" data-panel="3"><span class="feature-tab-icon">⚠️</span><span class="feature-tab-label">Arrears</span></button>
        <button class="feature-tab" data-panel="4"><span class="feature-tab-icon">🔧</span><span class="feature-tab-label">Maintenance</span></button>
        <button class="feature-tab" data-panel="5"><span class="feature-tab-icon">📊</span><span class="feature-tab-label">Dashboard</span></button>
        <button class="feature-tab" data-panel="6"><span class="feature-tab-icon">📁</span><span class="feature-tab-label">Documents</span></button>
        <button class="feature-tab" data-panel="7"><span class="feature-tab-icon">📤</span><span class="feature-tab-label">Tax export</span></button>
      </div>
      <div id="featurePanels">
        <div class="feature-panel active"><div class="feature-card"><div class="feature-card-num">01</div><h3>Add a property</h3><p class="lead">Enter your property details and we handle the rest. The right payment method is connected automatically based on where the unit is located.</p><ul class="feature-bullets"><li>Single-family, multi-family, and small portfolios</li><li>Rent and deposit amounts tracked per unit</li><li>Payment method connected automatically</li><li>First property free for one month</li></ul></div></div>
        <div class="feature-panel"><div class="feature-card"><div class="feature-card-num">02</div><h3>Create a lease</h3><p class="lead">Set rent, due date, grace period, and lease duration. Upload your signed lease PDF. Tenant receives an invite with their payment setup link.</p><ul class="feature-bullets"><li>Rent amount and schedule in one place</li><li>Lease terms stored with the property record</li><li>Security deposit amount logged — app does not hold funds</li><li>Tenant invite sent automatically on activation</li></ul></div></div>
        <div class="feature-panel"><div class="feature-card"><div class="feature-card-num">03</div><h3>Automated rent collection</h3><p class="lead">Rent is pulled on the due date via ACH or card. Notified on success or failure. Every payment logged with a permanent record.</p><ul class="feature-bullets"><li>ACH and card payments supported</li><li>Push notification and email on every payment event</li><li>Automatic retry on failure, arrears flag after second miss</li><li>Balance visible per property and per unit</li></ul></div></div>
        <div class="feature-panel"><div class="feature-card"><div class="feature-card-num">04</div><h3>Arrears management</h3><p class="lead">When rent isn't paid, the app handles escalation automatically. Reminders on day 1, 5, and 10 — tone escalates. Log manual payments or disputes from your dashboard.</p><ul class="feature-bullets"><li>Localised reminder emails in tenant's language</li><li>Escalating cadence: polite → firm → formal</li><li>Log cash or external bank transfer payments manually</li><li>Mark arrears as waived or disputed with notes</li></ul></div></div>
        <div class="feature-panel"><div class="feature-card"><div class="feature-card-num">05</div><h3>Maintenance requests</h3><p class="lead">Tenants raise issues with description and photos. Receive notification, respond, and close. Full history per property — invaluable for deposit disputes.</p><ul class="feature-bullets"><li>Tenant categorises: plumbing, electrical, structural, appliance</li><li>Photo attachments from camera or gallery</li><li>Status tracking: submitted → acknowledged → resolved</li><li>Contractor invoice attachment on closure</li></ul></div></div>
        <div class="feature-panel"><div class="feature-card"><div class="feature-card-num">06</div><h3>Financial dashboard</h3><p class="lead">All properties, all units, one view. See collected rent, outstanding balances, and trends across your portfolio at a glance.</p><ul class="feature-bullets"><li>Unified dashboard for your entire portfolio</li><li>Filter by property, status, or date range</li><li>Income and expense tracking per unit</li><li>Income trend chart across all properties</li></ul></div></div>
        <div class="feature-panel"><div class="feature-card"><div class="feature-card-num">07</div><h3>Document management</h3><p class="lead">Store leases, inspection reports, insurance certificates, and compliance documents. Share with tenants via secure time-limited links. 7-year retention.</p><ul class="feature-bullets"><li>Documents organised by property and type</li><li>Secure signed download links — 15 minute expiry</li><li>Tenant-uploaded documents (renewal ID, employer letters)</li><li>7-year minimum retention per data law requirements</li></ul></div></div>
        <div class="feature-panel"><div class="feature-card"><div class="feature-card-num">08</div><h3>Tax-ready export</h3><p class="lead">Annual income report per property. Every payment logged permanently. Hand it to your CPA at year end.</p><ul class="feature-bullets"><li>Annual report per property as CSV and PDF</li><li>Complete payment history — never recalculated</li><li>Structured for Schedule E and CPA review</li><li>Not a tax filing service — your CPA stays in control</li></ul></div></div>
      </div>
    </div>
  </div>
</section>

<section class="tenant">
  <div class="container">
    <div class="reveal section-header">
      <p class="section-label">For tenants</p>
      <h2 class="section-title">Simple for your tenants.</h2>
      <p class="section-sub section-sub--lg">Tenants get a straightforward experience — clear invites, automatic payments, and receipts they can access anytime.</p>
    </div>
    <div class="tenant-grid reveal">
      <div>
        <div class="tenant-steps">
          <div class="tenant-step"><div class="tenant-step-num">1</div><div class="tenant-step-content"><h4>Receive invite &amp; set up payment</h4><p>Email invite with a secure link. Connect bank account or card once.</p></div></div>
          <div class="tenant-step"><div class="tenant-step-num">2</div><div class="tenant-step-content"><h4>Rent collected automatically</h4><p>Notified on every payment. Full history with PDF receipts.</p></div></div>
          <div class="tenant-step"><div class="tenant-step-num">3</div><div class="tenant-step-content"><h4>Raise maintenance &amp; message landlord</h4><p>Photos, status tracking — all in one thread, on-platform.</p></div></div>
          <div class="tenant-step"><div class="tenant-step-num">4</div><div class="tenant-step-content"><h4>Access documents anytime</h4><p>Lease, receipts, rental references — always available.</p></div></div>
        </div>
        <a href="{{ url('/features') }}#tenant" class="link-arrow">Full tenant experience →</a>
      </div>
      <div>
        <div class="mock-app">
          <div class="mock-header"><div class="mock-dots"><div class="dot dot-r"></div><div class="dot dot-y"></div><div class="dot dot-g"></div></div><span class="mock-title">My payments — March 2025</span></div>
          <div class="pay-row"><div class="pay-left"><span class="pay-flag">🇺🇸</span><div class="pay-info"><h5>Oak Street, Austin TX</h5><p>1 Mar 2025 · ACH</p></div></div><div class="pay-right"><div class="pay-amount">$ 2,400</div><span class="pay-status s-paid">Paid</span></div></div>
          <div class="pay-row"><div class="pay-left"><span class="pay-flag">🇺🇸</span><div class="pay-info"><h5>Pine Ave, Denver CO</h5><p>1 Mar 2025 · ACH</p></div></div><div class="pay-right"><div class="pay-amount">$ 1,850</div><span class="pay-status s-paid">Paid</span></div></div>
          <div class="pay-row"><div class="pay-left"><span class="pay-flag">🇺🇸</span><div class="pay-info"><h5>Maple Dr, Phoenix AZ</h5><p>1 Apr 2025 · Card</p></div></div><div class="pay-right"><div class="pay-amount">$ 1,650</div><span class="pay-status s-due">Due in 3 days</span></div></div>
          <div class="mock-footer"><span>Next auto-collection</span><span class="pay-status s-auto">1 Apr 2025</span></div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="countries">
  <div class="container">
    <div class="reveal section-header">
      <p class="section-label">Supported markets</p>
      <h2 class="section-title">Built for US landlords. Ready to scale.</h2>
      <p class="section-sub">Start with your US portfolio. Add more units and markets as you grow. <a href="{{ url('/countries') }}" class="link-terra">See supported markets →</a></p>
    </div>
    <div class="country-grid reveal">
      <div class="country-card"><p class="country-region">Primary market</p><div class="country-flags">🇺🇸</div><h4>United States</h4><p>All 50 states — single-family to small portfolios</p><span class="country-method">ACH · Card</span></div>
      <div class="country-card"><p class="country-region">North America</p><div class="country-flags">🇨🇦</div><h4>Canada</h4><p>Provinces nationwide</p><span class="country-method">EFT · Bank</span></div>
      <div class="country-card"><p class="country-region">Western Europe</p><div class="country-flags">🇬🇧 🇫🇷 🇩🇪</div><h4>UK &amp; EU</h4><p>Major markets for landlords with overseas units</p><span class="country-method">Bank transfer</span></div>
      <div class="country-card"><p class="country-region">Pacific</p><div class="country-flags">🇦🇺 🇳🇿</div><h4>Australia &amp; NZ</h4><p>Residential rentals</p><span class="country-method">Bank · Direct debit</span></div>
      <div class="country-card"><p class="country-region">Latin America</p><div class="country-flags">🇲🇽 🇧🇷</div><h4>Mexico &amp; Brazil</h4><p>Growing landlord demand</p><span class="country-method">Local bank</span></div>
      <div class="country-card country-card--soon"><p class="country-region">Coming soon</p><div class="country-flags">🌍</div><h4>More markets</h4><p>Additional regions on the roadmap</p><span class="country-method"><a href="{{ url('/countries') }}" class="link-terra">View your market →</a></span></div>
    </div>
  </div>
</section>

<section class="pricing">
  <div class="container">
    <div class="reveal section-header">
      <p class="section-label">Transparent pricing</p>
      <h2 class="section-title section-title--inverse">Start free. Scale simply.</h2>
      <p class="section-sub">First property free for one month. Pay per unit from the second. <a href="{{ url('/pricing') }}" class="link-terra-light">See full pricing →</a></p>
    </div>
    @include('partials.sections.comparison-table')
    <p class="footnote-muted">Processor fees passed through at cost and shown before every collection. No markup.</p>
  </div>
</section>

<section class="trust">
  <div class="container">
    <div class="reveal section-header section-header--center u-text-center">
      <p class="section-label u-text-center">Why landlords trust us</p>
      <h2 class="section-title u-text-center">Your money never moves without you.</h2>
    </div>
    <div class="trust-grid reveal">
      <div class="trust-row">
        <div class="trust-card"><div class="trust-icon">🏦</div><h4>Direct collection only</h4><p>We collect rent through licensed payment providers. Funds go to your connected account. We are not a money transmitter.</p></div>
        <div class="trust-card"><div class="trust-icon">📋</div><h4>Every payment logged</h4><p>Every transaction recorded permanently — never recalculated. Your records are airtight for Schedule E and CPA review.</p></div>
        <div class="trust-card"><div class="trust-icon">🔒</div><h4>7-year document retention</h4><p>All leases, receipts, and communications stored for 7 years minimum. Encrypted in transit and at rest.</p></div>
      </div>
      <div class="trust-row">
        <div class="trust-card"><div class="trust-icon">💬</div><h4>On-platform communication</h4><p>All messages timestamped and stored. No more scrambling through text threads for evidence in a deposit dispute.</p></div>
        <div class="trust-card"><div class="trust-icon">🌐</div><h4>Licensed payment providers</h4><p>Every transaction processed through regulated, licensed payment partners — compliant with US banking and card network rules.</p></div>
        <div class="trust-card"><div class="trust-icon">📤</div><h4>Tax-ready exports</h4><p>Annual income report per property in CSV and PDF. Structured for your CPA. We make your accountant's job significantly easier.</p></div>
      </div>
    </div>
  </div>
</section>

@include('partials.sections.faq', [
  'eyebrow' => 'Got questions?',
  'title' => 'Frequently asked',
  'items' => [
    ['question' => 'Does ' . config('app.name') . ' move money for me?', 'answer' => 'No. ' . config('app.name') . ' collects rent through licensed payment providers into your connected account. We track every payment — we do not hold or move funds on your behalf.'],
    ['question' => 'How does rent collection work?', 'answer' => 'Tenants authorize ACH or card once during onboarding. Rent is collected automatically on the due date. You receive a notification on success or failure, and every payment is logged in your dashboard.'],
    ['question' => 'What happens if a tenant misses a payment?', 'answer' => "The app retries automatically after 3 days. If the second attempt also fails, you receive an arrears notification and automated reminders go to the tenant on days 1, 5, and 10 — with escalating tone. You can log a manual payment or mark it as disputed."],
    ['question' => 'Do I need to do anything at tax time?', 'answer' => config('app.name') . ' generates an annual income report per property that you hand to your CPA. We are not a tax advisor. Your filing obligations — including Schedule E — remain yours.'],
    ['question' => 'Is there a limit on how many properties I can add?', 'answer' => 'No limit. Your first property is free for the first month. From the second property onwards — or after the first month — you pay $9 per unit per month.'],
  ],
])

@endsection
