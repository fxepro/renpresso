@extends('layouts.marketing', ['page' => 'how-it-works'])

@section('title', 'How it works')
@section('meta_description', 'Three steps to collect rent reliably. Add a property, your tenant pays by ACH, you see everything in one dashboard.')

@section('content')

@include('partials.sections.marketing-hero', [
  'label' => 'How it works',
  'title' => 'Three steps.<br><em>That\'s it.</em>',
  'lead' => 'Rent collection, reminders, and reporting — handled automatically. You add a property and collect rent.',
  'ctas' => [
    ['href' => url('/waitlist'), 'label' => 'Join the waitlist', 'class' => 'rm-btn rm-btn-primary btn-lg'],
    ['href' => url('/features'), 'label' => 'See all features', 'class' => 'btn-outline-light'],
  ],
])

<!-- ══ OVERVIEW STRIP ══ -->
<div class="overview">
  <div class="container">
    <div class="overview-grid">
      <a href="#step-1" class="overview-card active">
        <div class="overview-num">01</div>
        <div class="overview-text">
          <h3>Add your property</h3>
          <p>Enter address and rent. Payment setup handled automatically.</p>
        </div>
      </a>
      <a href="#step-2" class="overview-card">
        <div class="overview-num">02</div>
        <div class="overview-text">
          <h3>Your tenant pays on autopilot</h3>
          <p>They authorize ACH or card once.</p>
        </div>
      </a>
      <a href="#step-3" class="overview-card">
        <div class="overview-num">03</div>
        <div class="overview-text">
          <h3>You see it all in one place</h3>
          <p>Unified dashboard. Every unit. One view.</p>
        </div>
      </a>
    </div>
  </div>
</div>

<!-- ══ DEEP DIVE ══ -->
<section class="deep-dive" id="step-1">
  <div class="container">

    <!-- STEP 1 -->
    <div class="step-block reveal">
      <div class="step-content">
        <div class="step-eyebrow">
          <div class="step-badge">1</div>
          <span class="step-tag">Getting started</span>
        </div>
        <h2>Add your property.<br>We handle the rest.</h2>
        <p class="lead">Enter your property address and rent amount. {{ config('app.name') }} connects the right payment method automatically — ACH or card for US properties.</p>
        <div class="substeps">
          <div class="substep">
            <span class="substep-icon">🏠</span>
            <div class="substep-content">
              <h4>Property details in minutes</h4>
              <p>Add address, unit type, rent amount, and due date. Works for single-family homes, condos, and small multi-unit buildings.</p>
            </div>
          </div>
          <div class="substep">
            <span class="substep-icon">📍</span>
            <div class="substep-content">
              <h4>Standard US address fields</h4>
              <p>Street, city, state, and ZIP — no generic forms. Lease terms stored alongside the property record.</p>
            </div>
          </div>
          <div class="substep">
            <span class="substep-icon">💳</span>
            <div class="substep-content">
              <h4>Payment method connected automatically</h4>
              <p>ACH direct debit or card payments — set up based on your property location. No manual processor configuration.</p>
            </div>
          </div>
          <div class="substep">
            <span class="substep-icon">🆓</span>
            <div class="substep-content">
              <h4>First property free for one month</h4>
              <p>No payment required to add your first property. Pay per unit from the second onwards.</p>
            </div>
          </div>
        </div>
      </div>
      <div class="visual-panel">
        <div class="v-country">
          <div class="v-header">
            <div class="v-dots"><div class="vd vd-r"></div><div class="vd vd-y"></div><div class="vd vd-g"></div></div>
            <span class="v-title">Add a property</span>
          </div>
          <div class="country-option selected">
            <div class="country-option-left">
              <span class="c-flag">🇺🇸</span>
              <div>
                <div class="c-name">United States</div>
                <div class="c-processor">ACH Direct Debit</div>
              </div>
            </div>
            <div class="c-check">✓</div>
          </div>
          <div class="country-option">
            <div class="country-option-left">
              <span class="c-flag">🇺🇸</span>
              <div>
                <div class="c-name">Card payment</div>
                <div class="c-processor">Visa · Mastercard · Amex</div>
              </div>
            </div>
            <div class="c-radio"></div>
          </div>
          <div class="country-option">
            <div class="country-option-left">
              <span class="c-flag">🇨🇦</span>
              <div>
                <div class="c-name">Canada</div>
                <div class="c-processor">EFT Direct Debit</div>
              </div>
            </div>
            <div class="c-radio"></div>
          </div>
          <div class="v-assigned">
            <span class="v-assigned-icon">⚡</span>
            <div class="v-assigned-text"><strong>Payment method assigned automatically</strong> — ACH and card supported. Rent will be collected in USD.</div>
          </div>
        </div>
      </div>
    </div>

    <!-- STEP 2 -->
    <div class="step-block flip reveal" id="step-2">
      <div class="step-content">
        <div class="step-eyebrow">
          <div class="step-badge terra">2</div>
          <span class="step-tag">Tenant onboarding</span>
        </div>
        <h2>Your tenant pays<br>without the hassle.</h2>
        <p class="lead">Once you create the lease and set the rent amount, your tenant receives an email invite. They connect their bank account or card and authorize recurring payments. No chasing, no manual transfers.</p>
        <div class="substeps">
          <div class="substep">
            <span class="substep-icon">📧</span>
            <div class="substep-content">
              <h4>Clear invite email</h4>
              <p>Tenant receives a secure link to review the lease and set up payment. The setup page walks them through each step.</p>
            </div>
          </div>
          <div class="substep">
            <span class="substep-icon">🔗</span>
            <div class="substep-content">
              <h4>One-time authorization</h4>
              <p>Tenant connects their bank account or card once. ACH authorization covers recurring collections — no action needed each month.</p>
            </div>
          </div>
          <div class="substep">
            <span class="substep-icon">📅</span>
            <div class="substep-content">
              <h4>Automatic collection on due date</h4>
              <p>Rent is pulled on the day you set — 1st of the month, 15th, or any day. No manual reminders, no chasing. Tenant gets a notification on each collection.</p>
            </div>
          </div>
          <div class="substep">
            <span class="substep-icon">🔔</span>
            <div class="substep-content">
              <h4>You're notified immediately</h4>
              <p>Push notification and email on every payment event — success, failure, or retry. Nothing happens silently.</p>
            </div>
          </div>
        </div>
      </div>
      <div class="visual-panel">
        <div class="v-mandate">
          <p style="font-size:19px;font-weight:600;color:var(--text-dark);margin-bottom:20px;">Tenant onboarding — Oak Street, Austin TX</p>
          <div class="mandate-flow">
            <div class="mandate-step-card done">
              <div class="ms-num">1</div>
              <div class="ms-text"><h5>Account created</h5><p>James R. registered via email invite</p></div>
              <span class="ms-badge ms-done">Done</span>
            </div>
            <div class="connector"></div>
            <div class="mandate-step-card done">
              <div class="ms-num">2</div>
              <div class="ms-text"><h5>Lease reviewed</h5><p>$ 2,400 / month · due 1st</p></div>
              <span class="ms-badge ms-done">Done</span>
            </div>
            <div class="connector"></div>
            <div class="mandate-step-card active-card">
              <div class="ms-num">3</div>
              <div class="ms-text"><h5>ACH authorization</h5><p>Connecting bank account for recurring payment</p></div>
              <span class="ms-badge ms-now">In progress</span>
            </div>
            <div class="connector"></div>
            <div class="mandate-step-card">
              <div class="ms-num">4</div>
              <div class="ms-text"><h5>First collection</h5><p>Scheduled for 1 Jun 2025</p></div>
              <span class="ms-badge ms-next">Upcoming</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- STEP 3 -->
    <div class="step-block reveal" id="step-3">
      <div class="step-content">
        <div class="step-eyebrow">
          <div class="step-badge green">3</div>
          <span class="step-tag">Your dashboard</span>
        </div>
        <h2>Every property.<br>One dashboard.</h2>
        <p class="lead">All your properties appear in a single unified dashboard. Every payment is logged permanently, keeping your records clean for tax time and CPA review.</p>
        <div class="substeps">
          <div class="substep">
            <span class="substep-icon">📋</span>
            <div class="substep-content">
              <h4>Complete payment history</h4>
              <p>Every transaction stored permanently — never recalculated. Your CPA has an airtight record for Schedule E filings.</p>
            </div>
          </div>
          <div class="substep">
            <span class="substep-icon">🏦</span>
            <div class="substep-content">
              <h4>Balance tracking per property</h4>
              <p>See collected rent, outstanding balances, and deposits per unit. Funds settle to your connected account.</p>
            </div>
          </div>
          <div class="substep">
            <span class="substep-icon">📤</span>
            <div class="substep-content">
              <h4>Annual tax export</h4>
              <p>One click generates a CSV and PDF income report per property — ready for your accountant at year end.</p>
            </div>
          </div>
          <div class="substep">
            <span class="substep-icon">📈</span>
            <div class="substep-content">
              <h4>Portfolio view across all properties</h4>
              <p>Occupancy rate, income trends, arrears summary — across your entire portfolio in one place.</p>
            </div>
          </div>
        </div>
      </div>
      <div class="visual-panel">
        <div class="v-dashboard">
          <div class="v-header">
            <div class="v-dots"><div class="vd vd-r"></div><div class="vd vd-y"></div><div class="vd vd-g"></div></div>
          </div>
          <div class="dash-header">
            <span class="dash-title">Portfolio overview</span>
            <span class="dash-period">May 2025</span>
          </div>
          <div class="dash-stats">
            <div class="dash-stat highlight">
              <div class="dash-stat-label">Total collected (USD)</div>
              <div class="dash-stat-value">$2,520</div>
              <div class="dash-stat-sub">This month · 3 properties</div>
            </div>
            <div class="dash-stat">
              <div class="dash-stat-label">Occupancy rate</div>
              <div class="dash-stat-value">100%</div>
              <div class="dash-stat-sub">All units tenanted</div>
            </div>
          </div>
          <div class="dash-props">
            <div class="dash-prop">
              <div class="dp-left">
                <span class="dp-flag">🇺🇸</span>
                <div>
                  <div class="dp-name">Oak Street, Austin TX</div>
                  <div class="dp-method">ACH Direct Debit</div>
                </div>
              </div>
              <div class="dp-right">
                <div class="dp-local">$ 2,400</div>
                <div class="dp-usd">Paid</div>
                <div class="dp-fx">1 May</div>
              </div>
            </div>
            <div class="dash-prop">
              <div class="dp-left">
                <span class="dp-flag">🇺🇸</span>
                <div>
                  <div class="dp-name">Pine Ave, Denver CO</div>
                  <div class="dp-method">ACH Direct Debit</div>
                </div>
              </div>
              <div class="dp-right">
                <div class="dp-local">$ 1,850</div>
                <div class="dp-usd">Paid</div>
                <div class="dp-fx">1 May</div>
              </div>
            </div>
            <div class="dash-prop">
              <div class="dp-left">
                <span class="dp-flag">🇺🇸</span>
                <div>
                  <div class="dp-name">Maple Dr, Phoenix AZ</div>
                  <div class="dp-method">Card</div>
                </div>
              </div>
              <div class="dp-right">
                <div class="dp-local">$ 1,650</div>
                <div class="dp-usd" style="color:var(--terra-light)">Due 3 Jun</div>
                <div class="dp-fx">Next collection</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
</section>



<!-- ══ REPATRIATION NOTE ══ -->
<section class="repatriation">
  <div class="container">
    <div class="repa-grid reveal">
      <div class="repa-content">
        <p class="section-label">One thing to know</p>
        <h2 class="section-title">Your money.<br>Your account.<br>Always.</h2>
        <p class="section-sub" style="font-size:19px;">{{ config('app.name') }} collects rent through licensed payment providers. Here's what that means in practice.</p>
        <div class="repa-items">
          <div class="repa-item">
            <span class="repa-icon">🏦</span>
            <div class="repa-item-text">
              <h4>Direct to your account</h4>
              <p>Rent settles to your connected bank account. The app tracks every payment and balance per property.</p>
            </div>
          </div>
          <div class="repa-item">
            <span class="repa-icon">🔒</span>
            <div class="repa-item-text">
              <h4>We never hold your funds</h4>
              <p>We are not a money transmitter. Payments flow through regulated providers directly to you.</p>
            </div>
          </div>
          <div class="repa-item">
            <span class="repa-icon">📋</span>
            <div class="repa-item-text">
              <h4>Complete records for tax time</h4>
              <p>Every payment logged permanently. Export a clean income report per property for Schedule E and CPA review.</p>
            </div>
          </div>
        </div>
      </div>
      <div class="repa-callout reveal">
        <p><strong>Why not automate the full transfer?</strong></p>
        <p>Cross-border money movement requires a remittance licence in every jurisdiction — a multi-year, multi-million dollar regulatory undertaking that would have delayed {{ config('app.name') }} by years and added no direct value to most landlords.</p>
        <p>Instead we focus on what actually saves you time: automated local collection, clean records, and tax-ready exports. The repatriation step — once or twice a year via your existing bank — takes 20 minutes.</p>
        <p>This also keeps your money inside your own accounts at all times. <strong>We never hold your funds.</strong></p>
      </div>
    </div>
  </div>
</section>
@endsection

@push('scripts')
<script>
// ── OVERVIEW ACTIVE STATE ON SCROLL ──
const sections = ['step-1','step-2','step-3'];
const cards    = document.querySelectorAll('.overview-card');
window.addEventListener('scroll', () => {
  let current = 0;
  sections.forEach((id, i) => {
    const el = document.getElementById(id);
    if (el && window.scrollY >= el.offsetTop - 200) current = i;
  });
  cards.forEach((c, i) => c.classList.toggle('active', i === current));
}, {passive:true});

// ── SCROLL REVEAL ──
const observer = new IntersectionObserver(
  entries => entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('visible'); observer.unobserve(e.target); } }),
  { threshold: 0.1, rootMargin: '0px 0px -50px 0px' }
);
document.querySelectorAll('.reveal').forEach(el => observer.observe(el));

// ── WAITLIST ──
function rmWaitlist(e) {
  e.preventDefault();
  const email = document.getElementById('rmEmail').value;
  const note  = document.getElementById('rmWaitlistNote');
  note.textContent = `✓ You're on the list — we'll reach out to ${email} soon.`;
  note.style.color = 'rgba(255,255,255,0.88)';
  document.getElementById('rmEmail').value = '';
}
</script>
@endpush
