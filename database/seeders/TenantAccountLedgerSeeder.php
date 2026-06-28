<?php

namespace Database\Seeders;

use App\Models\Lease;
use App\Models\Payment;
use App\Models\TenantLedgerEntry;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TenantAccountLedgerSeeder extends Seeder
{
    public function run(): void
    {
        Lease::query()
            ->where('status', 'active')
            ->whereNotNull('tenant_id')
            ->with(['tenant', 'property', 'payments'])
            ->each(fn (Lease $lease) => $this->seedLease($lease));
    }

    private function seedLease(Lease $lease): void
    {
        TenantLedgerEntry::query()->where('lease_id', $lease->id)->delete();

        $lease->update(['ledger_starting_balance_minor_units' => 0]);

        $tenant = $lease->tenant;
        $rent = (int) $lease->rent_minor_units;
        $trash = max(200, (int) round($rent * 0.009));
        $waterBase = max(150, (int) round($rent * 0.012));
        $sewerBase = max(90, (int) round($rent * 0.007));
        $opening = max(5000, (int) round($rent * 0.05));

        $start = $lease->start_date
            ? Carbon::parse($lease->start_date)->startOfMonth()
            : now()->subMonths(6)->startOfMonth();

        $sort = 0;

        TenantLedgerEntry::create([
            'lease_id'             => $lease->id,
            'entry_date'           => $start->copy()->subDay(),
            'description'          => 'Allowance for Bad Debt - Opening Balance',
            'charge_minor_units'   => $opening,
            'payment_minor_units'  => 0,
            'category'             => 'opening_balance',
            'sort_order'           => $sort++,
        ]);

        $monthCursor = $start->copy();
        $end = now()->startOfMonth();

        while ($monthCursor->lte($end)) {
            $label = $monthCursor->format('F Y');
            $day = min(max((int) $lease->due_day, 1), 28);
            $chargeDate = $monthCursor->copy()->day(min($day, $monthCursor->daysInMonth));

            TenantLedgerEntry::create([
                'lease_id'            => $lease->id,
                'entry_date'          => $chargeDate,
                'description'         => "Rental Income - {$label}",
                'charge_minor_units'  => $rent,
                'payment_minor_units' => 0,
                'category'            => 'rent',
                'sort_order'          => $sort++,
            ]);

            TenantLedgerEntry::create([
                'lease_id'            => $lease->id,
                'entry_date'          => $chargeDate,
                'description'         => "Trash - {$label}",
                'charge_minor_units'  => $trash,
                'payment_minor_units' => 0,
                'category'            => 'utility',
                'sort_order'          => $sort++,
            ]);

            TenantLedgerEntry::create([
                'lease_id'            => $lease->id,
                'entry_date'          => $chargeDate,
                'description'         => "Water/Sewer - {$label} Water Base Charge",
                'charge_minor_units'  => $waterBase,
                'payment_minor_units' => 0,
                'category'            => 'utility',
                'sort_order'          => $sort++,
            ]);

            TenantLedgerEntry::create([
                'lease_id'            => $lease->id,
                'entry_date'          => $chargeDate,
                'description'         => "Water/Sewer - {$label} Sewer Base Charge",
                'charge_minor_units'  => $sewerBase,
                'payment_minor_units' => 0,
                'category'            => 'utility',
                'sort_order'          => $sort++,
            ]);

            if ($monthCursor->month % 2 === 0) {
                $useStart = $monthCursor->copy()->subMonth()->day(17);
                $useEnd = $monthCursor->copy()->day(17);
                TenantLedgerEntry::create([
                    'lease_id'            => $lease->id,
                    'entry_date'          => $chargeDate->copy()->addDays(2),
                    'description'         => sprintf(
                        'Water/Sewer - WATER USE, SERVICE DATES: %s - %s, RUBS METHOD: ACT OCC',
                        $useStart->format('m/d/Y'),
                        $useEnd->format('m/d/Y'),
                    ),
                    'charge_minor_units'  => max(100, (int) round($waterBase * 0.4)),
                    'payment_minor_units' => 0,
                    'category'            => 'utility',
                    'sort_order'          => $sort++,
                ]);
            }

            $payment = $lease->payments
                ->filter(fn (Payment $p) => $p->status === 'success'
                    && $p->due_date
                    && Carbon::parse($p->due_date)->isSameMonth($monthCursor))
                ->sortBy('due_date')
                ->first();

            if ($payment) {
                $due = Carbon::parse($payment->due_date);
                $collected = $payment->collected_at
                    ? Carbon::parse($payment->collected_at)
                    : $due->copy()->addDay();

                TenantLedgerEntry::create([
                    'lease_id'            => $lease->id,
                    'entry_date'          => $collected,
                    'description'         => 'Rent payment - '.$due->format('F Y'),
                    'paid_by'             => $tenant?->fullName(),
                    'charge_minor_units'  => 0,
                    'payment_minor_units' => (int) $payment->amount_minor_units,
                    'category'            => 'payment',
                    'payment_id'          => $payment->id,
                    'sort_order'          => $sort++,
                ]);
            }

            $monthCursor->addMonth();
        }
    }
}
