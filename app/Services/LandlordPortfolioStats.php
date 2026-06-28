<?php

namespace App\Services;

use App\Models\Lease;
use App\Models\Property;
use App\Models\User;
use Illuminate\Support\Collection;

class LandlordPortfolioStats
{
    /** Home dashboard metric boxes (landlord). */
    public static function dashboard(int|string $landlordId): array
    {
        $portfolio = self::properties($landlordId);
        $leases    = self::leases($landlordId);

        return array_merge($portfolio, [
            'active_leases'       => $leases['active'],
            'countries'           => Property::query()
                ->where('landlord_id', $landlordId)
                ->distinct()
                ->count('country_code'),
        ]);
    }

    public static function properties(int|string $landlordId, ?string $portfolio = null): array
    {
        $properties = Property::query()
            ->where('landlord_id', $landlordId)
            ->with(['leases' => fn ($q) => $q->where('status', 'active')])
            ->get();

        $single = $properties->where('occupancy_mode', 'single');
        $multi  = $properties->where('occupancy_mode', 'multi');

        [$totalSlots, $occupiedSlots] = self::slotCounts($properties);

        $inView = match ($portfolio) {
            'multi'  => $multi,
            'single' => $single,
            default  => $properties,
        };

        [$inViewSlots, $inViewOccupied] = self::slotCounts($inView);

        return [
            'total'            => $properties->count(),
            'single_unit'      => $single->count(),
            'multi_unit'       => $multi->count(),
            'occupied_slots'   => $occupiedSlots,
            'total_slots'      => $totalSlots,
            'vacant_slots'     => max(0, $totalSlots - $occupiedSlots),
            'occupancy_pct'    => $totalSlots > 0 ? (int) round(100 * $occupiedSlots / $totalSlots) : 0,
            'in_view'          => $inView->count(),
            'in_view_occupied' => $inViewOccupied,
            'in_view_slots'    => $inViewSlots,
            'in_view_pct'      => $inViewSlots > 0 ? (int) round(100 * $inViewOccupied / $inViewSlots) : 0,
            'rent_in_view'     => self::monthlyRentByCurrency($inView),
        ];
    }

    /** @return array<string, int> currency code => minor units */
    private static function monthlyRentByCurrency(Collection $properties): array
    {
        $totals = [];

        foreach ($properties as $property) {
            $minor = $property->displayMonthlyRentMinor();
            if (! $minor) {
                continue;
            }
            $code = strtoupper((string) $property->currency_code);
            $totals[$code] = ($totals[$code] ?? 0) + $minor;
        }

        ksort($totals);

        return $totals;
    }

    public static function leases(int|string $landlordId): array
    {
        $leases = Lease::query()
            ->whereHas('property', fn ($q) => $q->where('landlord_id', $landlordId))
            ->with(['mandates'])
            ->get();

        $active = $leases->where('status', 'active');
        $withActiveMandate = $active->filter(fn (Lease $l) => $l->mandates->contains('status', 'active'));

        return [
            'total'              => $leases->count(),
            'active'             => $active->count(),
            'draft'              => $leases->where('status', 'draft')->count(),
            'inactive'           => $leases->whereNotIn('status', ['active', 'draft'])->count(),
            'active_mandates'    => $withActiveMandate->count(),
            'pending_mandates'   => max(0, $active->count() - $withActiveMandate->count()),
            'properties_leased'  => $active->pluck('property_id')->unique()->count(),
        ];
    }

    public static function tenants(int|string $landlordId): array
    {
        $tenants = User::query()
            ->where('role', 'tenant')
            ->whereHas('leases.property', fn ($q) => $q->where('landlord_id', $landlordId))
            ->with(['leases' => fn ($q) => $q->with('mandates')->whereHas('property', fn ($p) => $p->where('landlord_id', $landlordId))])
            ->get();

        $activeLeaseTenants = 0;
        $mandateActive      = 0;
        $mandatePending     = 0;

        foreach ($tenants as $tenant) {
            $lease = $tenant->leases->where('status', 'active')->first()
                ?? $tenant->leases->first();
            if (! $lease || $lease->status !== 'active') {
                continue;
            }
            $activeLeaseTenants++;
            if ($lease->mandates->contains('status', 'active')) {
                $mandateActive++;
            } else {
                $mandatePending++;
            }
        }

        return [
            'total'            => $tenants->count(),
            'active_lease'     => $activeLeaseTenants,
            'mandate_active'   => $mandateActive,
            'mandate_pending'  => $mandatePending,
            'inactive'         => max(0, $tenants->count() - $activeLeaseTenants),
        ];
    }

    /** @return array{0: int, 1: int} [totalSlots, occupiedSlots] */
    private static function slotCounts(Collection $properties): array
    {
        $totalSlots    = 0;
        $occupiedSlots = 0;

        foreach ($properties as $property) {
            $occupied = $property->leases->count();
            $occupiedSlots += $occupied;

            if ($property->isMultiUnit()) {
                $cap = (int) ($property->unit_capacity ?? 0);
                $totalSlots += $cap > 0 ? $cap : max($occupied, 1);
            } else {
                $totalSlots += 1;
            }
        }

        return [$totalSlots, $occupiedSlots];
    }
}
