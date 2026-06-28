@extends('layouts.marketing', ['page' => 'pricing'])

@section('title', 'Pricing')
@section('meta_description', 'Simple, transparent pricing for US landlords. First month free. $9 per unit per month after that. No setup fees, no contracts.')

@section('content')

@include('partials.sections.marketing-hero', [
  'label' => 'Pricing',
  'title' => 'Start free.<br><em>Scale simply.</em>',
  'lead' => 'First property free for one month. Pay per unit from the second. No setup fees, no contracts, no surprises.',
])

@include('partials.sections.pricing-band', [
  'sectionClass' => 'pricing-section',
  'cardVariant' => 'pricing',
  'plans' => [
    [
      'name' => 'Starter',
      'amount' => '0',
      'period' => '/ month',
      'description' => 'For landlords starting out. Every feature included. First month free, no card required.',
      'features' => [
        ['label' => '1 property in any supported country'],
        ['label' => 'Automated rent collection'],
        ['label' => 'Tenant invite & mandate setup'],
        ['label' => 'Maintenance requests'],
        ['label' => 'Document storage'],
        ['label' => 'In-app messaging'],
        ['label' => 'Annual tax export'],
        ['label' => 'Multi-property dashboard', 'available' => false],
        ['label' => 'Portfolio analytics', 'available' => false],
        ['label' => 'Priority support', 'available' => false],
      ],
      'cta' => ['href' => url('/waitlist'), 'label' => 'Start free — first month on us', 'class' => 'price-cta price-cta-outline'],
    ],
    [
      'name' => 'Per unit',
      'amount' => '9',
      'period' => '/ unit / mo',
      'featured' => true,
      'popular' => 'Most popular',
      'description' => 'For landlords with properties across multiple countries. First month free, then $9 per unit per month across your whole portfolio.',
      'features' => [
        ['label' => 'Unlimited properties'],
        ['label' => 'All 60+ supported countries'],
        ['label' => 'Automated rent collection'],
        ['label' => 'Multi-currency financial dashboard'],
        ['label' => 'FX rate history & repatriation log'],
        ['label' => 'Portfolio analytics'],
        ['label' => 'Full document management'],
        ['label' => 'Annual tax export per property'],
        ['label' => 'Priority email support'],
        ['label' => 'First month free — no card needed'],
      ],
      'cta' => ['href' => url('/waitlist'), 'label' => 'Start free trial', 'class' => 'price-cta price-cta-primary'],
    ],
    [
      'name' => 'Agency',
      'amount' => 'Talk to us',
      'talk' => true,
      'description' => 'For property managers and agencies handling portfolios on behalf of multiple owners.',
      'features' => [
        ['label' => 'Everything in Per unit'],
        ['label' => 'Sub-accounts per owner'],
        ['label' => 'Bulk lease import & management'],
        ['label' => 'White-label option'],
        ['label' => 'Dedicated account manager'],
        ['label' => 'Custom onboarding & training'],
        ['label' => 'SLA uptime guarantee'],
        ['label' => 'Volume pricing'],
        ['label' => 'API access'],
        ['label' => 'Custom contract'],
      ],
      'cta' => ['href' => url('/contact'), 'label' => 'Contact sales', 'class' => 'price-cta price-cta-outline'],
    ],
  ],
  'footnote' => 'Local payment fees are passed through at cost and shown before every collection. We add no markup.',
])


<!-- ══ CALCULATOR ══ -->
<section class="calculator">
  <div class="container">
    <div class="reveal">
      <p class="section-label">Estimate your cost</p>
      <h2 class="section-title" style="color:var(--white)">What will you pay?</h2>
      <p class="section-sub">Drag the sliders to calculate your monthly cost based on your portfolio size.</p>
    </div>
    <div class="calc-inner reveal">
      <div class="calc-controls">

        <div class="calc-group">
          <label>Number of properties <span id="propCount">3</span></label>
          <input type="range" min="1" max="20" step="1" value="3" id="propSlider">
          <div class="calc-slider-labels"><span>1 property</span><span>20 properties</span></div>
        </div>

        <div class="calc-group">
          <label>Average rent per property (USD) <span id="rentVal">$1,200</span></label>
          <input type="range" min="200" max="5000" step="100" value="1200" id="rentSlider">
          <div class="calc-slider-labels"><span>$200</span><span>$5,000</span></div>
        </div>

        <div style="padding: 42px; background: rgba(255,255,255,0.04); border-radius: var(--radius); border: 1px solid rgba(255,255,255,0.07);">
          <p style="font-size:19px; color:rgba(255,255,255,0.45); line-height:1.7; margin-bottom:16px;">
            <strong style="color:rgba(255,255,255,0.7);">How billing works:</strong><br>
            Your first property is free for one month. After that, all properties are billed at $9 per unit per month. No setup fees, no minimum terms, no contracts. Cancel anytime.
          </p>
          <div style="display:flex; flex-direction:column; gap:10px;">
            <div style="display:flex; align-items:center; gap:10px; font-size:19px; color:rgba(255,255,255,0.45);">
              <span style="width:6px;height:6px;border-radius:50%;background:var(--green);flex-shrink:0;"></span>First property — free for 1 month
            </div>
            <div style="display:flex; align-items:center; gap:10px; font-size:19px; color:rgba(255,255,255,0.45);">
              <span style="width:6px;height:6px;border-radius:50%;background:var(--terra-light);flex-shrink:0;"></span>Properties 2+ — $9 per unit per month
            </div>
            <div style="display:flex; align-items:center; gap:10px; font-size:19px; color:rgba(255,255,255,0.45);">
              <span style="width:6px;height:6px;border-radius:50%;background:rgba(255,255,255,0.3);flex-shrink:0;"></span>Local payment fees — passed through at cost, no markup
            </div>
          </div>
        </div>

      </div>

      <div class="calc-result">
        <div class="calc-result-label">Your estimated monthly platform cost</div>
        <div class="calc-result-value" id="calcMonthly">$18</div>
        <div class="calc-result-period" id="calcPeriodLabel">per month · 3 properties</div>

        <div class="calc-breakdown">
          <div class="calc-row">
            <span class="calc-row-label">Property 1 (free)</span>
            <span class="calc-row-val free">$0</span>
          </div>
          <div class="calc-row">
            <span class="calc-row-label" id="calcPaidLabel">Properties 2–3 (2 units)</span>
            <span class="calc-row-val" id="calcPaidVal">$18 / mo</span>
          </div>
          <div class="calc-row">
            <span class="calc-row-label">Annual platform cost</span>
            <span class="calc-row-val highlight" id="calcAnnual">$216 / yr</span>
          </div>
        </div>

        <div class="calc-breakdown" style="border-top: 1px solid rgba(255,255,255,0.07); padding-top:20px; border-bottom:none; padding-bottom:0; margin-bottom:16px;">
          <div class="calc-row">
            <span class="calc-row-label">Gross rent collected</span>
            <span class="calc-row-val" id="calcRent">$3,600 / mo</span>
          </div>
          <div class="calc-row">
            <span class="calc-row-label">Platform cost as % of rent</span>
            <span class="calc-row-val" id="calcPct">0.5%</span>
          </div>
        </div>

        <p class="calc-note">Payment processing fees (typically 0.8–2.9% per transaction) are separate and depend on payment method. These are always shown before each collection.</p>

        <a href="{{ url('/waitlist') }}" class="calc-cta">Get started free →</a>
      </div>
    </div>
  </div>
</section>

<!-- ══ PROCESSOR FEES ══ -->
<section class="fees">
  <div class="container">
    <div class="fees-intro reveal">
      <div>
        <p class="section-label">Payment fees</p>
        <h2 class="section-title">No markup.<br>Ever.</h2>
        <p class="section-sub">Local payment fees vary by country and payment method — we pass them through at exact cost. You always see the fee before a collection happens.</p>
      </div>
      <div>
        <div style="background: var(--green-pale); border-radius: var(--radius); padding: 48px; border: 1px solid rgba(42,107,74,0.2);">
          <p style="font-size:21px; color:var(--green); font-weight:600; margin-bottom:10px;">✓ Full transparency, every time</p>
          <p style="font-size:23px; color:var(--text-mid); line-height:1.7; font-weight:300;">Before every rent collection, {{ config('app.name') }} shows you the exact processor fee that will be deducted. No surprises on your statement. No rounding up. No "service fee" on top.</p>
        </div>
      </div>
    </div>

    <div class="fees-table-wrap reveal">
      <table class="fees-table">
        <thead>
          <tr>
            <th>Region &amp; payment method</th>
            <th>Payment method</th>
            <th>Typical fee</th>
            <th>On $1,000 rent</th>
          </tr>
        </thead>
        <tbody>
          <tr class="fee-highlight">
            <td><span class="fees-flag">🇺🇸</span>United States</td>
            <td>ACH direct debit</td>
            <td>0.8% (cap $5)</td>
            <td>$5.00</td>
          </tr>
          <tr>
            <td><span class="fees-flag">🇺🇸</span>United States</td>
            <td>Card payment</td>
            <td>2.9% + $0.30</td>
            <td>~$29.30</td>
          </tr>
          <tr>
            <td><span class="fees-flag">🇨🇦</span>Canada</td>
            <td>EFT direct debit</td>
            <td>0.8% (cap C$5)</td>
            <td>~$3.70</td>
          </tr>
          <tr>
            <td><span class="fees-flag">🇬🇧</span>United Kingdom</td>
            <td>Direct debit</td>
            <td>1.0% (cap £4)</td>
            <td>~$5.00</td>
          </tr>
          <tr>
            <td><span class="fees-flag">🇦🇺</span>Australia</td>
            <td>Direct debit</td>
            <td>1.0% (cap A$3.50)</td>
            <td>~$2.50</td>
          </tr>
          <tr>
            <td><span class="fees-flag">🇪🇺</span>Europe</td>
            <td>Bank transfer</td>
            <td>0.36% (cap €2)</td>
            <td>~$2.20</td>
          </tr>
        </tbody>
      </table>
    </div>
    <p class="fees-note">Fees are indicative and may vary by volume, account type, and local provider agreements. {{ config('app.name') }} displays the exact fee before every collection. All figures are approximate and based on standard published rates as of 2025.</p>
  </div>
</section>

<!-- ══ COMPARISON ══ -->
<section class="comparison">
  <div class="container">
    <div class="reveal">
      <p class="section-label">How we compare</p>
      <h2 class="section-title">Built for landlords<br>nobody else serves.</h2>
      <p class="section-sub">Every competitor either charges enterprise rates or lacks the features independent landlords need. {{ config('app.name') }} is purpose-built for US landlords who want simplicity without sacrificing capability.</p>
    </div>
    @include('partials.sections.comparison-table')
  </div>
</section>

@include('partials.sections.faq', [
  'eyebrow' => 'Pricing questions',
  'title' => 'Everything you need to know',
  'items' => [
    ['question' => 'How does the free first month work?', 'answer' => "Your first property is free for the first month — no credit card required to start. After that it's $9 per unit per month, whether you have one property or twenty. It's a trial period, not a permanent free tier."],
    ['question' => 'What counts as a "unit"?', 'answer' => 'One unit = one property with an active or recently active lease. A single building with multiple flats counts as multiple units. A property between tenancies (vacant) still counts as one unit if it has been active in the last 90 days. Properties you archive are not billed.'],
    ['question' => 'Are processor fees included in the $9?', 'answer' => 'No — payment processing fees are separate from the platform subscription. We add no markup. The $9 per unit is purely the ' . config('app.name') . ' platform fee. The processing fee for each rent collection is shown to you before it runs — typically 0.8% to 2.9% depending on payment method.'],
    ['question' => 'Can I change plans or cancel anytime?', 'answer' => "Yes — monthly billing, no contracts, cancel anytime. If you cancel, your data is retained for 90 days and you can export everything at any time. We don't hold your records hostage."],
    ['question' => 'Is there a discount for paying annually?', 'answer' => 'Annual billing will be available at launch with a 2-month discount (equivalent to 10 months for the price of 12). Monthly billing is available from day one with no commitment required.'],
    ['question' => 'Do you offer discounts for landlords with large portfolios?', 'answer' => 'Yes — volume pricing is available for landlords with 10+ properties and for agencies managing portfolios on behalf of multiple owners. Contact us to discuss custom pricing.'],
    ['question' => 'What happens during the free trial?', 'answer' => 'The free first month gives you full access to all features for your first property. No credit card required to start. After the first month, all properties are billed at $9 per unit per month. Add more properties at any time — each one is $9 per unit from day one.'],
  ],
])

@endsection

@push('scripts')
<script>
// ── CALCULATOR ──
const propSlider = document.getElementById('propSlider');
const rentSlider = document.getElementById('rentSlider');
const propCount  = document.getElementById('propCount');
const rentVal    = document.getElementById('rentVal');
const calcMonthly    = document.getElementById('calcMonthly');
const calcPeriodLabel = document.getElementById('calcPeriodLabel');
const calcPaidLabel  = document.getElementById('calcPaidLabel');
const calcPaidVal    = document.getElementById('calcPaidVal');
const calcAnnual     = document.getElementById('calcAnnual');
const calcRent       = document.getElementById('calcRent');
const calcPct        = document.getElementById('calcPct');

function fmt(n) { return '$' + n.toLocaleString(); }

function updateCalc() {
  const props = parseInt(propSlider.value);
  const rent  = parseInt(rentSlider.value);
  propCount.textContent = props;
  rentVal.textContent   = fmt(rent);

  const paidUnits   = Math.max(0, props - 1);
  const monthly     = paidUnits * 9;
  const annual      = monthly * 12;
  const totalRent   = props * rent;
  const pct         = totalRent > 0 ? ((monthly / totalRent) * 100).toFixed(1) : '0.0';

  calcMonthly.textContent     = fmt(monthly);
  calcPeriodLabel.textContent = `per month · ${props} propert${props === 1 ? 'y' : 'ies'}`;
  calcAnnual.textContent      = fmt(annual) + ' / yr';
  calcRent.textContent        = fmt(totalRent) + ' / mo';
  calcPct.textContent         = pct + '%';

  if (paidUnits === 0) {
    calcPaidLabel.textContent = 'Additional properties';
    calcPaidVal.textContent   = '$0 / mo';
  } else if (paidUnits === 1) {
    calcPaidLabel.textContent = 'Property 2 (1 unit)';
    calcPaidVal.textContent   = '$9 / mo';
  } else {
    calcPaidLabel.textContent = `Properties 2–${props} (${paidUnits} units)`;
    calcPaidVal.textContent   = fmt(monthly) + ' / mo';
  }
}

propSlider.addEventListener('input', updateCalc);
rentSlider.addEventListener('input', updateCalc);
updateCalc();

// ── FAQ ──
document.querySelectorAll('.faq-q').forEach(btn => {
  btn.addEventListener('click', () => {
    const item   = btn.closest('.faq-item');
    const isOpen = item.classList.contains('open');
    document.querySelectorAll('.faq-item').forEach(i => i.classList.remove('open'));
    if (!isOpen) item.classList.add('open');
  });
});

// ── SCROLL REVEAL ──
const observer = new IntersectionObserver(
  entries => entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('visible'); observer.unobserve(e.target); } }),
  { threshold: 0.08, rootMargin: '0px 0px -40px 0px' }
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
