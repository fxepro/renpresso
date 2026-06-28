@extends('admin.layout')
@section('title', 'Payments')
@section('page-title', 'Payments')
@section('breadcrumb', 'Settings')
@section('content')

<div class="db-card">
  <nav class="admin-pay-tabs" aria-label="Payment settings" style="display:flex;gap:4px;padding:0 24px;border-bottom:1px solid var(--cream-dark);background:var(--cream)">
    <a href="{{ route('admin.settings.payments', ['tab' => 'methods']) }}" class="admin-pay-tab {{ $tab === 'methods' ? 'active' : '' }}">Payment methods</a>
    <a href="{{ route('admin.settings.payments', ['tab' => 'processors']) }}" class="admin-pay-tab {{ $tab === 'processors' ? 'active' : '' }}">Processors</a>
  </nav>

  <div class="db-card-body" style="padding:0">
    @if($tab === 'methods')
      <form method="POST" action="{{ route('admin.settings.payments.choices.update') }}">
        @csrf
        @method('PUT')
        <input type="hidden" name="section" value="methods">
        <div class="db-table-wrap">
          <table class="db-table">
            <thead>
              <tr>
                <th>Method</th>
                <th>Processor</th>
                <th>Processor fee</th>
                <th>Fee paid by</th>
                <th style="text-align:center;width:100px">Offer</th>
              </tr>
            </thead>
            <tbody>
              @foreach($resolvedMethods as $method)
              <tr>
                <td><strong>{{ $method['label'] }}</strong></td>
                <td>
                  <select name="methods[{{ $method['slug'] }}][provider_slug]" class="db-select" style="min-width:180px">
                    @foreach($assignableProcessors as $prov)
                      <option value="{{ $prov->slug }}" @selected($method['provider_slug'] === $prov->slug)>{{ $prov->name }}</option>
                    @endforeach
                  </select>
                </td>
                <td>{{ $method['processor_fee_label'] ?? '—' }}</td>
                <td>{{ $feePaidByLabels[$method['fee_paid_by'] ?? ''] ?? '—' }}</td>
                <td style="text-align:center">
                  <input type="hidden" name="methods[{{ $method['slug'] }}][enabled]" value="0">
                  <input type="checkbox" name="methods[{{ $method['slug'] }}][enabled]" value="1" @checked($method['enabled']) aria-label="Offer {{ $method['label'] }}">
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        <div style="padding:18px 24px">
          <button type="submit" class="db-form-submit">Save</button>
        </div>
      </form>
    @else
      @php
        $roleLabels = [
            'processor'    => 'Collection processors',
            'payout'       => 'Payout providers',
            'subscription' => 'Subscription billing',
        ];
        $roleDescs = [
            'processor'    => 'Charge tenants and collect rent',
            'payout'       => 'Pay landlords in their home currency',
            'subscription' => 'Platform subscription billing',
        ];
        $grouped   = $processors->groupBy(fn($p) => $p->category ?? 'processor');
        $roleOrder = ['processor', 'payout', 'subscription'];
        $allCfg    = collect(config('platform_payment_providers.providers', []))->keyBy('slug');
        $countries = collect(config('countries', []));
      @endphp

      @foreach($roleOrder as $role)
        @if($grouped->has($role))
        <div class="pay-section">
          <div class="pay-section-header">
            <div>
              <span class="pay-section-title">{{ $roleLabels[$role] ?? ucfirst($role) }}</span>
              <span class="pay-section-desc">{{ $roleDescs[$role] ?? '' }}</span>
            </div>
          </div>
          <table class="db-table">
            <thead>
              <tr>
                <th style="width:220px">Provider</th>
                <th style="width:80px;text-align:center">Enabled</th>
                <th style="width:110px">Keys</th>
                <th style="width:130px">Markets</th>
                <th>Env keys needed</th>
                @if($role === 'processor')<th style="width:150px">Default for</th>@endif
                <th style="width:90px">Dashboard</th>
              </tr>
            </thead>
            <tbody>
              @foreach($grouped[$role]->sortBy('sort_order') as $provider)
              @php
                $cfg        = $allCfg[$provider->slug] ?? [];
                $markets    = $cfg['markets'] ?? [];
                $dashUrl    = $cfg['dashboard_url'] ?? null;
                $defaultFor = $countries->filter(fn($c) => ($c['processor'] ?? '') === $provider->slug)->keys()->implode(', ');
                $available  = $countries->filter(fn($c) => in_array($provider->slug, $c['available_processors'] ?? [$c['processor'] ?? '']))->keys()->implode(', ');
                $canToggle  = $provider->is_configured;
              @endphp
              <tr class="{{ $provider->is_enabled ? '' : 'pay-row-off' }}">

                {{-- Provider name + slug --}}
                <td>
                  <strong>{{ $provider->name }}</strong>
                  <code class="pay-slug">{{ $provider->slug }}</code>
                </td>

                {{-- Enable/disable toggle --}}
                <td style="text-align:center">
                  <form method="POST"
                        action="{{ route('admin.settings.payments.update', $provider) }}"
                        class="pay-toggle-form">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="is_enabled" value="{{ $provider->is_enabled ? '0' : '1' }}">
                    <button type="submit"
                            class="pay-toggle {{ $provider->is_enabled ? 'on' : 'off' }}"
                            @disabled(!$canToggle)
                            title="{{ !$canToggle ? 'Add API keys first' : ($provider->is_enabled ? 'Click to disable' : 'Click to enable') }}">
                      <span class="pay-toggle-knob"></span>
                    </button>
                  </form>
                </td>

                {{-- Keys status --}}
                <td>
                  @if($provider->is_configured)
                    <span class="badge badge-green" style="font-size:12px">✓ Set</span>
                  @else
                    <span class="badge" style="background:#fff3cd;color:#856404;font-size:12px">Missing</span>
                  @endif
                </td>

                {{-- Markets --}}
                <td style="font-size:12px;color:var(--text-light)">
                  @if($markets === 'global')
                    <span class="badge" style="background:#e8f5e9;color:#2e7d32;font-size:11px">Global</span>
                  @elseif(is_array($markets))
                    {{ implode(', ', $markets) }}
                  @else —
                  @endif
                </td>

                {{-- Env keys --}}
                <td>
                  @if(!empty($cfg['env_keys']))
                    @foreach($cfg['env_keys'] as $k)
                      <code class="pay-env-key">{{ strtoupper($provider->slug) }}_{{ strtoupper($k) }}</code>
                    @endforeach
                  @else
                    <span style="color:var(--text-light);font-size:13px">—</span>
                  @endif
                </td>

                {{-- Default for (processors only) --}}
                @if($role === 'processor')
                <td>
                  @if($defaultFor)
                    <span style="font-size:12px;color:var(--text-light)">{{ $defaultFor }}</span>
                  @endif
                  @if($available && $available !== $defaultFor)
                    <span class="pay-available-badge" title="Also available (not default): {{ $available }}">+ available</span>
                  @endif
                  @if(!$defaultFor && !$available)
                    <span style="font-size:12px;color:#ccc">—</span>
                  @endif
                  {{-- Link to Markets page to change default --}}
                  @if($defaultFor || $available)
                  <br><a href="{{ route('admin.settings.markets') }}" class="pay-set-default-link">Set default →</a>
                  @endif
                </td>
                @endif

                {{-- Dashboard link --}}
                <td>
                  @if($dashUrl)
                    <a href="{{ $dashUrl }}" target="_blank" rel="noopener" class="db-table-link" style="font-size:13px">Open ↗</a>
                  @else —
                  @endif
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        @endif
      @endforeach

      <div class="pay-footer-note">
        <strong>Missing keys?</strong>
        Add the env vars above to your <code>.env</code> file, then reload this page — the admin portal auto-syncs on every visit.
        <br>Change which processor is the <strong>default</strong> for a country in
        <a href="{{ route('admin.settings.markets') }}" class="db-table-link">Settings → Markets</a>.
      </div>
    @endif
  </div>
</div>

<p style="margin-top:16px"><a href="{{ route('admin.settings.index') }}" class="db-table-link">← All settings</a></p>
@endsection

@push('styles')
<style>
.admin-pay-tab {
  padding: 14px 18px;
  font-weight: 500;
  font-size: 14px;
  color: var(--text-light);
  text-decoration: none;
  border-bottom: 2px solid transparent;
  margin-bottom: -1px;
}
.admin-pay-tab:hover { color: var(--text-dark); }
.admin-pay-tab.active { color: var(--terra); border-bottom-color: var(--terra); }

.pay-section {
  border-bottom: 1px solid var(--cream-dark);
}
.pay-section:last-of-type { border-bottom: none; }

.pay-section-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 18px 24px 12px;
  background: var(--cream);
  border-bottom: 1px solid var(--cream-dark);
}
.pay-section-title {
  font-weight: 600;
  font-size: 14px;
  color: var(--text-dark);
  display: block;
}
.pay-section-desc {
  font-size: 12px;
  color: var(--text-light);
  display: block;
  margin-top: 2px;
}
.pay-env-key {
  display: inline-block;
  font-size: 11px;
  background: #f4f4f0;
  border: 1px solid #ddd;
  border-radius: 3px;
  padding: 1px 5px;
  margin: 2px 2px 2px 0;
  color: #444;
}

/* ── Toggle switch ─────────────────────────────────────── */
.pay-toggle-form { display:inline-flex; }

.pay-toggle {
  position: relative;
  display: inline-block;
  width: 44px;
  height: 24px;
  border: none;
  border-radius: 12px;
  cursor: pointer;
  transition: background 0.2s;
  padding: 0;
  flex-shrink: 0;
}
.pay-toggle.on  { background: var(--terra, #c0533a); }
.pay-toggle.off { background: #d1d5db; }
.pay-toggle:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}
.pay-toggle-knob {
  position: absolute;
  top: 3px;
  left: 3px;
  width: 18px;
  height: 18px;
  border-radius: 50%;
  background: #fff;
  transition: transform 0.2s;
  pointer-events: none;
  display: block;
}
.pay-toggle.on .pay-toggle-knob { transform: translateX(20px); }

/* ── Row dim when off ──────────────────────────────────── */
.pay-row-off td { opacity: 0.55; }
.pay-row-off td:nth-child(2) { opacity: 1; } /* keep toggle visible */

/* ── Misc ──────────────────────────────────────────────── */
.pay-slug {
  display: block;
  font-size: 11px;
  color: var(--text-light);
  margin-top: 2px;
}
.pay-available-badge {
  display: inline-block;
  font-size: 11px;
  background: #e8f0fe;
  color: #1a56db;
  border-radius: 3px;
  padding: 1px 5px;
  margin-top: 3px;
}
.pay-set-default-link {
  font-size: 11px;
  color: var(--terra);
  text-decoration: none;
  margin-top: 2px;
  display: inline-block;
}
.pay-set-default-link:hover { text-decoration: underline; }

.pay-footer-note {
  padding: 16px 24px;
  font-size: 13px;
  color: var(--text-light);
  border-top: 1px solid var(--cream-dark);
  background: var(--cream);
  line-height: 1.8;
}
</style>
@endpush
