@push('styles')
<style id="rm-toast-styles">
.rm-toast-host {
  position: fixed;
  top: 80px;
  right: 20px;
  z-index: 9999;
  display: flex;
  flex-direction: column;
  gap: 10px;
  align-items: flex-end;
  max-width: min(400px, calc(100vw - 32px));
  pointer-events: none;
}
.rm-toast-host .rm-toast { pointer-events: auto; }
.rm-toast {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  padding: 14px 16px;
  border-radius: 10px;
  box-shadow: 0 10px 40px rgba(13, 31, 53, 0.18);
  border: 1px solid #e8e4dc;
  background: #fff;
  animation: rmToastIn 0.28s ease;
  font-family: 'Outfit', system-ui, sans-serif;
  font-size: 14px;
  line-height: 1.45;
  color: #0d1f35;
}
.rm-toast-success {
  border-color: rgba(42, 107, 74, 0.28);
  background: #e4f0ea;
}
.rm-toast-error {
  border-color: rgba(192, 57, 43, 0.28);
  background: #fdedec;
}
.rm-toast-info {
  border-color: rgba(13, 31, 53, 0.12);
  background: #faf8f3;
}
.rm-toast-icon {
  font-size: 17px;
  flex-shrink: 0;
  line-height: 1.25;
  width: 1.25em;
  text-align: center;
}
.rm-toast-body { flex: 1; min-width: 0; word-break: break-word; }
.rm-toast-close {
  flex-shrink: 0;
  width: 28px;
  height: 28px;
  border: none;
  background: transparent;
  cursor: pointer;
  color: #8a99aa;
  border-radius: 6px;
  font-size: 20px;
  line-height: 1;
  margin: -4px -6px -4px 0;
}
.rm-toast-close:hover { background: rgba(0, 0, 0, 0.06); color: #0d1f35; }
@keyframes rmToastIn {
  from { opacity: 0; transform: translateX(14px); }
  to { opacity: 1; transform: translateX(0); }
}
.rm-toast.rm-toast-out {
  animation: rmToastOut 0.22s ease forwards;
}
@keyframes rmToastOut {
  to { opacity: 0; transform: translateX(14px); }
}
@media (max-width: 600px) {
  .rm-toast-host { top: auto; bottom: 20px; right: 12px; left: 12px; max-width: none; align-items: stretch; }
}
</style>
@endpush
<script>
(function () {
  const HOST_ID = 'rmToastHost';
  function ensureHost() {
    var el = document.getElementById(HOST_ID);
    if (!el) {
      el = document.createElement('div');
      el.id = HOST_ID;
      el.className = 'rm-toast-host';
      el.setAttribute('aria-live', 'polite');
      document.body.appendChild(el);
    }
    return el;
  }
  window.rmToast = function (message, variant, duration) {
    if (!message) return;
    variant = variant || 'info';
    if (duration === undefined) duration = 5200;
    var icons = { success: '✓', error: '✗', info: 'ℹ' };
    var host = ensureHost();
    var row = document.createElement('div');
    row.className = 'rm-toast rm-toast-' + variant;
    row.setAttribute('role', 'status');
    var icon = document.createElement('span');
    icon.className = 'rm-toast-icon';
    icon.textContent = icons[variant] || icons.info;
    var body = document.createElement('span');
    body.className = 'rm-toast-body';
    body.textContent = message;
    var btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'rm-toast-close';
    btn.setAttribute('aria-label', 'Dismiss');
    btn.textContent = '×';
    row.appendChild(icon);
    row.appendChild(body);
    row.appendChild(btn);
    function close() {
      if (!row.parentNode) return;
      row.classList.add('rm-toast-out');
      setTimeout(function () { row.remove(); }, 220);
    }
    btn.addEventListener('click', close);
    host.appendChild(row);
    while (host.children.length > 6) {
      host.removeChild(host.firstChild);
    }
    var t = null;
    if (duration > 0) t = setTimeout(close, duration);
  };
})();
</script>
