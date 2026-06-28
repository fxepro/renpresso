<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

// ─────────────────────────────────────────────────────────────
// FX SERVICE
// Fetches and caches live exchange rates.
// Rates are stored as integers (rate × 1,000,000) for precision.
// ─────────────────────────────────────────────────────────────
class FxService
{
    /**
     * Return the rate from $from to $to currency as an integer × 1,000,000.
     * Cached for 1 hour. Falls back to 1:1 if API unavailable.
     */
    public function rateToHome(string $fromCurrency, string $toCurrency): int
    {
        if ($fromCurrency === $toCurrency) {
            return 1_000_000; // 1:1
        }

        $cacheKey = "fx_rate_{$fromCurrency}_{$toCurrency}";

        return Cache::remember($cacheKey, 3600, function () use ($fromCurrency, $toCurrency) {
            try {
                $response = Http::timeout(5)->get(
                    config('services.fx.api_url') . '/latest/' . $fromCurrency,
                    ['apikey' => config('services.fx.api_key')]
                );

                if ($response->successful()) {
                    $rates = $response->json('conversion_rates', []);
                    $rate  = $rates[$toCurrency] ?? null;
                    if ($rate) {
                        return (int)round($rate * 1_000_000);
                    }
                }
            } catch (\Exception $e) {
                Log::error("FX rate fetch failed: {$e->getMessage()}");
            }

            // Fallback: return 1:1 and log — never block a payment
            Log::warning("FX rate unavailable for {$fromCurrency}/{$toCurrency}, using 1:1 fallback");
            return 1_000_000;
        });
    }

    /**
     * Convert minor units from one currency to another using a stored snapshot rate.
     * Used for display only — never for recalculating historical amounts.
     */
    public function convertMinorUnits(int $amountMinorUnits, int $fxRateSnapshot): int
    {
        return (int)round($amountMinorUnits * ($fxRateSnapshot / 1_000_000));
    }
}

// ─────────────────────────────────────────────────────────────
// LEDGER SERVICE
// Aggregates payment data for dashboard and tax export.
// Always reads from snapshotted data — never recalculates FX.
// ─────────────────────────────────────────────────────────────
class LedgerService
{
    /**
     * Return monthly income summary for a landlord across all properties.
     * Amounts in home currency using stored FX snapshots.
     */
    public function monthlySummary(string $landlordId, int $year, int $month): array
    {
        return \App\Models\Payment::query()
            ->whereHas('lease.property', fn($q) => $q->where('landlord_id', $landlordId))
            ->where('status', 'success')
            ->whereYear('collected_at', $year)
            ->whereMonth('collected_at', $month)
            ->with('lease.property')
            ->get()
            ->groupBy('lease.property_id')
            ->map(function ($payments) {
                $first = $payments->first();
                return [
                    'property_id'             => $first->lease->property_id,
                    'property_name'           => $first->lease->property->name,
                    'currency_code'           => $first->currency_code,
                    'total_local_minor_units' => $payments->sum('amount_minor_units'),
                    'home_currency_code'      => $first->home_currency_code,
                    'total_home_minor_units'  => $payments->sum('home_amount_minor_units'),
                    'payment_count'           => $payments->count(),
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * Annual income report per property — for tax export.
     * Returns all successful payments for a property in a given year.
     */
    public function annualReport(string $propertyId, int $year): array
    {
        $payments = \App\Models\Payment::query()
            ->where('status', 'success')
            ->whereYear('collected_at', $year)
            ->whereHas('lease', fn($q) => $q->where('property_id', $propertyId))
            ->with(['lease.property', 'lease.tenant'])
            ->orderBy('collected_at')
            ->get();

        return [
            'property_id'   => $propertyId,
            'year'          => $year,
            'payment_count' => $payments->count(),
            'payments'      => $payments->map(fn($p) => [
                'date'                    => $p->collected_at->format('Y-m-d'),
                'amount_minor_units'      => $p->amount_minor_units,
                'currency_code'           => $p->currency_code,
                'fx_rate'                 => $p->fxRate(),
                'home_currency_code'      => $p->home_currency_code,
                'home_amount_minor_units' => $p->home_amount_minor_units,
                'processor_ref'           => $p->processor_ref,
            ])->toArray(),
            'totals' => [
                'local_minor_units' => $payments->sum('amount_minor_units'),
                'home_minor_units'  => $payments->sum('home_amount_minor_units'),
            ],
        ];
    }
}
