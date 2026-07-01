<?php

namespace Database\Seeders;

use App\Models\Lease;
use App\Models\Payment;
use App\Models\PaymentMandate;
use App\Models\Property;
use App\Models\RepatriationLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Enriches demo landlords' 9 cross-border properties with FX ledger data:
 * backfilled rent payments (snapshotted FX) and manual repatriation logs.
 *
 * Targets demo@renpresso.com and demo@rentersmaxx.com. Clones the cross-border
 * portfolio from whichever account already has it when the other is missing it.
 *
 * Safe to run multiple times — skips months that already have a successful payment
 * and repatriations keyed by demo notes.
 *
 * Usage:
 *   php artisan db:seed --class=FxLedgerDemoSeeder --force
 */
class FxLedgerDemoSeeder extends Seeder
{
    /** @var list<string> */
    private const DEMO_LANDLORDS = [
        'demo@renpresso.com',
        'demo@rentersmaxx.com',
    ];

    /** @var list<string> */
    private const CROSS_BORDER_NAMES = [
        'Rue de Rivoli Apartment',
        'Bandra West Flat',
        'Shoreditch Studio',
        'Lekki Phase 1',
        'Kemang Villa',
        'Harbour View Apartments',
        'Bloomsbury Court',
        'Jordaan Residences',
        'Victoria Island Towers',
    ];

    public function run(): void
    {
        $totalPayments = 0;
        $totalRepatriations = 0;

        foreach (self::DEMO_LANDLORDS as $email) {
            $landlord = User::query()->where('email', $email)->first();
            if (! $landlord) {
                $this->command?->warn("Skipping {$email} — user not found.");

                continue;
            }

            $cloned = $this->ensureCrossBorderPortfolio($landlord);
            if ($cloned > 0) {
                $this->command?->info("{$email}: cloned {$cloned} cross-border propert".($cloned === 1 ? 'y' : 'ies').'.');
            }

            [$paymentsAdded, $repatriationsAdded] = $this->seedLandlord($landlord);
            $totalPayments += $paymentsAdded;
            $totalRepatriations += $repatriationsAdded;

            if ($paymentsAdded === 0 && $repatriationsAdded === 0 && $cloned === 0) {
                $cbCount = $this->crossBorderPropertyCount($landlord);
                if ($cbCount === 0) {
                    $this->command?->warn("{$email}: no cross-border properties — run DatabaseSeeder + MultiUnitDemoSeeder first.");
                } else {
                    $this->command?->line("{$email}: already up to date ({$cbCount} cross-border propert".($cbCount === 1 ? 'y' : 'ies').').');
                }
            } elseif ($paymentsAdded > 0 || $repatriationsAdded > 0) {
                $this->command?->info("{$email}: +{$paymentsAdded} payment(s), +{$repatriationsAdded} repatriation(s).");
            }
        }

        $this->command?->info("FxLedgerDemoSeeder done: {$totalPayments} payment(s) added, {$totalRepatriations} repatriation(s) added.");
    }

    /** @return array{0: int, 1: int} */
    private function seedLandlord(User $landlord): array
    {
        $homeCurrency = strtoupper($landlord->home_currency ?? 'USD');

        $properties = $landlord->properties()
            ->whereIn('name', self::CROSS_BORDER_NAMES)
            ->with(['leases' => fn ($q) => $q->where('status', 'active')])
            ->get()
            ->keyBy('name');

        $paymentsAdded = 0;
        $historyStart = now()->startOfYear()->subYear();

        foreach (self::CROSS_BORDER_NAMES as $name) {
            $property = $properties->get($name);
            if (! $property) {
                continue;
            }

            foreach ($property->leases as $lease) {
                $paymentsAdded += $this->backfillLeasePayments($lease, $property, $homeCurrency, $historyStart);
            }
        }

        $repatriationsAdded = $this->seedRepatriations($landlord, $properties, $homeCurrency);

        return [$paymentsAdded, $repatriationsAdded];
    }

    private function crossBorderPropertyCount(User $landlord): int
    {
        $homeCountry = strtoupper($landlord->home_country ?? 'US');
        $homeCurrency = strtoupper($landlord->home_currency ?? 'USD');

        return $landlord->properties()
            ->whereIn('name', self::CROSS_BORDER_NAMES)
            ->get()
            ->filter(fn (Property $p) => strtoupper($p->country_code ?? '') !== $homeCountry
                || strtoupper($p->currency_code ?? '') !== $homeCurrency)
            ->count();
    }

    /** Clone missing cross-border properties from another demo landlord that has them. */
    private function ensureCrossBorderPortfolio(User $target): int
    {
        $missing = array_values(array_diff(
            self::CROSS_BORDER_NAMES,
            $target->properties()->whereIn('name', self::CROSS_BORDER_NAMES)->pluck('name')->all()
        ));

        if ($missing === []) {
            return 0;
        }

        $source = collect(self::DEMO_LANDLORDS)
            ->map(fn (string $email) => User::query()->where('email', $email)->first())
            ->filter(fn (?User $user) => $user && $user->id !== $target->id)
            ->first(fn (User $user) => $user->properties()->whereIn('name', self::CROSS_BORDER_NAMES)->count() >= count(self::CROSS_BORDER_NAMES));

        if (! $source) {
            $this->command?->warn("Cannot clone portfolio for {$target->email} — no source landlord with all 9 properties.");

            return 0;
        }

        $sourceProperties = $source->properties()
            ->whereIn('name', $missing)
            ->with(['leases' => fn ($q) => $q->where('status', 'active')])
            ->get()
            ->keyBy('name');

        $created = 0;

        foreach ($missing as $name) {
            $src = $sourceProperties->get($name);
            if (! $src) {
                continue;
            }

            $attrs = collect($src->getAttributes())
                ->except(['id', 'landlord_id', 'created_at', 'updated_at', 'deleted_at'])
                ->all();

            $property = $target->properties()->create($attrs);

            foreach ($src->leases as $srcLease) {
                $lease = $property->leases()->create([
                    'tenant_id'           => $srcLease->tenant_id,
                    'unit_seq'            => $srcLease->unit_seq,
                    'unit_label'          => $srcLease->unit_label,
                    'rent_minor_units'    => $srcLease->rent_minor_units,
                    'currency_code'       => $srcLease->currency_code,
                    'due_day'             => $srcLease->due_day,
                    'grace_period_days'   => $srcLease->grace_period_days,
                    'late_fee_minor_units'=> $srcLease->late_fee_minor_units,
                    'frequency'           => $srcLease->frequency,
                    'deposit_minor_units' => $srcLease->deposit_minor_units,
                    'start_date'          => $srcLease->start_date,
                    'end_date'            => $srcLease->end_date,
                    'status'              => $srcLease->status,
                ]);

                if ($srcLease->activated_at) {
                    $lease->forceFill(['activated_at' => $srcLease->activated_at])->save();
                }

                $srcMandate = $srcLease->mandates()->where('status', 'active')->first();
                $lease->mandates()->create([
                    'processor_slug'       => $srcMandate->processor_slug ?? $property->processor_slug,
                    'processor_mandate_id' => 'mandate_fxclone_'.Str::random(12),
                    'status'               => 'active',
                    'payment_method_type'  => $srcMandate->payment_method_type ?? config('countries.'.$property->country_code.'.method', 'bank'),
                    'authorised_at'        => $srcMandate->authorised_at ?? $srcLease->start_date ?? now(),
                ]);
            }

            if (($property->occupancy_mode ?? 'single') === 'single') {
                $property->syncRentScheduleFromActiveLeases();
            }
            $property->syncStatusFromLeases();
            $created++;
        }

        return $created;
    }

    private function backfillLeasePayments(Lease $lease, Property $property, string $homeCurrency, Carbon $historyStart): int
    {
        $mandate = $this->resolveMandate($lease, $property);
        $leaseStart = $lease->start_date?->copy()->startOfMonth() ?? $historyStart->copy();
        $cursor = $leaseStart->greaterThan($historyStart) ? $leaseStart : $historyStart->copy();
        $end = now()->startOfMonth();
        $added = 0;

        while ($cursor->lte($end)) {
            $dueDay = min(max((int) $lease->due_day, 1), 28);
            $due = $cursor->copy()->day($dueDay)->startOfDay();

            $ref = 'fxdemo_'.str_replace('-', '', $lease->id).'_'.$due->format('Ym');

            $exists = $lease->payments()
                ->where('status', 'success')
                ->whereYear('due_date', $due->year)
                ->whereMonth('due_date', $due->month)
                ->exists()
                || Payment::query()->where('processor_ref', $ref)->exists();

            if (! $exists && $lease->start_date->lte($due)) {
                $fx = $this->fxSnapshot($property->currency_code, $due);
                $rent = (int) $lease->rent_minor_units;
                $home = (int) round($rent * ($fx / 1_000_000));

                $lease->payments()->create([
                    'mandate_id'              => $mandate->id,
                    'processor_ref'           => $ref,
                    'amount_minor_units'      => $rent,
                    'currency_code'           => $property->currency_code,
                    'fx_rate_snapshot'        => $fx,
                    'home_currency_code'      => $homeCurrency,
                    'home_amount_minor_units' => $home,
                    'status'                  => 'success',
                    'due_date'                => $due,
                    'collected_at'            => $due->copy()->addDay(),
                    'retry_count'             => 0,
                ]);
                $added++;
            }

            $cursor->addMonth();
        }

        return $added;
    }

    private function resolveMandate(Lease $lease, Property $property): PaymentMandate
    {
        $existing = $lease->mandates()->where('status', 'active')->first();
        if ($existing) {
            return $existing;
        }

        return $lease->mandates()->create([
            'processor_slug'       => $property->processor_slug,
            'processor_mandate_id' => 'mandate_fxdemo_'.Str::random(10),
            'status'               => 'active',
            'payment_method_type'  => config('countries.'.$property->country_code.'.method', 'bank'),
            'authorised_at'        => $lease->start_date ?? now(),
        ]);
    }

    /** Base FX × 1,000,000 with a small month-to-month drift (snapshotted, never recalculated). */
    private function fxSnapshot(string $currencyCode, Carbon $when): int
    {
        $base = match (strtoupper($currencyCode)) {
            'EUR' => 1_080_000,
            'GBP' => 1_260_000,
            'INR' => 12_000,
            'NGN' => 650,
            'IDR' => 65,
            'SGD' => 740_000,
            'CHF' => 1_120_000,
            'AUD' => 660_000,
            default => 1_000_000,
        };

        $drift = (($when->month + $when->year) % 9 - 4) * 0.006; // ±2.4%

        return max(1, (int) round($base * (1 + $drift)));
    }

    private function seedRepatriations(User $landlord, $properties, string $homeCurrency): int
    {
        $plans = [
            [
                'property' => 'Rue de Rivoli Apartment',
                'date'     => now()->subMonths(2)->day(18),
                'local'    => 127500, // €1,275 partial transfer
                'home'     => 1377,
                'notes'    => 'FX demo: Wise transfer EUR→USD — ref WISE-88421',
            ],
            [
                'property' => 'Bandra West Flat',
                'date'     => now()->subMonths(1)->day(8),
                'local'    => 7500000,
                'home'     => 90000,
                'notes'    => 'FX demo: HDFC wire to Chase — INR batch March',
            ],
            [
                'property' => 'Shoreditch Studio',
                'date'     => now()->subMonths(3)->day(22),
                'local'    => 220000,
                'home'     => 277200,
                'notes'    => 'FX demo: Revolut GBP→USD',
            ],
            [
                'property' => 'Lekki Phase 1',
                'date'     => now()->subMonths(1)->day(3),
                'local'    => 60000000,
                'home'     => 39000,
                'notes'    => 'FX demo: Flutterwave payout to US account',
            ],
            [
                'property' => 'Kemang Villa',
                'date'     => now()->subMonths(4)->day(12),
                'local'    => 2500000000,
                'home'     => 162500,
                'notes'    => 'FX demo: Xendit settlement — IDR→USD',
            ],
            [
                'property' => 'Harbour View Apartments',
                'date'     => now()->subMonths(2)->day(5),
                'local'    => 1315000, // combined units partial
                'home'     => 97310,
                'notes'    => 'FX demo: DBS outward remittance SGD→USD',
            ],
            [
                'property' => 'Bloomsbury Court',
                'date'     => now()->subMonths(5)->day(28),
                'local'    => 425000,
                'home'     => 535500,
                'notes'    => 'FX demo: Barclays international transfer',
            ],
            [
                'property' => 'Jordaan Residences',
                'date'     => now()->subMonth()->day(15),
                'local'    => 498000,
                'home'     => 537840,
                'notes'    => 'FX demo: ING SEPA + FX desk — Q1 sweep',
            ],
            [
                'property' => 'Victoria Island Towers',
                'date'     => now()->subMonths(2)->day(25),
                'local'    => 230000000,
                'home'     => 149500,
                'notes'    => 'FX demo: GTBank diaspora wire — Feb collections',
            ],
            [
                'property' => 'Rue de Rivoli Apartment',
                'date'     => now()->startOfYear()->addDays(20),
                'local'    => 150000,
                'home'     => 162000,
                'notes'    => 'FX demo: January rent repatriation',
            ],
            [
                'property' => 'Bandra West Flat',
                'date'     => now()->subMonths(6)->day(10),
                'local'    => 15000000,
                'home'     => 180000,
                'notes'    => 'FX demo: Mid-year INR sweep to US',
            ],
            [
                'property' => 'Kemang Villa',
                'date'     => now()->subMonth()->day(20),
                'local'    => 1250000000,
                'home'     => 81250,
                'notes'    => 'FX demo: Partial Jakarta rent — May payout',
            ],
        ];

        $added = 0;

        foreach ($plans as $plan) {
            $property = $properties->get($plan['property']);
            if (! $property) {
                continue;
            }

            if (RepatriationLog::query()
                ->where('landlord_id', $landlord->id)
                ->where('property_id', $property->id)
                ->where('notes', $plan['notes'])
                ->exists()) {
                continue;
            }

            $localMinor = (int) $plan['local'];
            $homeMinor = (int) $plan['home'];
            $fx = $localMinor > 0
                ? (int) round(($homeMinor / $localMinor) * 1_000_000)
                : 1_000_000;

            RepatriationLog::create([
                'landlord_id'             => $landlord->id,
                'property_id'             => $property->id,
                'amount_minor_units'      => $localMinor,
                'currency_code'           => $property->currency_code,
                'home_currency_code'      => $homeCurrency,
                'home_amount_minor_units' => $homeMinor,
                'fx_rate_snapshot'        => $fx,
                'repatriated_on'          => $plan['date']->toDateString(),
                'notes'                   => $plan['notes'],
            ]);
            $added++;
        }

        return $added;
    }
}
