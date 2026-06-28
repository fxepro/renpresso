<script>
(function () {
  var landlordSel = document.getElementById('inv-landlord');
  var propertySel = document.getElementById('inv-property');
  var requestSel = document.getElementById('inv-request');
  var hintEl = document.getElementById('inv-property-hint');
  if (!landlordSel || !propertySel || !requestSel) return;

  var optionsUrl = @json(route('maint.payments.invoices.form-options'));
  var presetProperty = @json($oldProperty ?? null);
  var presetRequest = @json($oldRequest ?? null);

  function fillSelect(sel, items, placeholder, selectedId) {
    sel.innerHTML = '';
    var empty = document.createElement('option');
    empty.value = '';
    empty.textContent = placeholder;
    sel.appendChild(empty);
    items.forEach(function (item) {
      var opt = document.createElement('option');
      opt.value = item.id;
      opt.textContent = item.label;
      if (selectedId && selectedId === item.id) opt.selected = true;
      sel.appendChild(opt);
    });
    sel.disabled = false;
  }

  function clearDependents() {
    propertySel.innerHTML = '<option value="">Select landlord first…</option>';
    requestSel.innerHTML = '<option value="">Select landlord first…</option>';
    propertySel.disabled = true;
    requestSel.disabled = true;
    if (hintEl) { hintEl.style.display = 'none'; hintEl.textContent = ''; }
  }

  function loadForLandlord(landlordId, keepSelections) {
    if (!landlordId) {
      clearDependents();
      return;
    }
    propertySel.disabled = true;
    requestSel.disabled = true;
    propertySel.innerHTML = '<option value="">Loading…</option>';
    requestSel.innerHTML = '<option value="">Loading…</option>';

    fetch(optionsUrl + '?landlord_id=' + encodeURIComponent(landlordId), {
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        fillSelect(propertySel, data.properties || [], '— Optional —', keepSelections ? presetProperty : null);
        fillSelect(requestSel, data.requests || [], '— Optional —', keepSelections ? presetRequest : null);
        if (hintEl) {
          if (data.hint) {
            hintEl.textContent = data.hint;
            hintEl.style.display = 'block';
          } else {
            hintEl.style.display = 'none';
            hintEl.textContent = '';
          }
        }
      })
      .catch(function () {
        propertySel.innerHTML = '<option value="">Could not load properties</option>';
        requestSel.innerHTML = '<option value="">Could not load requests</option>';
      });
  }

  landlordSel.addEventListener('change', function () {
    var opt = landlordSel.options[landlordSel.selectedIndex];
    if (opt && opt.value) {
      var name = document.getElementById('inv-bill-name');
      var email = document.getElementById('inv-bill-email');
      if (name && !name.value) name.value = opt.getAttribute('data-name') || '';
      if (email && !email.value) email.value = opt.getAttribute('data-email') || '';
    }
    presetProperty = null;
    presetRequest = null;
    loadForLandlord(landlordSel.value, false);
  });

  if (landlordSel.value) {
    loadForLandlord(landlordSel.value, true);
  } else {
    clearDependents();
  }
})();
</script>
