<?php
namespace App\Http\Controllers;
use App\Models\Property;
use App\Payment\ProcessorFactory;
use App\Services\LandlordPortfolioStats;
use App\Support\CurrencyDisplay;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PropertyController extends Controller
{
    public function index(Request $request)
    {
        $portfolio = $request->query('portfolio', 'single');
        if (! in_array($portfolio, ['single', 'multi'], true)) {
            $portfolio = 'single';
        }

        $query = Auth::user()->properties()
            ->with([
                'media',
                'leases' => fn ($q) => $q->where('status', 'active')->with(['tenant', 'subLeases.subletter'])->orderBy('unit_seq')->orderBy('unit_label'),
                'subLeases' => fn ($q) => $q->with(['subletter', 'parentLease.tenant'])->orderByDesc('created_at'),
                'applications' => fn ($q) => $q->with('backgroundChecks')->orderByDesc('created_at'),
            ]);

        $query->where('occupancy_mode', $portfolio === 'multi' ? 'multi' : 'single');

        $properties = $query->latest()->get();

        foreach ($properties as $property) {
            $property->syncStatusFromLeases();
            if ($property->isMultiUnit()) {
                if ($property->activeLeasesRentMinorTotal() > 0
                    && (int) ($property->rent_minor_units ?? 0) !== $property->activeLeasesRentMinorTotal()) {
                    $property->syncRentScheduleFromActiveLeases();
                }
                continue;
            }
            $lease = $property->leases->where('status', 'active')->first();
            if ($lease && (int) ($property->rent_minor_units ?? 0) !== (int) $lease->rent_minor_units) {
                $property->syncRentScheduleFromActiveLeases();
            }
        }

        $stats = LandlordPortfolioStats::properties(Auth::id(), $portfolio);

        // Total monthly rent across ALL active leases (both tabs), converted to landlord home currency.
        $homeCurrency = strtoupper(Auth::user()->home_currency ?? 'USD');
        $totalHomeRentMinor = (int) DB::table('payments as p')
            ->join('leases as l', 'p.lease_id', '=', 'l.id')
            ->join('properties as pr', 'l.property_id', '=', 'pr.id')
            ->where('pr.landlord_id', Auth::id())
            ->where('l.status', 'active')
            ->whereIn(DB::raw('(p.lease_id, p.due_date)'), function ($sub) {
                $sub->select('lease_id', DB::raw('MAX(due_date)'))
                    ->from('payments')
                    ->groupBy('lease_id');
            })
            ->sum('p.home_amount_minor_units');

        return view('dashboard.properties.index', compact('properties', 'portfolio', 'stats', 'homeCurrency', 'totalHomeRentMinor'));
    }

    public function create()
    {
        $defaultMultiUnitCapacity = Auth::user()->default_multi_unit_capacity;

        return view('dashboard.properties.create', compact('defaultMultiUnitCapacity'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                 => 'required|string|max:255',
            'country_code'         => 'required|string|size:2',
            'address_line1'        => 'required|string|max:255',
            'city'                 => 'required|string|max:100',
            'type'                 => 'required|in:apartment,house,commercial,other',
            'occupancy_mode'       => 'required|in:single,multi',
            'unit_capacity'        => 'nullable|required_if:occupancy_mode,multi|integer|min:1|max:999',
            'bedrooms'             => 'nullable|integer|min:0|max:99',
            'postal_code'          => 'nullable|string|max:20',
            'rental_mode'          => 'required|in:long_term,short_term',
            'listing_visibility'   => 'required|in:public,private',
            'sublet_allowed'                    => 'sometimes|boolean',
            'sublet_bg_check_required'          => 'sometimes|boolean',
            'sublet_landlord_approval_required' => 'sometimes|boolean',
        ]);

        if (!ProcessorFactory::supports($validated['country_code'])) {
            return back()->withErrors(['country_code' => 'Country not supported yet.'])->withInput();
        }

        $country = config('countries.'.$validated['country_code']);

        if ($validated['occupancy_mode'] === 'single') {
            $validated['unit_capacity'] = null;
        }

        Auth::user()->properties()->create(array_merge($validated, [
            'currency_code'  => $country['currency'],
            'processor_slug' => $country['processor'],
            'sublet_allowed'                    => $request->boolean('sublet_allowed'),
            'sublet_bg_check_required'          => $request->boolean('sublet_bg_check_required', true),
            'sublet_landlord_approval_required' => $request->boolean('sublet_landlord_approval_required', true),
        ]));

        return redirect()
            ->route('properties.index', ['portfolio' => $validated['occupancy_mode'] === 'multi' ? 'multi' : 'single'])
            ->with('success', 'Property added.');
    }

    public function show(Property $property)
    {
        $property = $this->loadPropertyForShow($property);

        if ($property->isMultiUnit()) {
            return view('dashboard.properties.show', [
                'property'          => $property,
                'unitSeq'           => null,
                'unitSlotsPayload'  => $this->buildUnitSlotsPayload($property),
                'activeSubLeases'   => collect(),
                'flags'             => $this->propertyFlags(),
            ]);
        }

        $lease = $property->leases->where('status', 'active')->first();
        if ($lease && (int) ($property->rent_minor_units ?? 0) !== (int) $lease->rent_minor_units) {
            $property->syncRentScheduleFromActiveLeases();
        }

        $activeLeases = $property->leases
            ->where('status', 'active')
            ->sortBy(fn ($l) => sprintf('%06d|%s', (int) $l->unit_seq, strtolower($l->unit_label ?? '')));

        return view('dashboard.properties.show', [
            'property'        => $property,
            'unitSeq'         => null,
            'unitSlotsPayload'=> null,
            'activeLeases'    => $activeLeases,
            'activeSubLeases' => $this->activeSubLeasesForProperty($property),
            'flags'           => $this->propertyFlags(),
        ]);
    }

    public function showUnit(Property $property, int $unit_seq)
    {
        $this->authorize('view', $property);
        abort_unless($property->isMultiUnit(), 404);

        $capacity = (int) $property->unit_capacity;
        abort_unless($capacity > 0 && $unit_seq >= 1 && $unit_seq <= $capacity, 404);

        return redirect()->route('properties.show', ['property' => $property, 'unit' => $unit_seq]);
    }

    private function loadPropertyForShow(Property $property): Property
    {
        $this->authorize('view', $property);
        $property->load([
            'applications' => fn ($q) => $q->with('backgroundChecks')->orderByDesc('created_at'),
            'media',
            'leases' => fn ($q) => $q->with(['tenant', 'payments', 'mandates', 'subLeases.subletter'])->orderBy('unit_seq')->orderBy('unit_label'),
            'subLeases' => fn ($q) => $q->with(['subletter', 'parentLease.tenant'])->orderByDesc('created_at'),
        ]);
        $property->syncStatusFromLeases();

        return $property;
    }

    /** @return list<array<string, mixed>> */
    private function buildUnitSlotsPayload(Property $property): array
    {
        if (! $property->isMultiUnit() || ! $property->unit_capacity) {
            return [];
        }

        $payload = [];
        $meta = $property->unit_slots_meta ?? [];
        $leasesBySeq = $property->leases->where('status', 'active')->keyBy(fn ($l) => (int) $l->unit_seq);

        for ($i = 1; $i <= (int) $property->unit_capacity; $i++) {
            $lease = $leasesBySeq->get($i);
            $slotMeta = $meta[(string) $i] ?? [];
            $displayLabel = $lease
                ? ($lease->unit_label ?: ('Unit '.$i))
                : ($slotMeta['label'] ?? ('Unit '.$i));

            $bedroomsRaw = $slotMeta['bedrooms'] ?? null;
            $bedroomsLabel = $bedroomsRaw !== null
                ? (((int) $bedroomsRaw === 0) ? 'Studio' : (int) $bedroomsRaw.' bed')
                : '—';

            $payload[] = [
                'seq'           => $i,
                'displayLabel'  => $displayLabel,
                'leased'        => $lease !== null,
                'lease'         => $lease,
                'bedrooms'      => $bedroomsLabel,
            ];
        }

        return $payload;
    }

    private function activeSubLeasesForProperty(Property $property)
    {
        return $property->subLeases()
            ->whereIn('sub_leases.status', ['active', 'pending_landlord_approval'])
            ->with(['subletter', 'parentLease.tenant'])
            ->orderBy('sub_leases.created_at')
            ->get();
    }

    /** @return array<string, string> */
    private function propertyFlags(): array
    {
        return ['FR'=>'🇫🇷','GB'=>'🇬🇧','US'=>'🇺🇸','IN'=>'🇮🇳','DE'=>'🇩🇪','AU'=>'🇦🇺','CA'=>'🇨🇦','NG'=>'🇳🇬'];
    }

    public function update(Request $request, Property $property)
    {
        $this->authorize('update', $property);
        $validated = $request->validate([
            'name'                 => 'required|string|max:255',
            'country_code'         => 'required|string|size:2',
            'address_line1'        => 'required|string|max:255',
            'city'                 => 'required|string|max:100',
            'type'                 => 'required|in:apartment,house,commercial,other',
            'occupancy_mode'       => 'required|in:single,multi',
            'unit_capacity'        => 'nullable|required_if:occupancy_mode,multi|integer|min:1|max:999',
            'bedrooms'             => 'nullable|integer|min:0|max:99',
            'postal_code'          => 'nullable|string|max:20',
            'rental_mode'          => 'required|in:long_term,short_term',
            'listing_visibility'   => 'required|in:public,private',
            'sublet_allowed'                    => 'sometimes|boolean',
            'sublet_bg_check_required'          => 'sometimes|boolean',
            'sublet_landlord_approval_required' => 'sometimes|boolean',
        ]);
        if ($validated['occupancy_mode'] === 'single') {
            $validated['unit_capacity'] = null;
        }

        $occupied = $property->leases()->where('status', 'active')->count();
        if (($validated['occupancy_mode'] ?? $property->occupancy_mode) === 'multi'
            && array_key_exists('unit_capacity', $validated)
            && $validated['unit_capacity'] !== null
            && (int) $validated['unit_capacity'] < $occupied) {
            return response()->json([
                'message'   => "Licensed unit capacity cannot be less than active leases ({$occupied} occupied).",
                'errors'    => ['unit_capacity' => ["Enter at least {$occupied} or terminate leases first."]],
                'success'   => false,
            ], 422);
        }

        $country = config('countries.'.$validated['country_code']);

        $property->update(array_merge($validated, [
            'currency_code'                     => $country['currency'] ?? $property->currency_code,
            'processor_slug'                    => $country['processor'] ?? $property->processor_slug,
            'sublet_allowed'                    => $request->boolean('sublet_allowed'),
            'sublet_bg_check_required'          => $request->boolean('sublet_bg_check_required', true),
            'sublet_landlord_approval_required' => $request->boolean('sublet_landlord_approval_required', true),
        ]));

        return response()->json(['success' => true]);
    }

    public function updateRent(Request $request, Property $property)
    {
        $this->authorize('update', $property);

        $validated = $request->validate([
            'base_rent_amount'       => 'nullable|numeric|min:0',
            'charge_lines'           => 'nullable|array',
            'charge_lines.*.label'   => 'required|string|max:120',
            'charge_lines.*.amount'  => 'nullable|numeric|min:0',
        ]);

        $baseMinor = isset($validated['base_rent_amount']) && $validated['base_rent_amount'] !== ''
            ? (int) round((float) $validated['base_rent_amount'] * 100)
            : 0;

        $lines = [];
        foreach ($validated['charge_lines'] ?? [] as $row) {
            $label = trim((string) ($row['label'] ?? ''));
            if ($label === '') {
                continue;
            }
            $key = strtolower(preg_replace('/[^a-z0-9]+/i', '_', $label) ?? 'charge');
            $key = trim($key, '_') ?: 'charge';
            $lines[] = [
                'key'                => $key,
                'label'              => $label,
                'amount_minor_units' => (int) round((float) ($row['amount'] ?? 0) * 100),
            ];
        }

        $property->base_rent_minor_units = $baseMinor > 0 ? $baseMinor : null;
        $property->rent_charge_lines = $lines !== [] ? $lines : null;
        $property->syncRentTotal();
        $property->save();
        $property->syncActiveLeasesFromRentSchedule();

        $displayCurrency = strtoupper($property->currency_code);

        return response()->json([
            'success'              => true,
            'display_currency'     => $displayCurrency,
            'rent_decimals'        => CurrencyDisplay::decimalPlaces($displayCurrency),
            'rent_amount_step'     => CurrencyDisplay::amountStep($displayCurrency),
            'base_rent_minor_units'=> $property->base_rent_minor_units,
            'rent_minor_units'     => $property->rent_minor_units,
            'rent_charge_lines'    => $property->normalizedRentChargeLines(),
            'total_rent_display'   => CurrencyDisplay::formatMinor($property->rent_minor_units, $displayCurrency),
            'base_rent_display'    => CurrencyDisplay::formatMinor($property->base_rent_minor_units, $displayCurrency),
        ]);
    }

    public function destroy(Property $property)
    {
        $this->authorize('update', $property);
        $property->delete();
        return redirect()->route('properties.index')->with('success', 'Property deleted.');
    }

}
