@if($user->payoutAccounts->isNotEmpty())
  <div class="db-card">
    <div class="db-card-header" style="display:flex;align-items:center;justify-content:space-between;gap:12px">
      <span class="db-card-title">Saved accounts</span>
      <a href="{{ route('landlord.account', ['tab' => 'banks', 'sec' => 'add']) }}" class="db-btn db-btn-primary" style="font-size:12px;padding:6px 14px;text-decoration:none">+ Add</a>
    </div>
    <div class="db-table-wrap">
      <table class="db-table">
        <thead>
          <tr>
            <th>Label</th>
            <th>Purpose</th>
            <th>Country</th>
            <th>Currency</th>
            <th>Holder</th>
            <th>Hint</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @foreach($user->payoutAccounts as $acc)
          <tr>
            <td><strong>{{ $acc->label }}</strong></td>
            <td>{{ \App\Support\CrossBorderPayout::purposeLabel($acc->purpose ?? 'collection') }}</td>
            <td>{{ $acc->country_code }}</td>
            <td>{{ $acc->currency_code }}</td>
            <td>{{ $acc->holder_name }}</td>
            <td>{{ $acc->display_hint ?? '—' }}</td>
            <td>
              <form method="POST" action="{{ route('landlord.account.payout-accounts.destroy', $acc) }}" style="display:inline" onsubmit="return confirm('Remove this account?');">
                @csrf @method('DELETE')
                <button type="submit" class="db-btn db-btn-danger" style="font-size:12px;padding:5px 10px">Remove</button>
              </form>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
@else
  <div class="db-empty" style="padding:32px 20px">
    <p style="margin:0 0 12px;color:var(--text-mid)">No bank accounts on file yet.</p>
    <a href="{{ route('landlord.account', ['tab' => 'banks', 'sec' => 'add']) }}" class="db-btn db-btn-primary" style="text-decoration:none">Add account</a>
  </div>
@endif
