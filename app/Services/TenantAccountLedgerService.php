<?php

namespace App\Services;

use App\Models\Lease;
use App\Models\TenantLedgerEntry;
use Illuminate\Support\Collection;

class TenantAccountLedgerService
{
    /**
     * @return array{
     *   starting_minor: int,
     *   rows: Collection<int, array{entry: TenantLedgerEntry, balance_minor: int}>,
     *   ending_minor: int
     * }
     */
    public function build(Lease $lease): array
    {
        $starting = (int) ($lease->ledger_starting_balance_minor_units ?? 0);

        $entries = TenantLedgerEntry::query()
            ->where('lease_id', $lease->id)
            ->orderBy('entry_date')
            ->orderBy('sort_order')
            ->orderBy('created_at')
            ->get();

        $balance = $starting;
        $rows = $entries->map(function (TenantLedgerEntry $entry) use (&$balance) {
            $balance += (int) $entry->charge_minor_units - (int) $entry->payment_minor_units;

            return [
                'entry'          => $entry,
                'balance_minor'  => $balance,
            ];
        });

        return [
            'starting_minor' => $starting,
            'rows'           => $rows,
            'ending_minor'   => $balance,
        ];
    }

    public function formatMinor(int $minor): string
    {
        return number_format($minor / 100, 2);
    }
}
