<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Services\TaxExportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TaxExportController extends Controller
{
    public function __construct(private readonly TaxExportService $taxExport) {}

    public function index(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->isLandlord(), 403);

        $year = max(2000, min(2100, (int) $request->query('year', now()->year - 1)));
        $homeCurrency = strtoupper($user->home_currency ?? 'USD');

        $summaries = $this->taxExport->portfolioSummaries($user, $year);
        $portfolioTotal = array_sum(array_column(array_column($summaries, 'totals'), 'home_minor_units'));
        $portfolioPayments = array_sum(array_column($summaries, 'payment_count'));
        $propertiesWithData = collect($summaries)->where('has_payments', true)->count();

        return view('dashboard.tax-export.index', compact(
            'user',
            'year',
            'homeCurrency',
            'summaries',
            'portfolioTotal',
            'portfolioPayments',
            'propertiesWithData',
        ));
    }

    public function csv(Request $request, Property $property): StreamedResponse
    {
        $user = Auth::user();
        abort_unless($user->isLandlord(), 403);
        $this->authorize('view', $property);
        abort_unless($property->landlord_id === $user->id, 403);

        $year = max(2000, min(2100, (int) $request->query('year', now()->year - 1)));
        $report = $this->taxExport->propertyReport($property, $user, $year);
        $filename = $this->taxExport->downloadFilename($property, $year, 'csv');

        return response()->streamDownload(function () use ($report) {
            $handle = fopen('php://output', 'w');
            foreach ($this->taxExport->csvMatrix($report) as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function pdf(Request $request, Property $property)
    {
        $user = Auth::user();
        abort_unless($user->isLandlord(), 403);
        $this->authorize('view', $property);
        abort_unless($property->landlord_id === $user->id, 403);

        $year = max(2000, min(2100, (int) $request->query('year', now()->year - 1)));
        $report = $this->taxExport->propertyReport($property, $user, $year);
        $filename = $this->taxExport->downloadFilename($property, $year, 'pdf');

        return Pdf::loadView('dashboard.tax-export.pdf', compact('report'))
            ->setPaper('letter', 'portrait')
            ->download($filename);
    }
}
