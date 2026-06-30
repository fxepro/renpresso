@extends('layouts.marketing', ['page' => 'countries'])

@section('title', 'Countries')
@section('meta_description', 'Supported markets for Renpresso — rent collection and property management with local payment methods in each region.')

@section('content')

@include('partials.sections.marketing-hero', [
  'label' => 'Supported markets',
  'title' => 'Global markets.<br><em>Local payments.</em>',
  'lead' => 'Renpresso supports landlords across multiple regions — each with the right payment method connected automatically. More markets roll out over time.',
])

<!-- ══ STAT STRIP ══ -->
<div class="stat-strip">
  <div class="stat-strip-inner">
    <div class="stat-item">
      <div class="stat-value">50</div>
      <div class="stat-label">US states supported</div>
    </div>
    <div class="stat-item">
      <div class="stat-value">2</div>
      <div class="stat-label">Payment methods (ACH &amp; card)</div>
    </div>
  </div>
</div>

<!-- ══ REGION FILTER ══ -->
<div class="region-filter" id="regionFilter">
  <div class="region-filter-inner">
    <span class="region-filter-label">Filter:</span>
    <button class="region-btn active" onclick="filterRegion('all')">🌍 All regions</button>
    <button class="region-btn" onclick="filterRegion('north-america')"><span class="r-flag">🌎</span>North America</button>
    <button class="region-btn" onclick="filterRegion('europe')"><span class="r-flag">🇪🇺</span>Europe</button>
    <button class="region-btn" onclick="filterRegion('southeast-asia')"><span class="r-flag">🌏</span>SE Asia</button>
    <button class="region-btn" onclick="filterRegion('latam')"><span class="r-flag">🌎</span>LatAm</button>
    <button class="region-btn" onclick="filterRegion('pacific')"><span class="r-flag">🌏</span>Pacific</button>
    <div class="search-wrap">
      <input type="text" class="country-search" placeholder="Search country…" id="countrySearch" oninput="searchCountries(this.value)">
    </div>
  </div>
</div>

<!-- ══ COUNTRIES ══ -->
<section class="countries-section">
  <div class="container">

    <!-- North America -->
    <div class="region-block" data-region="north-america" id="block-north-america">
      <div class="region-heading">
        <h2>🌎 North America</h2>
        <span class="region-count">2 countries</span>
      </div>
      <div class="country-grid">
        <div class="country-card" data-name="united states usa us">
          <div class="country-card-top"><span class="cc-flag">🇺🇸</span><div><div class="cc-name">United States</div><div class="cc-currency">USD · US Dollar</div></div></div>
          <div class="cc-methods"><span class="cc-method">ACH</span><span class="cc-method">Cards</span></div>
        </div>
        <div class="country-card" data-name="canada">
          <div class="country-card-top"><span class="cc-flag">🇨🇦</span><div><div class="cc-name">Canada</div><div class="cc-currency">CAD · Canadian Dollar</div></div></div>
          <div class="cc-methods"><span class="cc-method">EFT</span><span class="cc-method">Cards</span></div>
        </div>
      </div>
    </div>

    <!-- Europe -->
    <div class="region-block" data-region="europe" id="block-europe">
      <div class="region-heading">
        <h2>🇪🇺 Europe</h2>
        <span class="region-count">25+ countries</span>
      </div>
      <div class="country-grid">
        <div class="country-card" data-name="united kingdom uk england britain">
          <div class="country-card-top"><span class="cc-flag">🇬🇧</span><div><div class="cc-name">United Kingdom</div><div class="cc-currency">GBP · British Pound</div></div></div>
          <div class="cc-methods"><span class="cc-method">BACS</span><span class="cc-method">Cards</span></div>
        </div>
        <div class="country-card" data-name="france french">
          <div class="country-card-top"><span class="cc-flag">🇫🇷</span><div><div class="cc-name">France</div><div class="cc-currency">EUR · Euro</div></div></div>
          <div class="cc-methods"><span class="cc-method">SEPA</span><span class="cc-method">Cards</span></div>
        </div>
        <div class="country-card" data-name="germany german deutschland">
          <div class="country-card-top"><span class="cc-flag">🇩🇪</span><div><div class="cc-name">Germany</div><div class="cc-currency">EUR · Euro</div></div></div>
          <div class="cc-methods"><span class="cc-method">SEPA</span><span class="cc-method">Cards</span></div>
        </div>
        <div class="country-card" data-name="spain spanish espana">
          <div class="country-card-top"><span class="cc-flag">🇪🇸</span><div><div class="cc-name">Spain</div><div class="cc-currency">EUR · Euro</div></div></div>
          <div class="cc-methods"><span class="cc-method">SEPA</span><span class="cc-method">Cards</span></div>
        </div>
        <div class="country-card" data-name="italy italian italia">
          <div class="country-card-top"><span class="cc-flag">🇮🇹</span><div><div class="cc-name">Italy</div><div class="cc-currency">EUR · Euro</div></div></div>
          <div class="cc-methods"><span class="cc-method">SEPA</span><span class="cc-method">Cards</span></div>
        </div>
        <div class="country-card" data-name="netherlands holland dutch">
          <div class="country-card-top"><span class="cc-flag">🇳🇱</span><div><div class="cc-name">Netherlands</div><div class="cc-currency">EUR · Euro</div></div></div>
          <div class="cc-methods"><span class="cc-method">SEPA</span><span class="cc-method">iDEAL</span></div>
        </div>
        <div class="country-card" data-name="portugal portuguese">
          <div class="country-card-top"><span class="cc-flag">🇵🇹</span><div><div class="cc-name">Portugal</div><div class="cc-currency">EUR · Euro</div></div></div>
          <div class="cc-methods"><span class="cc-method">SEPA</span><span class="cc-method">MB Way</span></div>
        </div>
        <div class="country-card" data-name="belgium belgian">
          <div class="country-card-top"><span class="cc-flag">🇧🇪</span><div><div class="cc-name">Belgium</div><div class="cc-currency">EUR · Euro</div></div></div>
          <div class="cc-methods"><span class="cc-method">SEPA</span><span class="cc-method">Bancontact</span></div>
        </div>
        <div class="country-card" data-name="ireland irish">
          <div class="country-card-top"><span class="cc-flag">🇮🇪</span><div><div class="cc-name">Ireland</div><div class="cc-currency">EUR · Euro</div></div></div>
          <div class="cc-methods"><span class="cc-method">SEPA</span><span class="cc-method">Cards</span></div>
        </div>
        <div class="country-card" data-name="austria austrian">
          <div class="country-card-top"><span class="cc-flag">🇦🇹</span><div><div class="cc-name">Austria</div><div class="cc-currency">EUR · Euro</div></div></div>
          <div class="cc-methods"><span class="cc-method">SEPA</span><span class="cc-method">EPS</span></div>
        </div>
        <div class="country-card" data-name="switzerland swiss">
          <div class="country-card-top"><span class="cc-flag">🇨🇭</span><div><div class="cc-name">Switzerland</div><div class="cc-currency">CHF · Swiss Franc</div></div></div>
          <div class="cc-methods"><span class="cc-method">SEPA</span><span class="cc-method">Cards</span></div>
        </div>
        <div class="country-card" data-name="sweden swedish">
          <div class="country-card-top"><span class="cc-flag">🇸🇪</span><div><div class="cc-name">Sweden</div><div class="cc-currency">SEK · Swedish Krona</div></div></div>
          <div class="cc-methods"><span class="cc-method">SEPA</span><span class="cc-method">Cards</span></div>
        </div>
        <div class="country-card" data-name="norway norwegian">
          <div class="country-card-top"><span class="cc-flag">🇳🇴</span><div><div class="cc-name">Norway</div><div class="cc-currency">NOK · Norwegian Krone</div></div></div>
          <div class="cc-methods"><span class="cc-method">SEPA</span><span class="cc-method">Cards</span></div>
        </div>
        <div class="country-card" data-name="denmark danish">
          <div class="country-card-top"><span class="cc-flag">🇩🇰</span><div><div class="cc-name">Denmark</div><div class="cc-currency">DKK · Danish Krone</div></div></div>
          <div class="cc-methods"><span class="cc-method">SEPA</span><span class="cc-method">Cards</span></div>
        </div>
        <div class="country-card" data-name="poland polish">
          <div class="country-card-top"><span class="cc-flag">🇵🇱</span><div><div class="cc-name">Poland</div><div class="cc-currency">PLN · Polish Złoty</div></div></div>
          <div class="cc-methods"><span class="cc-method">SEPA</span><span class="cc-method">P24</span></div>
        </div>
        <div class="country-card" data-name="turkey turkish">
          <div class="country-card-top"><span class="cc-flag">🇹🇷</span><div><div class="cc-name">Turkey</div><div class="cc-currency">TRY · Turkish Lira</div></div></div>
          <div class="cc-methods"><span class="cc-method">Iyzico</span><span class="cc-method">Cards</span></div>
        </div>
      </div>
    </div>

    <!-- Southeast Asia -->
    <div class="region-block" data-region="southeast-asia" id="block-southeast-asia">
      <div class="region-heading">
        <h2>🌏 Southeast Asia</h2>
        <span class="region-count">5 countries</span>
      </div>
      <div class="country-grid">
        <div class="country-card" data-name="indonesia indonesian">
          <div class="country-card-top"><span class="cc-flag">🇮🇩</span><div><div class="cc-name">Indonesia</div><div class="cc-currency">IDR · Rupiah</div></div></div>
          <div class="cc-methods"><span class="cc-method">Virtual accounts</span><span class="cc-method">GoPay</span><span class="cc-method">OVO</span></div>
        </div>
        <div class="country-card" data-name="philippines philippine filipino">
          <div class="country-card-top"><span class="cc-flag">🇵🇭</span><div><div class="cc-name">Philippines</div><div class="cc-currency">PHP · Peso</div></div></div>
          <div class="cc-methods"><span class="cc-method">GCash</span><span class="cc-method">Bank</span><span class="cc-method">Cards</span></div>
        </div>
        <div class="country-card" data-name="malaysia malaysian">
          <div class="country-card-top"><span class="cc-flag">🇲🇾</span><div><div class="cc-name">Malaysia</div><div class="cc-currency">MYR · Ringgit</div></div></div>
          <div class="cc-methods"><span class="cc-method">FPX</span><span class="cc-method">TNG</span><span class="cc-method">Cards</span></div>
        </div>
        <div class="country-card" data-name="vietnam vietnamese">
          <div class="country-card-top"><span class="cc-flag">🇻🇳</span><div><div class="cc-name">Vietnam</div><div class="cc-currency">VND · Dong</div></div></div>
          <div class="cc-methods"><span class="cc-method">Bank transfer</span><span class="cc-method">Momo</span></div>
        </div>
        <div class="country-card" data-name="thailand thai">
          <div class="country-card-top"><span class="cc-flag">🇹🇭</span><div><div class="cc-name">Thailand</div><div class="cc-currency">THB · Baht</div></div></div>
          <div class="cc-methods"><span class="cc-method">PromptPay</span><span class="cc-method">Cards</span></div>
        </div>
      </div>
    </div>

    <!-- Latin America -->
    <div class="region-block" data-region="latam" id="block-latam">
      <div class="region-heading">
        <h2>🌎 Latin America</h2>
        <span class="region-count">6 countries</span>
      </div>
      <div class="country-grid">
        <div class="country-card" data-name="brazil brazil brazilian">
          <div class="country-card-top"><span class="cc-flag">🇧🇷</span><div><div class="cc-name">Brazil</div><div class="cc-currency">BRL · Real</div></div></div>
          <div class="cc-methods"><span class="cc-method">Pix</span><span class="cc-method">Boleto</span><span class="cc-method">Cards</span></div>
        </div>
        <div class="country-card" data-name="mexico mexican">
          <div class="country-card-top"><span class="cc-flag">🇲🇽</span><div><div class="cc-name">Mexico</div><div class="cc-currency">MXN · Peso</div></div></div>
          <div class="cc-methods"><span class="cc-method">SPEI</span><span class="cc-method">OXXO</span><span class="cc-method">Cards</span></div>
        </div>
        <div class="country-card" data-name="argentina argentinian">
          <div class="country-card-top"><span class="cc-flag">🇦🇷</span><div><div class="cc-name">Argentina</div><div class="cc-currency">ARS · Peso</div></div></div>
          <div class="cc-methods"><span class="cc-method">Bank transfer</span><span class="cc-method">Cards</span></div>
        </div>
        <div class="country-card" data-name="colombia colombian">
          <div class="country-card-top"><span class="cc-flag">🇨🇴</span><div><div class="cc-name">Colombia</div><div class="cc-currency">COP · Peso</div></div></div>
          <div class="cc-methods"><span class="cc-method">PSE</span><span class="cc-method">Nequi</span><span class="cc-method">Cards</span></div>
        </div>
        <div class="country-card" data-name="chile chilean">
          <div class="country-card-top"><span class="cc-flag">🇨🇱</span><div><div class="cc-name">Chile</div><div class="cc-currency">CLP · Peso</div></div></div>
          <div class="cc-methods"><span class="cc-method">WebPay</span><span class="cc-method">Cards</span></div>
        </div>
        <div class="country-card" data-name="peru peruvian">
          <div class="country-card-top"><span class="cc-flag">🇵🇪</span><div><div class="cc-name">Peru</div><div class="cc-currency">PEN · Sol</div></div></div>
          <div class="cc-methods"><span class="cc-method">Yape</span><span class="cc-method">Bank</span><span class="cc-method">Cards</span></div>
        </div>
      </div>
    </div>

    <!-- Pacific & Asia Pacific -->
    <div class="region-block" data-region="pacific" id="block-pacific">
      <div class="region-heading">
        <h2>🌏 Pacific &amp; Asia Pacific</h2>
        <span class="region-count">6 countries</span>
      </div>
      <div class="country-grid">
        <div class="country-card" data-name="australia australian">
          <div class="country-card-top"><span class="cc-flag">🇦🇺</span><div><div class="cc-name">Australia</div><div class="cc-currency">AUD · Australian Dollar</div></div></div>
          <div class="cc-methods"><span class="cc-method">BECS</span><span class="cc-method">Cards</span></div>
        </div>
        <div class="country-card" data-name="new zealand kiwi">
          <div class="country-card-top"><span class="cc-flag">🇳🇿</span><div><div class="cc-name">New Zealand</div><div class="cc-currency">NZD · NZ Dollar</div></div></div>
          <div class="cc-methods"><span class="cc-method">Bank debit</span><span class="cc-method">Cards</span></div>
        </div>
        <div class="country-card" data-name="singapore singaporean">
          <div class="country-card-top"><span class="cc-flag">🇸🇬</span><div><div class="cc-name">Singapore</div><div class="cc-currency">SGD · Singapore Dollar</div></div></div>
          <div class="cc-methods"><span class="cc-method">PayNow</span><span class="cc-method">Cards</span></div>
        </div>
        <div class="country-card" data-name="hong kong">
          <div class="country-card-top"><span class="cc-flag">🇭🇰</span><div><div class="cc-name">Hong Kong</div><div class="cc-currency">HKD · HK Dollar</div></div></div>
          <div class="cc-methods"><span class="cc-method">FPS</span><span class="cc-method">Cards</span></div>
        </div>
        <div class="country-card" data-name="japan japanese">
          <div class="country-card-top"><span class="cc-flag">🇯🇵</span><div><div class="cc-name">Japan</div><div class="cc-currency">JPY · Yen</div></div></div>
          <div class="cc-methods"><span class="cc-method">Konbini</span><span class="cc-method">Bank</span><span class="cc-method">Cards</span></div>
        </div>
        <div class="country-card" data-name="united arab emirates uae dubai">
          <div class="country-card-top"><span class="cc-flag">🇦🇪</span><div><div class="cc-name">UAE</div><div class="cc-currency">AED · Dirham</div></div></div>
          <div class="cc-methods"><span class="cc-method">Telr</span><span class="cc-method">Cards</span></div>
        </div>
      </div>
    </div>

    <!-- No results message -->
    <div id="noResults" style="display:none; text-align:center; padding:60px 0;">
      <p style="font-size:21px; color:var(--text-mid); margin-bottom:12px;">No countries found for "<span id="searchTerm"></span>"</p>
      <p style="font-size:23px; color:var(--text-light);">Don't see your country? <a href="#request" style="color:var(--terra); font-weight:500;">Request it below →</a></p>
    </div>

  </div>
</section>



<!-- ══ REQUEST COUNTRY ══ -->
<section class="request-section" id="request">
  <div class="container">
    <div class="request-inner reveal">
      <div class="request-content">
        <p class="section-label">Don't see your country?</p>
        <h2>We're adding markets<br>every quarter.</h2>
        <p>New countries are a configuration change — not a code change. Tell us where your property is and we'll prioritise accordingly. Middle East, Eastern Europe, and Central Asia are next on the roadmap.</p>
        <div style="display:flex; flex-direction:column; gap:14px; margin-top:28px;">
          <div style="display:flex; align-items:flex-start; gap:14px;">
            <span style="font-size:23px; flex-shrink:0; margin-top:2px;">🗺️</span>
            <div>
              <h4 style="font-family:'Outfit',sans-serif; font-size:21px; font-weight:600; color:var(--text-dark); margin-bottom:3px;">Middle East (UAE, Saudi Arabia, Qatar)</h4>
              <p style="font-size:19px; color:var(--text-mid); font-weight:300;">Targeted Q3 2025</p>
            </div>
          </div>
          <div style="display:flex; align-items:flex-start; gap:14px;">
            <span style="font-size:23px; flex-shrink:0; margin-top:2px;">🗺️</span>
            <div>
              <h4 style="font-family:'Outfit',sans-serif; font-size:21px; font-weight:600; color:var(--text-dark); margin-bottom:3px;">Eastern Europe (Romania, Hungary, Czech Republic)</h4>
              <p style="font-size:19px; color:var(--text-mid); font-weight:300;">Targeted Q4 2025</p>
            </div>
          </div>
          <div style="display:flex; align-items:flex-start; gap:14px;">
            <span style="font-size:23px; flex-shrink:0; margin-top:2px;">🗺️</span>
            <div>
              <h4 style="font-family:'Outfit',sans-serif; font-size:21px; font-weight:600; color:var(--text-dark); margin-bottom:3px;">Central &amp; South Asia (Pakistan, Bangladesh, Sri Lanka)</h4>
              <p style="font-size:19px; color:var(--text-mid); font-weight:300;">Under evaluation — regulatory review in progress</p>
            </div>
          </div>
        </div>
      </div>
      <div>
        <div class="request-form-card">
          <h3>Request a country</h3>
          <p>Tell us where your property is. Requests go directly to our product team.</p>
          <form onsubmit="submitRequest(event)">
            <div class="form-group">
              <label>Country name</label>
              <input type="text" class="form-input" placeholder="e.g. Pakistan, Romania, Saudi Arabia" required>
            </div>
            <div class="form-group">
              <label>Your email</label>
              <input type="email" class="form-input" placeholder="your@email.com" required>
            </div>
            <div class="form-group">
              <label>How many properties?</label>
              <select class="form-select">
                <option>1 property</option>
                <option>2–5 properties</option>
                <option>6–10 properties</option>
                <option>10+ properties</option>
              </select>
            </div>
            <button type="submit" class="form-submit" id="requestBtn">Send request →</button>
            <p class="form-note" id="requestNote">We read every request and respond within 48 hours.</p>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection

@push('scripts')
<script>
// ── REGION FILTER ──
function filterRegion(region) {
  document.querySelectorAll('.region-btn').forEach(b => b.classList.remove('active'));
  event.currentTarget.classList.add('active');
  document.getElementById('countrySearch').value = '';

  const blocks = document.querySelectorAll('.region-block');
  blocks.forEach(block => {
    if (region === 'all' || block.dataset.region === region) {
      block.style.display = 'block';
    } else {
      block.style.display = 'none';
    }
  });
  document.getElementById('noResults').classList.add('is-hidden');
}

// ── COUNTRY SEARCH ──
function searchCountries(query) {
  const q = query.toLowerCase().trim();

  // Reset region filter buttons
  document.querySelectorAll('.region-btn').forEach(b => b.classList.remove('active'));
  document.querySelector('.region-btn').classList.add('active');

  if (!q) {
    document.querySelectorAll('.region-block').forEach(b => b.style.display = 'block');
    document.querySelectorAll('.country-card').forEach(c => c.style.display = 'block');
    document.getElementById('noResults').classList.add('is-hidden');
    return;
  }

  let anyVisible = false;
  document.querySelectorAll('.region-block').forEach(block => {
    let blockHasMatch = false;
    block.querySelectorAll('.country-card').forEach(card => {
      const name = card.dataset.name || '';
      const match = name.includes(q);
      card.style.display = match ? 'block' : 'none';
      if (match) { blockHasMatch = true; anyVisible = true; }
    });
    block.style.display = blockHasMatch ? 'block' : 'none';
  });

  const noRes = document.getElementById('noResults');
  const term  = document.getElementById('searchTerm');
  noRes.classList.toggle('is-hidden', anyVisible);
  if (term) term.textContent = query;
}

// ── REQUEST FORM ──
function submitRequest(e) {
  e.preventDefault();
  const btn  = document.getElementById('requestBtn');
  const note = document.getElementById('requestNote');
  btn.textContent  = '✓ Request received';
  btn.style.background = 'var(--green)';
  btn.disabled = true;
  note.textContent = 'We\'ll review your request and be in touch within 48 hours.';
  note.style.color = 'var(--green)';
}

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
