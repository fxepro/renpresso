// Renpresso — marketing shell JavaScript (Vite bundle)

// ── NAV: scroll shadow + hamburger + active link ──
(function() {
  const nav    = document.getElementById('rmNav');
  const burger = document.getElementById('rmBurger');
  const drawer = document.getElementById('rmDrawer');
  if (!nav) return;

  window.addEventListener('scroll', () => {
    nav.classList.toggle('scrolled', scrollY > 20);
  }, { passive: true });

  if (burger && drawer) {
    burger.addEventListener('click', () => {
      const open = drawer.classList.toggle('open');
      burger.classList.toggle('open', open);
      burger.setAttribute('aria-expanded', open);
    });
  }

  // Active nav link — driven by data-page on <nav>
  const page = nav.dataset.page || '';
  document.querySelectorAll('.rm-nav-links a, .rm-drawer a').forEach(a => {
    const href = a.getAttribute('href') || '';
    if (href && href !== '#' && window.location.pathname.endsWith(href.replace('.html', ''))) {
      a.classList.add('active');
    }
    if (href === page) a.classList.add('active');
  });
})();

// ── SCROLL REVEAL ──
(function() {
  const observer = new IntersectionObserver(
    entries => entries.forEach(e => {
      if (e.isIntersecting) {
        e.target.classList.add('visible');
        observer.unobserve(e.target);
      }
    }),
    { threshold: 0.08, rootMargin: '0px 0px -40px 0px' }
  );
  document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
})();

// ── FEATURE TABS (homepage) ──
(function () {
  const tabs = document.querySelectorAll('.feature-tab');
  const panels = document.querySelectorAll('.feature-panel');
  if (!tabs.length || !panels.length) return;

  tabs.forEach((tab, i) => {
    tab.addEventListener('click', () => {
      tabs.forEach(t => t.classList.remove('active'));
      panels.forEach(p => p.classList.remove('active'));
      tab.classList.add('active');
      panels[i]?.classList.add('active');
    });
  });
})();

// ── RENTAL TYPE SWITCHER (/rental-types) ──
(function () {
  const root = document.getElementById('rentalTypeSwitcher');
  if (!root) return;

  const tabs = root.querySelectorAll('[data-rental-type]');
  const panels = {
    'long-term': document.getElementById('typeLongTerm'),
    'short-term': document.getElementById('typeShortTerm'),
    sublets: document.getElementById('typeSublets'),
    roommates: document.getElementById('typeRoommates'),
  };

  function activate(type) {
    if (!panels[type]) return;

    tabs.forEach(tab => {
      const on = tab.dataset.rentalType === type;
      tab.classList.toggle('active', on);
      tab.setAttribute('aria-selected', on ? 'true' : 'false');
    });

    Object.entries(panels).forEach(([key, panel]) => {
      if (panel) panel.hidden = key !== type;
    });

    panels[type].querySelectorAll('.reveal:not(.visible)').forEach(el => {
      el.classList.add('visible');
    });

    if (history.replaceState) {
      history.replaceState(null, '', '#' + type);
    }
  }

  tabs.forEach(tab => {
    tab.addEventListener('click', () => activate(tab.dataset.rentalType));
  });

  const hash = location.hash.replace('#', '');
  if (hash && panels[hash]) {
    activate(hash);
  }
})();

// ── FAQ ACCORDION ──
(function () {
  document.querySelectorAll('.faq-q').forEach(btn => {
    btn.addEventListener('click', () => {
      const item = btn.closest('.faq-item');
      if (!item) return;
      const isOpen = item.classList.contains('open');
      document.querySelectorAll('.faq-item').forEach(i => i.classList.remove('open'));
      if (!isOpen) item.classList.add('open');
    });
  });
})();

// ── WAITLIST FORM (legacy footer partial — client-only fallback) ──
function rmWaitlist(e) {
  e.preventDefault();
  const emailEl = document.getElementById('rmEmail');
  const noteEl  = document.getElementById('rmWaitlistNote');
  if (!emailEl || !noteEl) return;
  // TODO: POST to /api/waitlist
  noteEl.textContent = `✓ You're on the list — we'll reach out to ${emailEl.value} soon.`;
  noteEl.style.color = 'rgba(255,255,255,0.88)';
  emailEl.value = '';
}

// ── COOKIE CONSENT BANNER ──
(function () {
  const banner = document.getElementById('rmCookieBanner');
  if (!banner) return;

  const KEY = 'rm_cookie_consent';

  try {
    if (localStorage.getItem(KEY)) {
      banner.remove();
      return;
    }
  } catch (e) {
    /* storage blocked — show banner each visit */
  }

  banner.classList.remove('is-hidden');

  function dismiss(consent) {
    try {
      localStorage.setItem(KEY, JSON.stringify({ ...consent, ts: Date.now() }));
    } catch (e) {
      /* ignore */
    }
    banner.remove();
  }

  banner.querySelector('[data-cookie-accept]')?.addEventListener('click', () => {
    dismiss({ essential: true, preferences: true, analytics: true });
  });

  banner.querySelector('[data-cookie-manage]')?.addEventListener('click', () => {
    window.location.href = banner.dataset.cookiesUrl || '/cookies';
  });
})();
