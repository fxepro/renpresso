// ═══════════════════════════════════════════════════════
// Rentersmaxx — Shared JavaScript
// ═══════════════════════════════════════════════════════

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

// ── WAITLIST FORM (CTA banner — shared across pages) ──
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
