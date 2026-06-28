// Dashboard shell — sidebar collapse (desktop)
(function () {
  const body = document.body;
  const toggle = document.getElementById('dbSidebarToggle');
  if (!toggle) return;

  const KEY = body.dataset.sidebarKey || 'rm_db_sidebar_collapsed';
  const mq = window.matchMedia('(min-width: 901px)');

  function readStored() {
    try { return localStorage.getItem(KEY) === '1'; } catch (e) { return false; }
  }

  function apply(collapsed) {
    if (mq.matches) {
      body.classList.toggle('db-sidebar-collapsed', collapsed);
      toggle.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
      toggle.title = collapsed ? 'Expand sidebar' : 'Collapse sidebar';
    } else {
      body.classList.remove('db-sidebar-collapsed');
      toggle.setAttribute('aria-expanded', 'true');
      toggle.title = 'Collapse sidebar';
    }
  }

  apply(readStored());

  toggle.addEventListener('click', function () {
    if (!mq.matches) return;
    const next = !body.classList.contains('db-sidebar-collapsed');
    try { localStorage.setItem(KEY, next ? '1' : '0'); } catch (e) { /* ignore */ }
    apply(next);
  });

  mq.addEventListener('change', function () { apply(readStored()); });
})();
