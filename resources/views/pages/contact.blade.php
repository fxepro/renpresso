@extends('layouts.marketing', ['page' => 'contact'])

@section('title', 'Contact')
@section('meta_description', 'Get in touch with the Renpresso team. Sales, support, and partnership enquiries welcome.')

@section('content')

@include('partials.sections.marketing-hero', [
  'label' => 'Contact',
  'title' => 'We read <em>every</em><br>message.',
  'lead' => "We're a small team building something new. Your questions, feedback, and ideas go directly to the people building the product.",
])

<!-- ══ CONTACT TYPE TABS ══ -->
<div class="contact-types">
  <div class="contact-types-inner">
    <button class="ct-tab active" onclick="switchType('general', this)">
      <div class="ct-icon">💬</div>
      <div class="ct-label">General</div>
      <div class="ct-desc">Questions &amp; feedback</div>
    </button>
    <button class="ct-tab" onclick="switchType('sales', this)">
      <div class="ct-icon">🤝</div>
      <div class="ct-label">Sales</div>
      <div class="ct-desc">Agency &amp; enterprise</div>
    </button>
    <button class="ct-tab" onclick="switchType('press', this)">
      <div class="ct-icon">📰</div>
      <div class="ct-label">Press</div>
      <div class="ct-desc">Media &amp; interviews</div>
    </button>
    <button class="ct-tab" onclick="switchType('support', this)">
      <div class="ct-icon">🛟</div>
      <div class="ct-label">Support</div>
      <div class="ct-desc">Account &amp; technical</div>
    </button>
  </div>
</div>

<!-- ══ CONTACT BODY ══ -->
<section class="contact-body">
  <div class="contact-grid reveal">

    <!-- Info left -->
    <div class="contact-info">
      <h2 id="contactHeading">Got a question?<br>Let's talk.</h2>
      <p class="lead" id="contactLead">Whether you're a landlord with properties in three countries, a property manager exploring agency pricing, or a journalist covering proptech — we want to hear from you.</p>

      <div class="response-badge">
        <span class="response-dot"></span>
        We respond within 24 hours
      </div>

      <div class="contact-channels">
        <a href="mailto:hello@renpresso.com" class="channel-card">
          <span class="channel-icon">✉️</span>
          <div class="channel-info">
            <h4>Email us directly</h4>
            <p>hello@renpresso.com</p>
          </div>
          <span class="channel-arrow">→</span>
        </a>
        <a href="{{ url('/waitlist') }}" class="channel-card">
          <span class="channel-icon">🚀</span>
          <div class="channel-info">
            <h4>Join the waitlist</h4>
            <p>Reserve your spot and get first access</p>
          </div>
          <span class="channel-arrow">→</span>
        </a>
        <a href="{{ url('/countries') }}#request" class="channel-card">
          <span class="channel-icon">🌍</span>
          <div class="channel-info">
            <h4>Request a country</h4>
            <p>Don't see your market? Tell us.</p>
          </div>
          <span class="channel-arrow">→</span>
        </a>
      </div>

      <div class="office-note">
        <p><strong>Based remotely.</strong> Our team works across US time zones. Responses during business hours every day.</p>
      </div>
    </div>

    <!-- Form right -->
    <div class="contact-form-card">

      <div class="form-tabs">
        <button class="form-tab active" id="tab-general" onclick="switchFormTab('general')">General</button>
        <button class="form-tab" id="tab-sales" onclick="switchFormTab('sales')">Sales</button>
        <button class="form-tab" id="tab-press" onclick="switchFormTab('press')">Press</button>
        <button class="form-tab" id="tab-support" onclick="switchFormTab('support')">Support</button>
      </div>

      <!-- General form -->
      <div id="form-general">
        <form class="contact-form" onsubmit="submitContact(event, 'general')">
          <div class="form-row">
            <div class="form-group"><label>Name <span class="req">*</span></label><input type="text" class="form-input" placeholder="Your name" required></div>
            <div class="form-group"><label>Email <span class="req">*</span></label><input type="email" class="form-input" placeholder="you@example.com" required></div>
          </div>
          <div class="form-group"><label>Subject <span class="req">*</span></label>
            <select class="form-select" required>
              <option value="" disabled selected>What's this about?</option>
              <option>Product question</option>
              <option>Country availability</option>
              <option>Pricing question</option>
              <option>Partnership enquiry</option>
              <option>Feedback or suggestion</option>
              <option>Other</option>
            </select>
          </div>
          <div class="form-group"><label>Message <span class="req">*</span></label><textarea class="form-textarea" placeholder="Tell us what's on your mind…" required></textarea></div>
          <button type="submit" class="form-submit">Send message →</button>
          <p class="form-legal">We'll respond within 24 hours. By submitting you agree to our <a href="{{ url('/privacy') }}">Privacy Policy</a>.</p>
        </form>
        <div class="form-success" id="success-general"><div class="fs-icon">✉️</div><h3>Message sent.</h3><p>We'll be in touch within 24 hours — usually sooner.</p></div>
      </div>

      <!-- Sales form -->
      <div id="form-sales" class="is-hidden">
        <form class="contact-form" onsubmit="submitContact(event, 'sales')">
          <div class="form-row">
            <div class="form-group"><label>Name <span class="req">*</span></label><input type="text" class="form-input" placeholder="Your name" required></div>
            <div class="form-group"><label>Company</label><input type="text" class="form-input" placeholder="Company name (if applicable)"></div>
          </div>
          <div class="form-group"><label>Email <span class="req">*</span></label><input type="email" class="form-input" placeholder="you@company.com" required></div>
          <div class="form-row">
            <div class="form-group"><label>Portfolio size</label>
              <select class="form-select">
                <option value="" disabled selected>Select range</option>
                <option>2–10 properties</option>
                <option>11–50 properties</option>
                <option>51–200 properties</option>
                <option>200+ properties</option>
              </select>
            </div>
            <div class="form-group"><label>Markets</label><input type="text" class="form-input" placeholder="e.g. Texas, Florida"></div>
          </div>
          <div class="form-group"><label>Tell us about your setup <span class="req">*</span></label><textarea class="form-textarea" placeholder="How you currently manage rent collection, what's not working, what you need…" required></textarea></div>
          <button type="submit" class="form-submit">Request sales call →</button>
          <p class="form-legal">We'll reach out to schedule a call within one business day.</p>
        </form>
        <div class="form-success" id="success-sales"><div class="fs-icon">🤝</div><h3>Request received.</h3><p>Our sales team will reach out within one business day to schedule a call.</p></div>
      </div>

      <!-- Press form -->
      <div id="form-press" class="is-hidden">
        <form class="contact-form" onsubmit="submitContact(event, 'press')">
          <div class="form-row">
            <div class="form-group"><label>Name <span class="req">*</span></label><input type="text" class="form-input" placeholder="Your name" required></div>
            <div class="form-group"><label>Publication <span class="req">*</span></label><input type="text" class="form-input" placeholder="e.g. TechCrunch, FT" required></div>
          </div>
          <div class="form-group"><label>Email <span class="req">*</span></label><input type="email" class="form-input" placeholder="you@publication.com" required></div>
          <div class="form-group"><label>Enquiry type</label>
            <select class="form-select">
              <option value="" disabled selected>Select type</option>
              <option>Interview request</option>
              <option>Product demo</option>
              <option>Data / statistics request</option>
              <option>Comment or quote</option>
              <option>Embargo briefing</option>
            </select>
          </div>
          <div class="form-group"><label>Tell us about your piece <span class="req">*</span></label><textarea class="form-textarea" placeholder="What are you working on? Deadline?" required></textarea></div>
          <button type="submit" class="form-submit">Send press enquiry →</button>
          <p class="form-legal">Press enquiries are responded to within 4 hours during business hours.</p>
        </form>
        <div class="form-success" id="success-press"><div class="fs-icon">📰</div><h3>Enquiry received.</h3><p>Our press contact will respond within 4 business hours.</p></div>
      </div>

      <!-- Support form -->
      <div id="form-support" class="is-hidden">
        <form class="contact-form" onsubmit="submitContact(event, 'support')">
          <div class="form-row">
            <div class="form-group"><label>Name <span class="req">*</span></label><input type="text" class="form-input" placeholder="Your name" required></div>
            <div class="form-group"><label>Email <span class="req">*</span></label><input type="email" class="form-input" placeholder="Account email" required></div>
          </div>
          <div class="form-group"><label>Issue type</label>
            <select class="form-select">
              <option value="" disabled selected>Select type</option>
              <option>Payment not collected</option>
              <option>Tenant invite not received</option>
              <option>Account access issue</option>
              <option>Document not uploading</option>
              <option>Dashboard showing wrong data</option>
              <option>Other technical issue</option>
            </select>
          </div>
          <div class="form-group"><label>Property state</label><input type="text" class="form-input" placeholder="e.g. Texas, Colorado"></div>
          <div class="form-group"><label>Describe the issue <span class="req">*</span></label><textarea class="form-textarea" placeholder="What happened? What were you trying to do? Any error messages?" required></textarea></div>
          <button type="submit" class="form-submit">Submit support ticket →</button>
          <p class="form-legal">Support tickets are prioritised by severity. Payment issues are treated as urgent.</p>
        </form>
        <div class="form-success" id="success-support"><div class="fs-icon">🛟</div><h3>Ticket raised.</h3><p>Our support team will respond within 4 hours. Payment issues are treated as urgent.</p></div>
      </div>

    </div>
  </div>
</section>

<!-- ══ FAQ STRIP ══ -->
<section class="contact-faq">
  <div class="container">
    <div class="reveal u-text-center">
      <p class="contact-faq-eyebrow">Before you write</p>
      <h2 class="contact-faq-title">Common questions answered.</h2>
    </div>
    <div class="faq-grid">
      <div class="faq-card">
        <h4>When is Renpresso launching?</h4>
        <p>Renpresso is available in the United States and expanding to additional markets. <a href="{{ url('/waitlist') }}">Join the waitlist</a> to be notified when we open in your country.</p>
      </div>
      <div class="faq-card">
        <h4>Do you support my country?</h4>
        <p>Check the <a href="{{ url('/countries') }}">supported markets page</a> for current coverage — and if yours isn't listed yet, you can request it directly.</p>
      </div>
      <div class="faq-card">
        <h4>I'm a property manager — is there an agency plan?</h4>
        <p>Yes. Agency pricing covers sub-accounts, bulk lease management, white-label options, and a dedicated account manager. Use the Sales form above to talk to us.</p>
      </div>
      <div class="faq-card">
        <h4>I want to integrate with Renpresso — do you have an API?</h4>
        <p>API access is part of the Agency plan. If you're building an integration or exploring a partnership, use the Sales form and describe your use case.</p>
      </div>
      <div class="faq-card">
        <h4>How is my data protected?</h4>
        <p>All data is encrypted in transit and at rest. Full privacy compliance. <a href="{{ url('/privacy') }}">Read our privacy policy.</a></p>
      </div>
      <div class="faq-card">
        <h4>I have a property in a country not on your list.</h4>
        <p>Use the <a href="{{ url('/countries') }}#request">country request form</a> on our supported countries page. We review every request and prioritise by demand.</p>
      </div>
    </div>
  </div>
</section>
@include('partials.sections.cta-banner', [
  'title' => 'Ready to get started?',
  'body' => 'Join the waitlist and be first when we launch in your state.',
  'href' => url('/waitlist'),
  'label' => 'Join the waitlist →',
  'innerClass' => 'contact-cta-inner',
])

@endsection

@push('scripts')
<script>
// ── CONTACT TYPE TABS (top) ──
const headings = {
  general: { h: 'Got a question?<br>Let\'s talk.', p: 'Whether you\'re a landlord with properties in three countries, a property manager exploring agency pricing, or a journalist covering proptech — we want to hear from you.' },
  sales:   { h: 'Let\'s talk<br><em>agency pricing.</em>', p: 'Managing properties on behalf of multiple owners? We have a plan built for you. Tell us about your portfolio and we\'ll get back within one business day.' },
  press:   { h: 'Working on<br><em>a story?</em>', p: 'We\'re building something that hasn\'t existed before. Happy to brief journalists, provide data, and make the founding team available for interviews.' },
  support: { h: 'Something<br><em>not working?</em>', p: 'We prioritise support tickets by severity — payment issues are treated as urgent. Tell us exactly what happened and we\'ll resolve it fast.' },
};

function switchType(type, btn) {
  document.querySelectorAll('.ct-tab').forEach(t => t.classList.remove('active'));
  btn.classList.add('active');
  document.getElementById('contactHeading').innerHTML = headings[type].h;
  document.getElementById('contactLead').textContent = headings[type].p;
  switchFormTab(type);
}

// ── FORM TABS ──
function switchFormTab(type) {
  ['general','sales','press','support'].forEach(t => {
    document.getElementById('form-' + t).classList.toggle('is-hidden', t !== type);
    document.getElementById('tab-' + t).classList.toggle('active', t === type);
  });
}

// ── FORM SUBMIT ──
function submitContact(e, type) {
  e.preventDefault();
  const btn = e.target.querySelector('.form-submit');
  btn.textContent = 'Sending…';
  btn.disabled = true;
  setTimeout(() => {
    e.target.classList.add('is-hidden');
    document.getElementById('success-' + type).style.display = 'block';
  }, 700);
}

// ── SCROLL REVEAL ──
const observer = new IntersectionObserver(
  entries => entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('visible'); observer.unobserve(e.target); } }),
  { threshold: 0.08 }
);
document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
</script>
@endpush
