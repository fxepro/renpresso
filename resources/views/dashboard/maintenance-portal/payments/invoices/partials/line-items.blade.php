@php
  $lineRows = old('lines');
  if ($lineRows === null && isset($invoice)) {
    $lineRows = $invoice->lines->map(fn ($l) => [
      'description' => $l->description,
      'quantity' => $l->quantity,
      'unit_price' => number_format($l->unit_price_minor / 100, 2, '.', ''),
    ])->all();
  }
  if (empty($lineRows)) {
    $lineRows = [['description' => '', 'quantity' => '1', 'unit_price' => '']];
  }
@endphp
<div class="db-card">
  <div class="db-card-header" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px">
    <span class="db-card-title">Line items</span>
    <button type="button" class="db-btn db-btn-ghost" id="inv-add-line" style="font-size:13px">+ Add line</button>
  </div>
  <div class="db-card-body" style="padding:0">
    <div class="db-table-wrap">
      <table class="db-table" id="inv-lines-table">
        <thead>
          <tr>
            <th style="width:44%">Description</th>
            <th style="width:14%">Qty</th>
            <th style="width:18%">Unit price</th>
            <th style="width:18%">Line total</th>
            <th></th>
          </tr>
        </thead>
        <tbody id="inv-lines-body">
          @foreach($lineRows as $i => $row)
          <tr class="inv-line-row">
            <td><input type="text" name="lines[{{ $i }}][description]" class="db-input" value="{{ $row['description'] ?? '' }}" required maxlength="500" placeholder="Labour, parts, call-out fee…"></td>
            <td><input type="number" name="lines[{{ $i }}][quantity]" class="db-input inv-qty" step="0.001" min="0.001" value="{{ $row['quantity'] ?? '1' }}" required></td>
            <td><input type="number" name="lines[{{ $i }}][unit_price]" class="db-input inv-unit" step="0.01" min="0" value="{{ $row['unit_price'] ?? '' }}" required placeholder="0.00"></td>
            <td class="inv-line-total" style="font-variant-numeric:tabular-nums">—</td>
            <td><button type="button" class="db-btn db-btn-ghost inv-remove-line" title="Remove" @if(count($lineRows) <= 1) disabled @endif>×</button></td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>
<script>
(function () {
  var body = document.getElementById('inv-lines-body');
  var addBtn = document.getElementById('inv-add-line');
  if (!body || !addBtn) return;

  function reindex() {
    body.querySelectorAll('.inv-line-row').forEach(function (row, i) {
      row.querySelectorAll('input[name^="lines"]').forEach(function (inp) {
        inp.name = inp.name.replace(/lines\[\d+\]/, 'lines[' + i + ']');
      });
    });
    var rows = body.querySelectorAll('.inv-line-row');
    rows.forEach(function (row) {
      var btn = row.querySelector('.inv-remove-line');
      if (btn) btn.disabled = rows.length <= 1;
    });
    recalc();
  }

  function lineTotal(row) {
    var q = parseFloat(row.querySelector('.inv-qty')?.value) || 0;
    var u = parseFloat(row.querySelector('.inv-unit')?.value) || 0;
    return q * u;
  }

  function recalc() {
    var sub = 0;
    body.querySelectorAll('.inv-line-row').forEach(function (row) {
      var t = lineTotal(row);
      sub += t;
      var cell = row.querySelector('.inv-line-total');
      if (cell) cell.textContent = t > 0 ? t.toFixed(2) : '—';
    });
    var tax = parseFloat(document.getElementById('inv-tax')?.value) || 0;
    var subEl = document.getElementById('inv-subtotal-preview');
    var totalEl = document.getElementById('inv-total-preview');
    if (subEl) subEl.textContent = sub.toFixed(2);
    if (totalEl) totalEl.textContent = (sub + tax).toFixed(2);
  }

  addBtn.addEventListener('click', function () {
    var i = body.querySelectorAll('.inv-line-row').length;
    var tr = document.createElement('tr');
    tr.className = 'inv-line-row';
    tr.innerHTML = '<td><input type="text" name="lines[' + i + '][description]" class="db-input" required maxlength="500"></td>'
      + '<td><input type="number" name="lines[' + i + '][quantity]" class="db-input inv-qty" step="0.001" min="0.001" value="1" required></td>'
      + '<td><input type="number" name="lines[' + i + '][unit_price]" class="db-input inv-unit" step="0.01" min="0" required placeholder="0.00"></td>'
      + '<td class="inv-line-total">—</td>'
      + '<td><button type="button" class="db-btn db-btn-ghost inv-remove-line">×</button></td>';
    body.appendChild(tr);
    reindex();
  });

  body.addEventListener('click', function (e) {
    if (!e.target.classList.contains('inv-remove-line')) return;
    var row = e.target.closest('.inv-line-row');
    if (body.querySelectorAll('.inv-line-row').length <= 1) return;
    row.remove();
    reindex();
  });

  body.addEventListener('input', function (e) {
    if (e.target.classList.contains('inv-qty') || e.target.classList.contains('inv-unit')) recalc();
  });
  var taxInp = document.getElementById('inv-tax');
  if (taxInp) taxInp.addEventListener('input', recalc);
  recalc();
})();
</script>
