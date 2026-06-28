<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CountryMarket;
use App\Models\PlatformPaymentProvider;
use App\Models\PlatformSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminSettingsController extends Controller
{
    public function index(): View
    {
        PlatformPaymentProvider::syncFromConfig();

        return view('admin.settings.index', [
            'settings' => PlatformSetting::current(),
            'providerCount' => PlatformPaymentProvider::count(),
            'marketCount' => CountryMarket::where('is_active', true)->count(),
        ]);
    }

    public function general(): View
    {
        return view('admin.settings.general', [
            'settings' => PlatformSetting::current(),
        ]);
    }

    public function updateGeneral(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'reporting_currency'             => 'required|string|size:3',
            'default_billing_currency'       => 'required|string|size:3',
            'first_property_free_months'     => 'required|integer|min:0|max:12',
            'signup_fee'                     => 'required|numeric|min:0',
            'monthly_fee'                    => 'required|numeric|min:0',
            'maintenance_commission_percent' => 'required|numeric|min:0|max:100',
        ]);

        PlatformSetting::current()->update([
            'reporting_currency'                 => $validated['reporting_currency'],
            'default_billing_currency'           => $validated['default_billing_currency'],
            'first_property_free_months'         => $validated['first_property_free_months'],
            'default_signup_fee_minor_per_unit'  => $this->majorToMinor($validated['signup_fee']),
            'default_monthly_fee_minor_per_unit' => $this->majorToMinor($validated['monthly_fee']),
            'default_maintenance_commission_bps' => $this->percentToBps($validated['maintenance_commission_percent']),
        ]);

        return redirect()
            ->route('admin.settings.general')
            ->with('success', 'General settings saved.');
    }

    public function payments(): View
    {
        PlatformPaymentProvider::syncFromConfig();

        $settings = PlatformSetting::current();
        $feePaidByLabels = config('payment_methods.fee_paid_by', []);
        $processors = PlatformPaymentProvider::query()->orderBy('sort_order')->orderBy('name')->get();
        $resolvedMethods = $settings->resolvedPaymentMethods();
        $tab = in_array(request('tab'), ['methods', 'processors'], true) ? request('tab') : 'methods';

        return view('admin.settings.payments', [
            'settings'              => $settings,
            'feePaidByLabels'       => $feePaidByLabels,
            'processors'            => $processors,
            'assignableProcessors'  => $processors,
            'resolvedMethods'       => $resolvedMethods,
            'tab'                   => $tab,
        ]);
    }

    public function updatePaymentChoices(Request $request): RedirectResponse
    {
        PlatformPaymentProvider::syncFromConfig();

        $processorSlugs = PlatformPaymentProvider::query()->pluck('slug')->all();
        $request->validate([
            'section' => 'required|in:methods',
            'methods' => 'required|array',
        ]);

        $settings = PlatformSetting::current();
        $map = [];

        foreach (collect(config('payment_methods.methods', []))->pluck('slug') as $slug) {
            $row = $request->input("methods.{$slug}", []);
            $enabled = ! empty($row['enabled']);
            $provider = $row['provider_slug'] ?? null;
            $defaults = PlatformSetting::defaultPaymentMethodSettings();

            $map[$slug] = [
                'enabled'       => $enabled,
                'provider_slug' => $provider && in_array($provider, $processorSlugs, true)
                    ? $provider
                    : ($defaults[$slug]['provider_slug'] ?? 'stripe'),
            ];
        }

        $settings->payment_method_settings = $map;
        $settings->save();
        $settings->syncLegacyPaymentColumns();

        return redirect()
            ->route('admin.settings.payments', ['tab' => 'methods'])
            ->with('success', 'Payment methods saved.');
    }

    public function updatePaymentProvider(Request $request, PlatformPaymentProvider $provider): RedirectResponse
    {
        $validated = $request->validate([
            'is_enabled'  => 'sometimes|boolean',
            'setup_notes' => 'nullable|string|max:2000',
        ]);

        if ($request->has('is_enabled')) {
            $provider->is_enabled = $request->boolean('is_enabled');
        }
        if (array_key_exists('setup_notes', $validated)) {
            $provider->setup_notes = $validated['setup_notes'];
        }
        $provider->save();
        $provider->refreshConfiguredFromEnv();

        return redirect()
            ->route('admin.settings.payments', ['tab' => 'processors'])
            ->with('success', "{$provider->name} updated.");
    }

    public function markets(): View
    {
        PlatformPaymentProvider::syncFromConfig();

        $markets = CountryMarket::query()
            ->orderBy('pricing_tier')
            ->orderBy('country_code')
            ->get();

        $processors = PlatformPaymentProvider::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $processorsBySlug = $processors->keyBy('slug');

        return view('admin.settings.markets', compact('markets', 'processors', 'processorsBySlug'));
    }

    public function updateMarket(Request $request, CountryMarket $market): RedirectResponse
    {
        $processorSlugs = PlatformPaymentProvider::query()->pluck('slug')->all();

        $validated = $request->validate([
            'rent_processor_slug'          => 'required|string|in:'.implode(',', $processorSlugs),
            'pricing_tier'                   => 'required|in:standard,emerging,frontier',
            'signup_fee'                     => 'required|numeric|min:0',
            'monthly_fee'                    => 'required|numeric|min:0',
            'maintenance_commission_percent' => 'required|numeric|min:0|max:100',
            'is_active'                      => 'sometimes|boolean',
        ]);

        $market->update([
            'rent_processor_slug'        => $validated['rent_processor_slug'],
            'pricing_tier'               => $validated['pricing_tier'],
            'signup_fee_minor_per_unit'  => $this->majorToMinor($validated['signup_fee']),
            'monthly_fee_minor_per_unit' => $this->majorToMinor($validated['monthly_fee']),
            'maintenance_commission_bps' => $this->percentToBps($validated['maintenance_commission_percent']),
            'is_active'                  => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('admin.settings.markets')
            ->with('success', "Market {$market->country_code} updated.");
    }

    public function applyDefaultsToMarkets(Request $request): RedirectResponse
    {
        $tier = $request->validate(['pricing_tier' => 'required|in:standard,emerging,frontier'])['pricing_tier'];
        $defaults = CountryMarket::defaultsFromPlatform();
        $multiplier = match ($tier) {
            'emerging' => 0.55,
            'frontier' => 0.35,
            default    => 1.0,
        };

        CountryMarket::query()
            ->where('pricing_tier', $tier)
            ->each(function (CountryMarket $market) use ($defaults, $multiplier) {
                $market->update([
                    'signup_fee_minor_per_unit'  => (int) round($defaults['signup_fee_minor_per_unit'] * $multiplier),
                    'monthly_fee_minor_per_unit' => (int) round($defaults['monthly_fee_minor_per_unit'] * $multiplier),
                    'maintenance_commission_bps' => $defaults['maintenance_commission_bps'],
                ]);
            });

        return redirect()
            ->route('admin.settings.markets')
            ->with('success', "Applied default multipliers to all {$tier} markets.");
    }

    private function majorToMinor(float|string $major): int
    {
        return (int) round((float) $major * 100);
    }

    private function percentToBps(float|string $percent): int
    {
        return (int) round((float) $percent * 100);
    }
}
