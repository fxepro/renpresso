<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesMaintenanceTeam;
use App\Models\MaintenanceRequest;
use Illuminate\Support\Facades\Auth;

class MaintenancePortalController extends Controller
{
    use ResolvesMaintenanceTeam;

    public function dashboard()
    {
        $user = Auth::user();
        $team = $this->maintenanceTeam();

        $stats = [
            'open_requests' => 0,
            'in_progress' => 0,
            'resolved_30d' => 0,
            'outstanding_invoices' => 0,
            'received_30d_minor' => 0,
            'landlords_linked' => 0,
            'cities' => 0,
        ];

        if ($team) {
            $landlordIds = $team->engagedLandlordIds();
            $baseQuery = $team->assignedMaintenanceRequestsQuery();

            $stats['open_requests'] = (clone $baseQuery)->whereIn('status', ['submitted', 'acknowledged'])->count();
            $stats['in_progress'] = (clone $baseQuery)->where('status', 'in_progress')->count();
            $stats['resolved_30d'] = (clone $baseQuery)->where('status', 'resolved')
                ->where('updated_at', '>=', now()->subDays(30))->count();
            $stats['outstanding_invoices'] = $team->invoices()
                ->whereIn('status', ['sent', 'partially_paid'])
                ->count();
            $stats['received_30d_minor'] = (int) $team->paymentsReceived()
                ->where('paid_on', '>=', now()->subDays(30)->toDateString())
                ->sum('amount_minor');
            $stats['landlords_linked'] = $landlordIds->count();
            $stats['cities'] = $team->cities()->count();
        }

        $recentRequests = $team
            ? $team->assignedMaintenanceRequestsQuery()
                ->with(['lease.property', 'raisedBy', 'maintenanceTeam'])
                ->orderByDesc('updated_at')
                ->limit(6)
                ->get()
            : collect();

        $recentPayments = $team
            ? $team->paymentsReceived()->with('invoice', 'landlord')->orderByDesc('paid_on')->limit(5)->get()
            : collect();

        return view('dashboard.maintenance-portal.dashboard', compact('team', 'stats', 'recentRequests', 'recentPayments'));
    }
}
