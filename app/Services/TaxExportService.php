<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Property;
use App\Models\User;
use App\Support\CurrencyDisplay;

class TaxExportService
{
    public function __construct(private readonly LedgerService $ledger) {}

    /** @return list<array<string, mixed>> */
    public function portfolioSummaries(User $landlord, int $year): array
    {
        return $landlord->properties()
            ->orderBy('name')
            ->get()
            ->map(fn (Property $property) => $this->propertySummary($property, $landlord, $year))
            ->all();
    }

    /** @return array<string, mixed> */
    public function propertySummary(Property $property, User $landlord, int $year): array
    {
        $report = $this->ledger->annualReport($property->id, $year);

        return [
            'property'       => $property,
            'year'           => $year,
            'payment_count'  => $report['payment_count'],
            'totals'         => $report['totals'],
            'has_payments'   => $report['payment_count'] > 0,
            'home_currency'  => strtoupper($landlord->home_currency ?? 'USD'),
        ];
    }

    /** Full report payload for CSV/PDF export. */
    public function propertyReport(Property $property, User $landlord, int $year): array
    {
        $homeCurrency = strtoupper($landlord->home_currency ?? 'USD');

        $payments = Payment::query()
            ->where('status', 'success')
            ->whereYear('collected_at', $year)
            ->whereHas('lease', fn ($q) => $q->where('property_id', $property->id))
            ->with(['lease.tenant'])
            ->orderBy('collected_at')
            ->get();

        $rows = $payments->map(function (Payment $payment) use ($property) {
            $lease = $payment->lease;

            return [
                'date'                    => $payment->collected_at?->format('Y-m-d') ?? '',
                'tenant_name'             => trim(($lease->tenant->first_name ?? '').' '.($lease->tenant->last_name ?? '')),
                'unit_label'              => $lease->unit_label,
                'amount_minor_units'      => $payment->amount_minor_units,
                'currency_code'           => $payment->currency_code,
                'fx_rate'                 => $payment->fxRate(),
                'home_currency_code'      => $payment->home_currency_code,
                'home_amount_minor_units' => $payment->home_amount_minor_units,
                'processor_ref'           => $payment->processor_ref,
            ];
        })->all();

        return [
            'landlord'      => $landlord,
            'property'      => $property,
            'year'          => $year,
            'home_currency' => $homeCurrency,
            'payments'      => $rows,
            'payment_count' => count($rows),
            'totals'        => [
                'local_minor_units' => $payments->sum('amount_minor_units'),
                'home_minor_units'  => $payments->sum('home_amount_minor_units'),
            ],
            'generated_at'  => now(),
        ];
    }

    /** @return list<list<string>> */
    public function csvMatrix(array $report): array
    {
        $property = $report['property'];
        $landlord = $report['landlord'];
        $homeCurrency = $report['home_currency'];
        $localCurrency = strtoupper($property->currency_code);

        $matrix = [
            ['Renpresso — Annual rent income report'],
            ['Landlord', $landlord->leasePartyName(false)],
            ['Tax year', (string) $report['year']],
            ['Property', $property->name],
            ['Address', trim(implode(', ', array_filter([
                $property->address_line1,
                $property->city,
                $property->state_province,
                $property->postal_code,
                $property->country_code,
            ])))],
            ['Report currency (home ledger)', $homeCurrency],
            ['Generated', $report['generated_at']->format('Y-m-d H:i')],
            [],
            [
                'Collected date',
                'Tenant',
                'Unit',
                'Amount (local)',
                'Local currency',
                'FX rate',
                'Amount (reportable)',
                'Reportable currency',
                'Reference',
            ],
        ];

        foreach ($report['payments'] as $row) {
            $matrix[] = [
                $row['date'],
                $row['tenant_name'] ?: '—',
                $row['unit_label'] ?? '—',
                $this->majorPlain($row['amount_minor_units'], $row['currency_code']),
                strtoupper($row['currency_code']),
                number_format($row['fx_rate'], 6, '.', ''),
                $this->majorPlain($row['home_amount_minor_units'], $row['home_currency_code']),
                strtoupper($row['home_currency_code']),
                $row['processor_ref'] ?? '',
            ];
        }

        $matrix[] = [];
        $matrix[] = [
            'TOTALS',
            '',
            '',
            $this->majorPlain($report['totals']['local_minor_units'], $localCurrency),
            $localCurrency,
            '',
            $this->majorPlain($report['totals']['home_minor_units'], $homeCurrency),
            $homeCurrency,
            $report['payment_count'].' payment(s)',
        ];
        $matrix[] = [];
        $matrix[] = [
            'Note: Reportable amounts use FX rates snapshotted at payment time. '
            .'Intended for Schedule E / CPA review — not a substitute for professional tax advice.',
        ];

        return $matrix;
    }

    public function downloadFilename(Property $property, int $year, string $ext): string
    {
        $slug = preg_replace('/[^a-z0-9]+/i', '-', strtolower($property->name)) ?: 'property';
        $slug = trim($slug, '-');

        return "renpresso-tax-{$slug}-{$year}.{$ext}";
    }

    private function majorPlain(int $minorUnits, string $currencyCode): string
    {
        $decimals = CurrencyDisplay::decimalPlaces($currencyCode);

        return number_format($minorUnits / 100, $decimals, '.', '');
    }
}
