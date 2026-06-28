// Admin dashboard — collapsible sidebar nav sections
(function () {
  const KEY = 'rm_admin_nav_sections';

  function loadState() {
    try { return JSON.parse(localStorage.getItem(KEY) || '{}'); } catch (e) { return {}; }
  }

  function saveState(id, collapsed) {
    const s = loadState();
    s[id] = collapsed;
    try { localStorage.setItem(KEY, JSON.stringify(s)); } catch (e) { /* ignore */ }
  }

  document.querySelectorAll('.db-nav-collapse-toggle').forEach(function (btn) {
    const id = btn.getAttribute('data-nav-section');
    const panel = document.querySelector('[data-nav-panel="' + id + '"]');
    const icon = btn.querySelector('.db-nav-collapse-icon');
    if (!panel) return;

    const stored = loadState();
    if (stored[id] === false && panel.classList.contains('is-collapsed')) {
      panel.classList.remove('is-collapsed');
      btn.setAttribute('aria-expanded', 'true');
      if (icon) icon.textContent = '−';
    }
    if (stored[id] === true && !panel.classList.contains('is-collapsed')) {
      panel.classList.add('is-collapsed');
      btn.setAttribute('aria-expanded', 'false');
      if (icon) icon.textContent = '+';
    }

    btn.addEventListener('click', function () {
      panel.classList.toggle('is-collapsed');
      const collapsed = panel.classList.contains('is-collapsed');
      btn.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
      if (icon) icon.textContent = collapsed ? '+' : '−';
      saveState(id, collapsed);
    });
  });
})();
