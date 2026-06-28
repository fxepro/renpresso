<script>
(function () {
  const nav = document.getElementById('rmNav');
  const burger = document.getElementById('rmBurger');
  const drawer = document.getElementById('rmDrawer');
  if (!nav || !burger || !drawer) return;

  window.addEventListener('scroll', () => nav.classList.toggle('scrolled', scrollY > 20), { passive: true });

  burger.addEventListener('click', () => {
    const open = drawer.classList.toggle('open');
    burger.classList.toggle('open', open);
    burger.setAttribute('aria-expanded', open ? 'true' : 'false');
  });
})();
</script>
