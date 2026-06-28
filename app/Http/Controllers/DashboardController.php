<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessWebhookJob;
use App\Models\{Lease, Payment, Property, WaitlistEmail, MaintenanceRequest, Message};
use App\Payment\ProcessorFactory;
use App\Services\{LandlordPortfolioStats, PaymentService, LedgerService};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

// ─────────────────────────────────────────────────────────────
// DASHBOARD CONTROLLER
// ─────────────────────────────────────────────────────────────
class DashboardController extends Controller
{
    public function __construct(private readonly LedgerService $ledger) {}

    public function index()
    {
        $user = Auth::user();

        $properties = $user->properties()->with(['leases' => fn ($q) => $q->where('status', 'active')])->get();
        foreach ($properties as $property) {
            $property->syncStatusFromLeases();
        }
        $summary    = $this->ledger->monthlySummary($user->id, now()->year, now()->month);
        $stats      = LandlordPortfolioStats::dashboard($user->id);

        return view('dashboard.index', compact('user', 'properties', 'summary', 'stats'));
    }
}
