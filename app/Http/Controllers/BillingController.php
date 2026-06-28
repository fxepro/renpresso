<?php
namespace App\Http\Controllers;

use App\Models\Lease;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BillingController extends Controller
{
    private const RATE_MINOR = 900; // $9.00 in cents

    public function index()
    {
        $landlordId = Auth::id();
        $months     = $this->buildMonthSeries($landlordId);

        return view('dashboard.billing.index', [
            'months'     => $months,
            'rateMinor'  => self::RATE_MINOR,
        ]);
    }

    public function show(int $year, int $month)
    {
        abort_if($month < 1 || $month > 12, 404);

        $landlordId = Auth::id();
        $period     = Carbon::create($year, $month, 1)->startOfMonth();
        abort_if($period->isFuture() && !$period->isCurrentMonth(), 404);

        $leases = $this->leasesActiveInMonth($landlordId, $period);
        $count  = $leases->count();
        $total  = $count * self::RATE_MINOR;

        return view('dashboard.billing.show', [
            'period'    => $period,
            'leases'    => $leases,
            'count'     => $count,
            'total'     => $total,
            'rateMinor' => self::RATE_MINOR,
            'status'    => $this->monthStatus($period),
        ]);
    }

    // ── helpers ──────────────────────────────────────────────────────────────

    private function buildMonthSeries(string|int $landlordId): array
    {
        $earliest = Lease::query()
            ->whereHas('property', fn ($q) => $q->where('landlord_id', $landlordId))
            ->orderBy('activated_at')
            ->value('activated_at');

        $start   = $earliest ? Carbon::parse($earliest)->startOfMonth() : now()->startOfMonth();
        $current = now()->startOfMonth();
        $months  = [];

        for ($m = $current->copy(); $m->greaterThanOrEqualTo($start); $m->subMonth()) {
            $period = $m->copy();
            $count  = $this->leasesActiveInMonth($landlordId, $period)->count();
            $months[] = [
                'period'  => $period,
                'count'   => $count,
                'total'   => $count * self::RATE_MINOR,
                'status'  => $this->monthStatus($period),
            ];
        }

        return $months;
    }

    private function leasesActiveInMonth(string|int $landlordId, Carbon $period)
    {
        $start = $period->copy()->startOfMonth();
        $end   = $period->copy()->endOfMonth();

        return Lease::query()
            ->whereHas('property', fn ($q) => $q->where('landlord_id', $landlordId))
            ->where('activated_at', '<=', $end)
            ->where(fn ($q) => $q
                ->whereNull('end_date')
                ->orWhere('end_date', '>=', $start)
            )
            ->whereIn('status', ['active', 'expired', 'terminated'])
            ->with(['property:id,name,occupancy_mode,country_code', 'tenant:id,first_name,last_name'])
            ->get();
    }

    private function monthStatus(Carbon $period): string
    {
        if ($period->isCurrentMonth()) return 'due';
        if ($period->isFuture())       return 'upcoming';
        return 'paid';
    }
}
