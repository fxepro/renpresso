@extends('layouts.marketing', ['page' => 'rental-types'])

@section('title', 'Rental types')
@section('meta_description', 'Long-term leases, short-term stays, sublets, and roommates — four rental models in one platform, each with its own lease, payment, and workflow.')

@section('content')

@include('partials.sections.marketing-hero', [
  'label' => 'Rental types',
  'title' => 'Long-term. Short-term.<br><em>Sublets &amp; roommates.</em>',
  'lead' => 'Most tools pick one model and ignore the rest. ' . config('app.name') . ' covers all four — with the right lease, payments, and workflow for each.',
  'ctas' => [
    ['href' => url('/waitlist'), 'label' => 'Join the waitlist', 'class' => 'rm-btn rm-btn-primary btn-lg'],
    ['href' => url('/listings'), 'label' => 'Browse listings', 'class' => 'btn-outline-light'],
  ],
])

<div class="type-switcher" id="rentalTypeSwitcher" role="tablist" aria-label="Rental type">
  <div class="container">
    <div class="type-switcher__track">
      <button type="button" class="type-switcher__tab active" role="tab" aria-selected="true" data-rental-type="long-term">Long-term</button>
      <button type="button" class="type-switcher__tab" role="tab" aria-selected="false" data-rental-type="short-term">Short-term</button>
      <button type="button" class="type-switcher__tab" role="tab" aria-selected="false" data-rental-type="sublets">Sublets</button>
      <button type="button" class="type-switcher__tab" role="tab" aria-selected="false" data-rental-type="roommates">Roommates</button>
    </div>
  </div>
</div>

<!-- ══ LONG-TERM ══ -->
<section class="landlord-section rental-type-section" id="typeLongTerm">
  <div class="container">
    <div class="reveal section-header">
      <p class="section-label">Long-term rentals</p>
      <h2 class="section-title">Annual leases. Monthly rent. Full lifecycle.</h2>
      <p class="section-sub">The default model for US landlords — single-family homes, condos, and small multi-unit buildings. ACH collection, lease documents, maintenance, and tax exports in one place.</p>
    </div>

    <div class="rental-type-cards reveal">
      <article class="rt-card rt-card--a">
        <div class="rt-card__body">
          <div class="feat-eyebrow"><span class="feat-num">01</span><span class="feat-tag">Leases</span></div>
          <h3>Master lease from day one</h3>
          <p class="lead">Create the lease, set rent and due date, upload the signed agreement, and invite your tenant. Rent collects automatically every month via ACH or card.</p>
          <ul class="feat-bullets">
            <li>12-month and custom lease terms</li>
            <li>Security deposit tracked — app never holds funds</li>
            <li>Automated reminders and arrears escalation</li>
            <li>Annual income report per property for Schedule E</li>
          </ul>
          <a href="{{ route('listings.index', ['tab' => 'long-term']) }}" class="link-arrow">Browse long-term listings →</a>
        </div>
        <div class="rt-card__preview">
          <div class="lease-card">
            <div class="lease-prop">
              <span class="lease-flag">🇺🇸</span>
              <div>
                <div class="lease-name">Oak Street, Austin TX</div>
                <div class="lease-addr">78701 · Texas · Long-term</div>
              </div>
            </div>
            <div class="lease-rows">
              <div class="lease-row"><span class="lease-row-label">Monthly rent</span><span class="lease-row-val rent">$ 2,400</span></div>
              <div class="lease-row"><span class="lease-row-label">Payment</span><span class="lease-row-val">ACH · Due 1st</span></div>
              <div class="lease-row"><span class="lease-row-label">Lease term</span><span class="lease-row-val">Jun 2025 – May 2026</span></div>
            </div>
            <div class="lease-status">
              <div class="lease-status-dot"></div>
              <span class="lease-status-text">Active — autopay confirmed</span>
            </div>
          </div>
        </div>
      </article>

      <article class="rt-card rt-card--b">
        <div class="rt-card__body">
          <div class="feat-eyebrow"><span class="feat-num">02</span><span class="feat-tag navy">Portfolio</span></div>
          <h3>Multiple units, one dashboard</h3>
          <p class="lead">Every long-term property in one view — occupancy, collections, maintenance history, and documents. Add units as you grow without switching tools.</p>
          <ul class="feat-bullets">
            <li>Single-family and multi-unit on one account</li>
            <li>Per-unit rent, lease, and tenant records</li>
            <li>Optional public listing in the directory</li>
            <li>Same $9 / unit pricing — first month free</li>
          </ul>
        </div>
        <div class="rt-card__preview">
          <div class="dash-widget">
            <div class="dash-widget-header">
              <div><div class="dw-title">Long-term portfolio</div><div class="dw-sub">5 units · 4 occupied</div></div>
              <div style="text-align:right"><div class="dw-total">$9,200</div><div class="dw-sub">Collected this month</div></div>
            </div>
            <div class="dw-bars">
              <div class="dw-bar-row"><span class="dw-bar-flag">🇺🇸</span><div class="dw-bar-track"><div class="dw-bar-fill fr"></div></div><span class="dw-bar-val">$2,400 · Austin</span></div>
              <div class="dw-bar-row"><span class="dw-bar-flag">🇺🇸</span><div class="dw-bar-track"><div class="dw-bar-fill in"></div></div><span class="dw-bar-val">$1,850 · Denver</span></div>
              <div class="dw-bar-row"><span class="dw-bar-flag">🇺🇸</span><div class="dw-bar-track"><div class="dw-bar-fill gb"></div></div><span class="dw-bar-val">$1,650 · Phoenix</span></div>
            </div>
          </div>
        </div>
      </article>
    </div>
  </div>
</section>

<!-- ══ SHORT-TERM ══ -->
<section class="landlord-section rental-type-section" id="typeShortTerm" hidden>
  <div class="container">
    <div class="reveal section-header">
      <p class="section-label">Short-term stays</p>
      <h2 class="section-title">Fixed dates. Guest bookings. Turnover handled.</h2>
      <p class="section-sub">Vacation rentals and furnished stays — operated by the property owner, not via sublet. Check-in and check-out dates, guest agreements, and cleaning between stays.</p>
    </div>

    <div class="rental-type-cards reveal">
      <article class="rt-card rt-card--a">
        <div class="rt-card__body">
          <div class="feat-eyebrow"><span class="feat-num">01</span><span class="feat-tag gold">Bookings</span></div>
          <h3>Manage the booking calendar</h3>
          <p class="lead">Convert a property to short-term mode and manage guest stays directly. Each booking has fixed start and end dates — separate from long-term leases and sublets.</p>
          <ul class="feat-bullets">
            <li>Per-stay guest agreement and payment</li>
            <li>Public listing in the short-term directory</li>
            <li>Availability calendar per unit</li>
            <li>Independent from long-term lease records</li>
          </ul>
          <a href="{{ route('listings.index', ['tab' => 'short-term']) }}" class="link-arrow">Browse short-term listings →</a>
        </div>
        <div class="rt-card__preview">
          <div class="lease-card">
            <div class="lease-prop">
              <span class="lease-flag">🇺🇸</span>
              <div>
                <div class="lease-name">Lakeview Cabin, Denver CO</div>
                <div class="lease-addr">Short-term · Guest stay</div>
              </div>
            </div>
            <div class="lease-rows">
              <div class="lease-row"><span class="lease-row-label">Check-in</span><span class="lease-row-val">Fri 14 Jun 2025</span></div>
              <div class="lease-row"><span class="lease-row-label">Check-out</span><span class="lease-row-val">Mon 17 Jun 2025</span></div>
              <div class="lease-row"><span class="lease-row-label">Stay total</span><span class="lease-row-val rent">$ 720</span></div>
            </div>
            <div class="lease-status">
              <div class="lease-status-dot"></div>
              <span class="lease-status-text">Confirmed · cleaning scheduled</span>
            </div>
          </div>
        </div>
      </article>

      <article class="rt-card rt-card--b">
        <div class="rt-card__body">
          <div class="feat-eyebrow"><span class="feat-num">02</span><span class="feat-tag green">Turnover</span></div>
          <h3>Cleaning crews between guests</h3>
          <p class="lead">Turnover works like maintenance — assign cleaning crews, track job status, and keep the unit ready for the next booking.</p>
          <ul class="feat-bullets">
            <li>Cleaning portal for turnover teams</li>
            <li>Auto-schedule on checkout (coming soon)</li>
            <li>Photo checklists per stay</li>
            <li>Separate from long-term maintenance requests</li>
          </ul>
        </div>
        <div class="rt-card__preview">
          <div class="maint-widget">
            <div class="maint-header">
              <span class="maint-title">Turnover schedule</span>
              <span class="maint-new">1 upcoming</span>
            </div>
            <div class="maint-item open">
              <div class="maint-item-top"><span class="maint-item-title">Post-checkout clean</span><span class="maint-item-cat">Turnover</span></div>
              <div class="maint-item-prop">🇺🇸 Lakeview Cabin · Mon 17 Jun</div>
              <div class="maint-item-status s-open">● Crew assigned · 11:00 AM</div>
            </div>
            <div class="maint-item">
              <div class="maint-item-top"><span class="maint-item-title">Linens &amp; restock</span><span class="maint-item-cat">Turnover</span></div>
              <div class="maint-item-prop">🇺🇸 Lakeview Cabin · Completed</div>
              <div class="maint-item-status s-done">● Ready for next guest</div>
            </div>
          </div>
        </div>
      </article>
    </div>
  </div>
</section>

<!-- ══ SUBLETS (entire unit) ══ -->
<section class="landlord-section rental-type-section" id="typeSublets" hidden>
  <div class="container">
    <div class="reveal section-header">
      <p class="section-label">Sublets</p>
      <h2 class="section-title">The entire unit. One sub-lease.</h2>
      <p class="section-sub">When the primary tenant needs to leave but the master lease still runs, they can sublet the full unit to one sub-letter — not room by room. Sublet settings live on the property record.</p>
    </div>

    <div class="rental-type-cards reveal">
      <article class="rt-card rt-card--a">
        <div class="rt-card__body">
          <div class="feat-eyebrow"><span class="feat-num">01</span><span class="feat-tag">Sublet settings</span></div>
          <h3>Configure once on the property</h3>
          <p class="lead">Enable sublets on any long-term property. A whole-unit sublet is a single sub-lease — one sub-letter, one sub-rent amount, same master lease dates.</p>
          <ul class="feat-bullets">
            <li>Toggle sublet allowed per property</li>
            <li>One active sub-lease for the full unit</li>
            <li>Background check required for the sub-letter</li>
            <li>Optional owner sign-off, or auto-activate after background check</li>
          </ul>
        </div>
        <div class="rt-card__preview">
          <div class="lease-card">
            <div class="lease-prop">
              <span class="lease-flag">🇺🇸</span>
              <div>
                <div class="lease-name">Maple Court Apt 4B, Austin TX</div>
                <div class="lease-addr">2 bd · Entire unit sublet</div>
              </div>
            </div>
            <div class="lease-rows">
              <div class="lease-row"><span class="lease-row-label">Master rent</span><span class="lease-row-val">$ 2,400 / mo</span></div>
              <div class="lease-row"><span class="lease-row-label">Sub-rent</span><span class="lease-row-val rent">$ 2,400 / mo</span></div>
              <div class="lease-row"><span class="lease-row-label">Scope</span><span class="lease-row-val">Full unit · 1 sub-letter</span></div>
              <div class="lease-row"><span class="lease-row-label">Sign-off</span><span class="lease-row-val">Required</span></div>
            </div>
            <div class="lease-status">
              <div class="lease-status-dot"></div>
              <span class="lease-status-text">Primary tenant on master lease</span>
            </div>
          </div>
        </div>
      </article>

      <article class="rt-card rt-card--b">
        <div class="rt-card__body">
          <div class="feat-eyebrow"><span class="feat-num">02</span><span class="feat-tag navy">Three roles</span></div>
          <h3>Owner, primary tenant, sub-letter</h3>
          <p class="lead">The primary tenant creates one sub-lease for the whole unit and sets sub-rent. The sub-letter gets a tenant portal scoped to that sub-lease. Master rent still collects from the primary tenant.</p>
          <ul class="feat-bullets">
            <li>Sub-lease inherits master lease terms and dates</li>
            <li>Sub-letter portal — payments, messages, maintenance</li>
            <li>Primary tenant manages the invite and sub-rent amount</li>
            <li>Separate from short-term guest bookings</li>
          </ul>
        </div>
        <div class="rt-card__preview">
          <div class="msg-widget">
            <div class="msg-thread-title">Sub-lease · Entire unit · Jordan K.</div>
            <div class="msg-bubble from"><div><div class="msg-text">Background check submitted — pending owner sign-off.</div><div class="msg-time">System · 9:02am</div></div></div>
            <div class="msg-bubble to"><div><div class="msg-text">Signed off. Sub-lease activates on the 1st.</div><div class="msg-time">Owner · 9:18am</div></div></div>
            <div style="padding:10px 14px;border-radius:10px;background:rgba(42,107,74,0.15);border:1px solid rgba(42,107,74,0.25);font-size:var(--fs-ui);color:rgba(255,255,255,0.6);margin-top:12px;">✓ Sub-rent $2,400 / mo · ACH setup complete</div>
          </div>
        </div>
      </article>
    </div>
  </div>
</section>

<!-- ══ ROOMMATES (rooms within unit) ══ -->
<section class="landlord-section rental-type-section" id="typeRoommates" hidden>
  <div class="container">
    <div class="reveal section-header">
      <p class="section-label">Roommates</p>
      <h2 class="section-title">Individual rooms. Shared unit.</h2>
      <p class="section-sub">The primary tenant holds the master lease while renting out rooms within the unit — student housing, roommate splits, or shared flats. Each roommate gets their own sub-lease, rent, and portal.</p>
    </div>

    <div class="rental-type-cards reveal">
      <article class="rt-card rt-card--a">
        <div class="rt-card__body">
          <div class="feat-eyebrow"><span class="feat-num">01</span><span class="feat-tag">Room leases</span></div>
          <h3>One sub-lease per room</h3>
          <p class="lead">Enable sublets on the property, then the primary tenant opens a sub-lease for each room — up to the bedroom count. Each roommate pays their own sub-rent.</p>
          <ul class="feat-bullets">
            <li>Max concurrent room sub-leases = bedroom count</li>
            <li>Primary tenant sets sub-rent per room</li>
            <li>Background check required for every roommate</li>
            <li>Optional owner sign-off per roommate</li>
          </ul>
        </div>
        <div class="rt-card__preview">
          <div class="lease-card">
            <div class="lease-prop">
              <span class="lease-flag">🇺🇸</span>
              <div>
                <div class="lease-name">Pine Ave Duplex, Denver CO</div>
                <div class="lease-addr">4 bd · Room leases active</div>
              </div>
            </div>
            <div class="lease-rows">
              <div class="lease-row"><span class="lease-row-label">Master rent</span><span class="lease-row-val">$ 3,200 / mo</span></div>
              <div class="lease-row"><span class="lease-row-label">Room A</span><span class="lease-row-val">$ 900 / mo · occupied</span></div>
              <div class="lease-row"><span class="lease-row-label">Room B</span><span class="lease-row-val rent">$ 850 / mo · occupied</span></div>
              <div class="lease-row"><span class="lease-row-label">Rooms open</span><span class="lease-row-val">2 of 4</span></div>
            </div>
            <div class="lease-status">
              <div class="lease-status-dot"></div>
              <span class="lease-status-text">Primary tenant on master lease</span>
            </div>
          </div>
        </div>
      </article>

      <article class="rt-card rt-card--b">
        <div class="rt-card__body">
          <div class="feat-eyebrow"><span class="feat-num">02</span><span class="feat-tag navy">Split rent</span></div>
          <h3>Each roommate, their own ledger</h3>
          <p class="lead">Roommates get a tenant portal scoped to their room — payments, messages, and maintenance tied to their sub-lease. The primary tenant collects sub-rent from each room.</p>
          <ul class="feat-bullets">
            <li>Per-room sub-rent and payment setup</li>
            <li>Sub-lease inherits master lease terms and dates</li>
            <li>Primary tenant manages invites per room</li>
            <li>Distinct from a whole-unit sublet</li>
          </ul>
        </div>
        <div class="rt-card__preview">
          <div class="msg-widget">
            <div class="msg-thread-title">Room B · Alex M.</div>
            <div class="msg-bubble from"><div><div class="msg-text">Background check submitted — pending owner sign-off.</div><div class="msg-time">System · 9:02am</div></div></div>
            <div class="msg-bubble to"><div><div class="msg-text">Signed off. Room lease activates on the 1st.</div><div class="msg-time">Owner · 9:18am</div></div></div>
            <div style="padding:10px 14px;border-radius:10px;background:rgba(42,107,74,0.15);border:1px solid rgba(42,107,74,0.25);font-size:var(--fs-ui);color:rgba(255,255,255,0.6);margin-top:12px;">✓ Sub-rent $850 / mo · ACH setup complete</div>
          </div>
        </div>
      </article>
    </div>
  </div>
</section>

<section class="comparison rental-types-compare">
  <div class="container">
    <div class="reveal section-header section-header--center u-text-center">
      <p class="section-label u-text-center">Side by side</p>
      <h2 class="section-title u-text-center">Four models. One platform.</h2>
      <p class="section-sub">Most software forces you into one lane. Here is how each type works in {{ config('app.name') }}.</p>
    </div>
    <div class="comp-table-wrap reveal">
      <table class="comp-table">
        <thead>
          <tr>
            <th>Aspect</th>
            <th class="ours">Long-term</th>
            <th>Short-term</th>
            <th>Sublets</th>
            <th>Roommates</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>Scope</td>
            <td class="ours">Full unit</td>
            <td>Full unit</td>
            <td>Entire unit</td>
            <td>Rooms within unit</td>
          </tr>
          <tr>
            <td>Who operates</td>
            <td class="ours">Property owner</td>
            <td>Property owner</td>
            <td>Primary tenant</td>
            <td>Primary tenant</td>
          </tr>
          <tr>
            <td>Typical term</td>
            <td class="ours">6–12+ months</td>
            <td>Days to weeks</td>
            <td>Within master lease</td>
            <td>Within master lease</td>
          </tr>
          <tr>
            <td>Rent collection</td>
            <td class="ours">ACH / card monthly</td>
            <td>Per stay</td>
            <td>Sub-rent · full unit</td>
            <td>Sub-rent · per room</td>
          </tr>
          <tr>
            <td>Public listings</td>
            <td class="ours"><span class="comp-check">✓</span></td>
            <td><span class="comp-check">✓</span></td>
            <td><span class="comp-cross">✗</span> Private to unit</td>
            <td><span class="comp-cross">✗</span> Private to unit</td>
          </tr>
          <tr>
            <td>Turnover cleaning</td>
            <td class="ours"><span class="comp-cross">✗</span></td>
            <td><span class="comp-check">✓</span></td>
            <td><span class="comp-cross">✗</span></td>
            <td><span class="comp-cross">✗</span></td>
          </tr>
          <tr>
            <td>Background check</td>
            <td class="ours">Optional</td>
            <td>Guest KYC (TBD)</td>
            <td><span class="comp-check">✓</span> Always</td>
            <td><span class="comp-check">✓</span> Always</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</section>

@endsection
