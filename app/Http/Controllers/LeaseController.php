<?php
namespace App\Http\Controllers;
use App\Models\{Lease, Property, User};
use App\Services\LandlordPortfolioStats;
use App\Support\CurrencyDisplay;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LeaseController extends Controller
{
    public function index()
    {
        $leases = Lease::whereHas('property', fn ($q) => $q->where('landlord_id', Auth::id()))
            ->with(['property', 'tenant', 'mandates'])
            ->latest()
            ->get();

        $stats = LandlordPortfolioStats::leases(Auth::id());

        return view('dashboard.leases.index', compact('leases', 'stats'));
    }

    public function create(Property $property, Request $request)
    {
        $this->authorize('view', $property);

        if ($property->isAtLicensedUnitCapacity()) {
            return redirect()
                ->route('properties.show', $property)
                ->with('error', $property->isMultiUnit()
                    ? 'All licensed unit slots are in use. Increase capacity on the property (or end a lease) before adding another.'
                    : 'This single-unit property already has an active lease.');
        }

        $unitSeq = null;
        $prefillLabel = null;
        if ($property->isMultiUnit() && $request->filled('unit_seq')) {
            $seq = (int) $request->query('unit_seq');
            $cap = (int) ($property->unit_capacity ?? 0);
            if ($cap >= 1 && $seq >= 1 && $seq <= $cap) {
                $occupied = $property->leases()->where('status', 'active')->where('unit_seq', $seq)->exists();
                if (! $occupied) {
                    $unitSeq = $seq;
                    $meta = $property->unit_slots_meta[(string) $seq] ?? [];
                    $prefillLabel = $meta['label'] ?? null;
                }
            }
        }

        $defaultRentAmount = null;
        if ($property->rent_minor_units) {
            $decimals = CurrencyDisplay::decimalPlaces($property->currency_code);
            $defaultRentAmount = round($property->rent_minor_units / 100, $decimals);
        }

        return view('dashboard.leases.create', compact('property', 'unitSeq', 'prefillLabel', 'defaultRentAmount'));
    }

    public function store(Request $request, Property $property)
    {
        $this->authorize('view', $property);

        $validated = $request->validate([
            'tenant_email'     => 'required|email',
            'unit_label'       => $property->isMultiUnit()
                ? 'required|string|max:64'
                : 'nullable|string|max:64',
            'unit_seq'           => 'nullable|integer|min:1|max:999',
            'rent_amount'      => 'required|numeric|min:1',
            'due_day'          => 'required|integer|min:1|max:28',
            'start_date'       => 'required|date',
            'end_date'         => 'nullable|date|after:start_date',
            'deposit_amount'   => 'nullable|numeric|min:0',
            'grace_period_days'=> 'nullable|integer|min:0|max:30',
            'late_fee_amount'  => 'nullable|numeric|min:0',
            'use_business_entity' => 'nullable|boolean',
        ]);

        return DB::transaction(function () use ($validated, $property) {
            $property->refresh();

            if ($property->isMultiUnit() && $property->unit_capacity === null) {
                return back()
                    ->withErrors(['unit_label' => 'Set licensed unit capacity on the property before adding leases.'])
                    ->withInput();
            }

            if ($property->isAtLicensedUnitCapacity()) {
                return back()
                    ->withErrors(['tenant_email' => $property->isMultiUnit()
                        ? 'Licensed unit capacity reached for this property.'
                        : 'This single-unit property already has an active lease.'])
                    ->withInput();
            }

            $tenant = User::firstOrCreate(
                ['email' => $validated['tenant_email']],
                ['first_name' => 'Tenant', 'last_name' => '', 'role' => 'tenant', 'password' => bcrypt(Str::random(32))]
            );

            if ($property->isMultiUnit()) {
                $cap = (int) ($property->unit_capacity ?? 0);
                $rawSeq = $validated['unit_seq'] ?? null;
                if ($rawSeq !== null && $rawSeq !== '') {
                    $unitSeq = (int) $rawSeq;
                    if ($cap < 1 || $unitSeq < 1 || $unitSeq > $cap) {
                        return back()
                            ->withErrors(['unit_seq' => 'Choose a valid unit slot for this building.'])
                            ->withInput();
                    }
                    if ($property->leases()->where('status', 'active')->where('unit_seq', $unitSeq)->exists()) {
                        return back()
                            ->withErrors(['unit_seq' => 'That unit slot already has an active lease.'])
                            ->withInput();
                    }
                } else {
                    $unitSeq = $property->nextUnitSeq();
                    if ($cap >= 1 && $unitSeq > $cap) {
                        return back()
                            ->withErrors(['tenant_email' => 'No free licensed unit slots. Increase capacity or pick a specific vacant unit.'])
                            ->withInput();
                    }
                }
                $unitLabel = $validated['unit_label'];
            } else {
                $unitSeq = 0;
                $unitLabel = null;
            }

            $landlord = Auth::user();
            $useBusiness = null;
            if ($landlord->businessEntityReadyForLease()) {
                $useBusiness = $request->boolean('use_business_entity');
            }

            $lease = $property->leases()->create([
                'tenant_id'          => $tenant->id,
                'unit_seq'           => $unitSeq,
                'unit_label'         => $unitLabel,
                'rent_minor_units'   => (int)($validated['rent_amount'] * 100),
                'currency_code'      => $property->currency_code,
                'due_day'            => $validated['due_day'],
                'grace_period_days'  => $validated['grace_period_days'] ?? 5,
                'late_fee_minor_units' => isset($validated['late_fee_amount'])
                    ? (int) round((float) $validated['late_fee_amount'] * 100)
                    : null,
                'start_date'         => $validated['start_date'],
                'end_date'           => $validated['end_date'] ?? null,
                'deposit_minor_units'=> isset($validated['deposit_amount']) ? (int)($validated['deposit_amount'] * 100) : null,
                'status'             => 'active',
                'use_business_entity'=> $useBusiness,
            ]);

            if (! $property->isMultiUnit()) {
                $property->syncRentScheduleFromActiveLeases();
            }
            $property->syncStatusFromLeases();

            return redirect()
                ->route('properties.show', $property)
                ->with('success', $property->isMultiUnit() ? 'Unit lease created.' : 'Lease created.');
        });
    }

    public function show(Lease $lease)
    {
        $this->authorize('view', $lease);
        $lease->load(['property','tenant','payments','mandates']);
        return view('dashboard.leases.show', compact('lease'));
    }
}
