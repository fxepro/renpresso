<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Property;
use App\Models\RepatriationLog;
use App\Services\LedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FxLedgerController extends Controller
{
    public function __construct(private readonly LedgerService $ledger) {}

    public function index(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->isLandlord(), 403);

        $year = max(2000, min(2100, (int) $request->query('year', now()->year)));
        $month = max(1, min(12, (int) $request->query('month', now()->month)));

        $homeCurrency = strtoupper($user->home_currency ?? 'USD');
        $homeCountry = strtoupper($user->home_country ?? 'US');

        $properties = $user->properties()->orderBy('name')->get();

        $crossBorderProperties = $properties->filter(function ($property) use ($homeCountry, $homeCurrency) {
            $propertyCountry = strtoupper($property->country_code ?? '');
            $propertyCurrency = strtoupper($property->currency_code ?? '');

            return $propertyCountry !== $homeCountry
                || ($propertyCurrency !== '' && $propertyCurrency !== $homeCurrency);
        });

        $monthlySummary = $this->ledger->monthlySummary($user->id, $year, $month);
        $monthTotal = $this->ledger->monthlyHomeTotal($user->id, $year, $month);
        $yearTotal = $this->ledger->yearlyHomeTotal($user->id, $year);

        $repatriatedYear = (int) RepatriationLog::query()
            ->where('landlord_id', $user->id)
            ->whereYear('repatriated_on', $year)
            ->sum('home_amount_minor_units');

        $repatriations = RepatriationLog::query()
            ->where('landlord_id', $user->id)
            ->with('property')
            ->orderByDesc('repatriated_on')
            ->orderByDesc('created_at')
            ->paginate(10, ['*'], 'repatriation_page')
            ->withQueryString();

        $fxPayments = Payment::query()
            ->whereHas('lease.property', fn ($q) => $q->where('landlord_id', $user->id))
            ->where('status', 'success')
            ->whereYear('collected_at', $year)
            ->whereMonth('collected_at', $month)
            ->with(['lease.property', 'lease.tenant'])
            ->orderByDesc('collected_at')
            ->paginate(20, ['*'], 'payment_page')
            ->withQueryString();

        return view('dashboard.fx-ledger.index', compact(
            'user',
            'year',
            'month',
            'homeCurrency',
            'homeCountry',
            'properties',
            'crossBorderProperties',
            'monthlySummary',
            'monthTotal',
            'yearTotal',
            'repatriatedYear',
            'repatriations',
            'fxPayments',
        ));
    }

    public function storeRepatriation(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->isLandlord(), 403);

        $validated = $request->validate([
            'property_id'    => 'required|uuid|exists:properties,id',
            'amount'         => 'required|numeric|min:0.01',
            'home_amount'    => 'required|numeric|min:0.01',
            'repatriated_on' => 'required|date|before_or_equal:today',
            'notes'          => 'nullable|string|max:1000',
        ]);

        $property = Property::query()->findOrFail($validated['property_id']);
        $this->authorize('update', $property);

        $currency = strtoupper($property->currency_code);
        $homeCurrency = strtoupper($user->home_currency ?? 'USD');
        $amountMinor = (int) round($validated['amount'] * 100);
        $homeMinor = (int) round($validated['home_amount'] * 100);

        $fxRate = $amountMinor > 0
            ? (int) round(($homeMinor / $amountMinor) * 1_000_000)
            : 1_000_000;

        RepatriationLog::create([
            'landlord_id'             => $user->id,
            'property_id'             => $property->id,
            'amount_minor_units'      => $amountMinor,
            'currency_code'           => $currency,
            'home_currency_code'      => $homeCurrency,
            'home_amount_minor_units' => $homeMinor,
            'fx_rate_snapshot'        => $fxRate,
            'repatriated_on'          => $validated['repatriated_on'],
            'notes'                   => $validated['notes'] ?? null,
        ]);

        return redirect()
            ->route('fx-ledger.index', $request->only(['year', 'month']))
            ->with('success', 'Repatriation logged for '.$property->name.'.');
    }
}
