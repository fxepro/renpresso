@extends('layouts.marketing', ['page' => 'about'])

@section('title', 'About us')
@section('meta_description', 'Renpresso is property management software for independent landlords — rent collection, maintenance, documents, and tax-ready exports.')

@section('content')

@include('partials.sections.marketing-hero', [
  'label' => 'About',
  'title' => 'Built for landlords<br>who do it themselves.',
  'lead' => config('app.name') . ' is property management software for independent landlords — not enterprise property managers, not real estate investment firms. Just you and your units.',
  'ctas' => [
    ['href' => url('/waitlist'), 'label' => 'Join the waitlist', 'class' => 'rm-btn rm-btn-primary btn-lg'],
    ['href' => url('/how-it-works'), 'label' => 'See how it works', 'class' => 'btn-outline-light'],
  ],
])

<!-- ══ STORY ══ -->
<section class="story story--hero">
  <div class="container">
    <div class="story-inner reveal">
      <div class="story-content">
        <p class="section-label">The problem we solve</p>
        <h2>Landlords deserve<br>better tools.</h2>
        <p>Managing rental properties means chasing rent, tracking maintenance, storing documents, and preparing records for your accountant — often across spreadsheets, text messages, and multiple apps.</p>
        <p>The landlord with five units across two states isn't unusual. But the software options are either too basic or built for large property management companies with enterprise budgets.</p>
        <p>{{ config('app.name') }} is built for this landlord. Not as a feature bolt-on. As the core product — designed from the ground up for <strong>independent, multi-property</strong> ownership.</p>
        <p>Tenants get a simple experience — clear invites, automatic payments, and receipts. Landlords get everything unified in one dashboard, with a single annual export for their CPA.</p>
      </div>
      <div>
        <div class="story-cards">
          <div class="story-card">
            <div class="story-card-label">Rent collection</div>
            <h4>ACH and card. Set up once.</h4>
            <p>Tenants authorize recurring payments during onboarding. Rent is collected on the due date — no chasing required.</p>
          </div>
          <div class="story-card">
            <div class="story-card-label">Portfolio view</div>
            <h4>Every unit. One dashboard.</h4>
            <p>See collected rent, outstanding balances, and trends across your entire portfolio at a glance.</p>
          </div>
          <div class="story-card">
            <div class="story-card-label">Multi-property</div>
            <h4>Every property. One account.</h4>
            <p>Leases, rent collection, maintenance, documents, and communication — managed from a single account.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ══ MISSION ══ -->
<section class="mission">
  <div class="mission-inner reveal">
    <p class="mission-label">Our mission</p>
    <h2 class="mission-statement">One landlord. Every unit. <em>One platform.</em></h2>
    <p class="mission-sub">Independent landlords manage more units than ever — often across multiple states. {{ config('app.name') }} is built to handle rent collection, maintenance, documents, and reporting without the complexity of enterprise software.</p>
  </div>
</section>

<!-- ══ VALUES ══ -->
<section class="values">
  <div class="container">
    <div class="reveal">
      <p class="section-label">What we believe</p>
      <h2 class="section-title">Six things we don't<br>compromise on.</h2>
    </div>
    <div class="values-grid reveal">
      <div class="value-card">
        <div class="value-num">01</div>
        <h3>Transparency over cleverness</h3>
        <p>Every fee is shown before it happens. Every payment is logged permanently. Every policy is written in plain language. We don't hide things in footnotes.</p>
      </div>
      <div class="value-card">
        <div class="value-num">02</div>
        <h3>Simple for tenants, powerful for landlords</h3>
        <p>Tenants see a straightforward payment setup. Landlords get the full picture — collection history, maintenance, documents, and exports.</p>
      </div>
      <div class="value-card">
        <div class="value-num">03</div>
        <h3>Your money, your control</h3>
        <p>We collect rent through licensed payment providers. We never hold your funds. Payments settle directly to your connected account.</p>
      </div>
      <div class="value-card">
        <div class="value-num">04</div>
        <h3>Honest about what we don't do</h3>
        <p>We are not a tax advisor. We are not a legal platform. We tell you this clearly at onboarding and we don't pretend otherwise to win a sale.</p>
      </div>
      <div class="value-card">
        <div class="value-num">05</div>
        <h3>Records that last</h3>
        <p>Seven years of document retention. Every payment logged permanently. Communication threads stored as correspondence records. When you need evidence, we have it.</p>
      </div>
      <div class="value-card">
        <div class="value-num">06</div>
        <h3>Small team, real access</h3>
        <p>We're not a corporation. Every contact form is read by a person. Early members get direct access to the founding team. Feedback actually changes what we build next.</p>
      </div>
    </div>
  </div>
</section>



<!-- ══ PRINCIPLES ══ -->
<section class="principles">
  <div class="container">
    <div class="reveal">
      <p class="section-label">How we build</p>
      <h2 class="section-title">The decisions we make<br>every day.</h2>
    </div>
    <div class="principles-grid reveal">
      <div class="principle">
        <span class="principle-icon">🔧</span>
        <div class="principle-content">
          <h4>Configuration over code</h4>
          <p>Landlords shouldn't need to configure payment processors or write integration code. We handle that invisibly.</p>
        </div>
      </div>
      <div class="principle">
        <span class="principle-icon">📋</span>
        <div class="principle-content">
          <h4>Records over features</h4>
          <p>Every payment, message, and document is stored permanently. When there's a dispute or an audit, the record is there.</p>
        </div>
      </div>
      <div class="principle">
        <span class="principle-icon">🔒</span>
        <div class="principle-content">
          <h4>Security by default</h4>
          <p>Encrypted in transit and at rest. Tenant data handled by licensed payment providers — not stored unnecessarily by us.</p>
        </div>
      </div>
      <div class="principle">
        <span class="principle-icon">💬</span>
        <div class="principle-content">
          <h4>On-platform communication</h4>
          <p>Messages between landlord and tenant stay in the app — timestamped, stored, and available when you need them.</p>
        </div>
      </div>
    </div>
  </div>
</section>

@include('partials.sections.cta-banner', [
  'title' => 'Ready to simplify<br>your portfolio?',
  'body' => 'Join the waitlist for early access. First property free for one month.',
  'href' => url('/waitlist'),
  'label' => 'Join the waitlist',
])

@endsection
