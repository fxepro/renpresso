<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lease;
use App\Models\MaintenanceInvoice;
use App\Models\MaintenancePaymentReceived;
use App\Models\MaintenanceTeam;
use App\Models\Payment;
use App\Models\Property;
use App\Models\User;
use App\Models\WaitlistEmail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function dashboard(): View
    {
        $now = now();

        // ── User counts ───────────────────────────────────────────────────────
        $landlordCount   = User::where('role', 'landlord')->count();
        $tenantCount     = User::where('role', 'tenant')->count();
        $maintTeamCount  = MaintenanceTeam::count();
        $propertyCount   = Property::count();

        // ── Lease health ──────────────────────────────────────────────────────
        $activeLeases    = Lease::where('status', 'active')->count();
        $mrr             = $activeLeases * self::BILLING_RATE_MINOR; // $9 per active lease

        // ── Rent payments this month ──────────────────────────────────────────
        $monthPayments = Payment::query()
            ->whereMonth('due_date', $now->month)
            ->whereYear('due_date', $now->year)
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as success,
                SUM(CASE WHEN status = 'failed'  THEN 1 ELSE 0 END) as failed,
                SUM(CASE WHEN status = 'success' THEN home_amount_minor_units ELSE 0 END) as home_gmv
            ")
            ->first();

        $rentGmvMonth     = (int) ($monthPayments->home_gmv ?? 0);
        $collectionRate   = ($monthPayments->total ?? 0) > 0
            ? round($monthPayments->success / $monthPayments->total * 100)
            : null;
        $failedThisMonth  = (int) ($monthPayments->failed ?? 0);

        // ── Maintenance this month ────────────────────────────────────────────
        $maintGmvMonth = (int) MaintenancePaymentReceived::query()
            ->whereNotNull('maintenance_invoice_id')
            ->whereMonth('paid_on', $now->month)
            ->whereYear('paid_on', $now->year)
            ->sum('amount_minor');

        // ── YTD platform revenue ──────────────────────────────────────────────
        $ytdSubRev = $this->ytdSubscriptionRevenue($now->year);

        $ytdMaintComm = (int) round(
            (float) DB::table('maintenance_payments_received')
                ->where('method', 'platform')
                ->whereYear('paid_on', $now->year)
                ->sum('amount_minor')
            * self::MAINTENANCE_COMMISSION_RATE
        );

        // ── Maintenance invoices ──────────────────────────────────────────────
        $invoicesSent    = MaintenanceInvoice::whereIn('status', ['sent', 'partially_paid', 'paid'])->count();
        $invoicesOverdue = MaintenanceInvoice::whereIn('status', ['sent', 'partially_paid'])
            ->where('due_date', '<', today())
            ->count();

        // ── Reporting currency (home currency from most recent payment) ───────
        $homeCurrency = DB::table('payments')
            ->where('status', 'success')
            ->orderByDesc('collected_at')
            ->value('home_currency_code') ?? config('admin.reporting_currency', 'USD');

        return view('admin.index', [
            'stats' => [
                'landlords'       => $landlordCount,
                'tenants'         => $tenantCount,
                'maint_teams'     => $maintTeamCount,
                'properties'      => $propertyCount,
                'active_leases'   => $activeLeases,
                'mrr'             => $mrr,
                'rent_gmv_month'  => $rentGmvMonth,
                'collection_rate' => $collectionRate,
                'failed_month'    => $failedThisMonth,
                'maint_gmv_month' => $maintGmvMonth,
                'invoices_sent'   => $invoicesSent,
                'invoices_overdue'=> $invoicesOverdue,
                'ytd_sub_rev'     => $ytdSubRev,
                'ytd_maint_comm'  => $ytdMaintComm,
                'ytd_total'       => $ytdSubRev + $ytdMaintComm,
                'waitlist'        => WaitlistEmail::count(),
                'currency'        => $homeCurrency,
            ],
            'reportingCurrency' => $homeCurrency,
        ]);
    }

    /** Sum of subscription revenue for all months in a given year. */
    private function ytdSubscriptionRevenue(int $year): int
    {
        $start   = Carbon::create($year, 1, 1)->startOfMonth();
        $current = now()->startOfMonth();
        $end     = Carbon::create($year, 12, 1)->startOfMonth();
        $ceiling = $current->lt($end) ? $current : $end;

        $total = 0;
        for ($m = $start->copy(); $m->lte($ceiling); $m->addMonth()) {
            $total += $this->subscriptionLeaseCountForMonth($m) * self::BILLING_RATE_MINOR;
        }

        return $total;
    }

    public function placeholder(string $page): View
    {
        $meta = $this->pageMeta($page);

        return view('admin.placeholder', [
            'pageTitle'   => $meta['title'],
            'breadcrumb'  => $meta['breadcrumb'],
            'description' => $meta['description'],
        ]);
    }

    /** @return array{title: string, breadcrumb: string, description: string} */
    private function pageMeta(string $page): array
    {
        return match ($page) {
            'revenue' => [
                'title' => 'Platform revenue',
                'breadcrumb' => 'Finance',
                'description' => 'MTD/QTD/YTD platform commission and subscription revenue in reporting currency, split by rent vs maintenance and by landlord home country.',
            ],
            'revenue-ledger' => [
                'title' => 'Revenue ledger',
                'breadcrumb' => 'Finance',
                'description' => 'Immutable ledger of recognized platform revenue (commission rules applied at event time).',
            ],
            'commission-rules' => [
                'title' => 'Commission rules',
                'breadcrumb' => 'Finance',
                'description' => 'Configure subscription, rent, and maintenance take rates by property country and landlord home country.',
            ],
            'rent-collections' => [
                'title' => 'Rent collections',
                'breadcrumb' => 'Finance',
                'description' => 'All successful tenant rent payments across landlords — local amount, home equivalent, processor ref.',
            ],
            'maintenance-payments' => [
                'title' => 'Maintenance payments',
                'breadcrumb' => 'Finance',
                'description' => 'Landlord-approved platform payments to maintenance teams.',
            ],
            'landlord-billing' => [
                'title' => 'Landlord subscriptions',
                'breadcrumb' => 'Finance',
                'description' => 'Per-unit monthly billing ($9/unit), free first month on first property, Stripe subscription state.',
            ],
            'repatriation' => [
                'title' => 'FX & repatriation',
                'breadcrumb' => 'Finance',
                'description' => 'Cross-border rent: collected locally, repatriation logged by landlords (read-only aggregate).',
            ],
            'tax-export' => [
                'title' => 'Tax export',
                'breadcrumb' => 'Finance',
                'description' => 'Period-close exports for accounting.',
            ],
            'landlords' => [
                'title' => 'Landlords',
                'breadcrumb' => 'Operations',
                'description' => 'All landlord accounts — portfolio, KYC, billing, engagement.',
            ],
            'tenants' => [
                'title' => 'Tenants',
                'breadcrumb' => 'Operations',
                'description' => 'Renter accounts and active leases.',
            ],
            'properties' => [
                'title' => 'Properties',
                'breadcrumb' => 'Operations',
                'description' => 'Full property catalog across landlords and countries.',
            ],
            'leases' => [
                'title' => 'Leases',
                'breadcrumb' => 'Operations',
                'description' => 'Active and historical leases.',
            ],
            'maintenance-teams' => [
                'title' => 'Maintenance teams',
                'breadcrumb' => 'Operations',
                'description' => 'Maintenance companies, cities, listings, documents.',
            ],
            'maintenance-requests' => [
                'title' => 'Maintenance requests',
                'breadcrumb' => 'Operations',
                'description' => 'Tickets from tenants/landlords and assignment state.',
            ],
            'maintenance-invoices' => [
                'title' => 'Maintenance invoices',
                'breadcrumb' => 'Operations',
                'description' => 'Invoices issued by teams to landlords (all statuses).',
            ],
            'messages' => [
                'title' => 'Messages',
                'breadcrumb' => 'Operations',
                'description' => 'Landlord–tenant message threads.',
            ],
            'applications' => [
                'title' => 'Applications',
                'breadcrumb' => 'Operations',
                'description' => 'Rental applications on listings.',
            ],
            'waitlist' => [
                'title' => 'Waitlist',
                'breadcrumb' => 'Operations',
                'description' => 'Marketing waitlist signups.',
            ],
            'documents' => [
                'title' => 'Documents',
                'breadcrumb' => 'Operations',
                'description' => 'Landlord document library uploads.',
            ],
            'deals' => [
                'title' => 'Deals',
                'breadcrumb' => 'Operations',
                'description' => 'Insurance and coupon programs.',
            ],
            'kyc' => [
                'title' => 'KYC queue',
                'breadcrumb' => 'Operations',
                'description' => 'Landlords pending identity verification.',
            ],
            'helpline' => [
                'title' => 'Helpline log',
                'breadcrumb' => 'Help',
                'description' => 'Support questions and feedback from all roles.',
            ],
            default => [
                'title' => 'Admin',
                'breadcrumb' => 'Admin',
                'description' => 'This section is not configured yet.',
            ],
        };
    }

    // ── Rent collections ─────────────────────────────────────────────────────

    public function rentCollections(): View
    {
        // Monthly series — one query, grouped by due_date year/month
        $monthly = DB::table('payments')
            ->join('leases', 'payments.lease_id', '=', 'leases.id')
            ->whereNull('leases.deleted_at')
            ->selectRaw("
                EXTRACT(YEAR  FROM payments.due_date)::int AS yr,
                EXTRACT(MONTH FROM payments.due_date)::int AS mo,
                COUNT(*)                                                                      AS total_count,
                SUM(CASE WHEN payments.status = 'success' THEN 1 ELSE 0 END)::int            AS success_count,
                SUM(CASE WHEN payments.status = 'failed'  THEN 1 ELSE 0 END)::int            AS failed_count,
                SUM(CASE WHEN payments.status = 'pending' THEN 1 ELSE 0 END)::int            AS pending_count,
                SUM(CASE WHEN payments.status = 'success' THEN payments.home_amount_minor_units ELSE 0 END) AS home_gmv,
                SUM(CASE WHEN payments.status = 'success' THEN payments.amount_minor_units    ELSE 0 END)   AS local_gmv
            ")
            ->groupByRaw("EXTRACT(YEAR FROM payments.due_date), EXTRACT(MONTH FROM payments.due_date)")
            ->orderByRaw("yr DESC, mo DESC")
            ->get();

        $currentMonth = $monthly->first();
        $stats = [
            'current_month_gmv'  => $currentMonth?->home_gmv ?? 0,
            'current_month_label'=> $currentMonth ? Carbon::create($currentMonth->yr, $currentMonth->mo)->format('M Y') : now()->format('M Y'),
            'ytd_gmv'            => $monthly->where('yr', now()->year)->sum('home_gmv'),
            'total_payments'     => $monthly->sum('total_count'),
            'failed_payments'    => $monthly->sum('failed_count'),
            'success_rate'       => $monthly->sum('total_count') > 0
                ? round($monthly->sum('success_count') / $monthly->sum('total_count') * 100, 1)
                : 0,
        ];

        // Detect the home currency in use (from most recent successful payment)
        $homeCurrency = DB::table('payments')
            ->where('status', 'success')
            ->orderByDesc('collected_at')
            ->value('home_currency_code') ?? 'USD';

        return view('admin.rent-collections.index', compact('monthly', 'stats', 'homeCurrency'));
    }

    public function rentCollectionsMonth(int $year, int $month): View
    {
        abort_if($month < 1 || $month > 12, 404);
        $period = Carbon::create($year, $month, 1);
        abort_if($period->isFuture() && !$period->isCurrentMonth(), 404);

        $payments = Payment::query()
            ->whereYear('due_date', $year)
            ->whereMonth('due_date', $month)
            ->with([
                'lease.property:id,name,city,country_code,occupancy_mode,landlord_id',
                'lease.property.landlord:id,first_name,last_name',
                'lease.tenant:id,first_name,last_name',
            ])
            ->orderByDesc('due_date')
            ->orderBy('status')
            ->get();

        $summary = [
            'success' => $payments->where('status', 'success')->count(),
            'failed'  => $payments->where('status', 'failed')->count(),
            'pending' => $payments->where('status', 'pending')->count(),
            'home_gmv'=> $payments->where('status', 'success')->sum('home_amount_minor_units'),
            'home_currency' => $payments->where('status', 'success')->first()?->home_currency_code ?? 'USD',
        ];

        return view('admin.rent-collections.show', compact('payments', 'period', 'summary'));
    }

    // ── Maintenance teams ────────────────────────────────────────────────────

    public function maintenanceTeams(): View
    {
        $teams = \App\Models\MaintenanceTeam::query()
            ->with('owner:id,first_name,last_name,email')
            ->withCount([
                'reviews',
                'engagedLandlords',
                'invoices',
                'cities',
            ])
            ->withAvg('reviews', 'rating')
            ->orderByDesc('is_listed')
            ->orderBy('name')
            ->get();

        $stats = [
            'total'    => $teams->count(),
            'listed'   => $teams->where('is_listed', true)->count(),
            'unlisted' => $teams->where('is_listed', false)->count(),
            'countries'=> $teams->pluck('country_code')->unique()->count(),
        ];

        return view('admin.maintenance-teams.index', compact('teams', 'stats'));
    }

    public function maintenanceTeamShow(\App\Models\MaintenanceTeam $maintenanceTeam): View
    {
        $maintenanceTeam->load([
            'owner:id,first_name,last_name,email',
            'cities',
            'engagedLandlords:id,first_name,last_name,email,home_country',
            'reviews' => fn ($q) => $q->with('reviewer:id,first_name,last_name')->orderByDesc('created_at')->limit(10),
            'invoices' => fn ($q) => $q->with('property:id,name')->orderByDesc('created_at')->limit(10),
        ]);

        // Maintenance requests assigned to this team
        $requests = \App\Models\MaintenanceRequest::query()
            ->where('maintenance_team_id', $maintenanceTeam->id)
            ->with([
                'lease.property:id,name,city,country_code',
                'raisedBy:id,first_name,last_name',
            ])
            ->orderByDesc('created_at')
            ->get();

        $compliance = $maintenanceTeam->complianceSummary();

        return view('admin.maintenance-teams.show', [
            'team'       => $maintenanceTeam,
            'requests'   => $requests,
            'compliance' => $compliance,
        ]);
    }

    // ── Maintenance requests ─────────────────────────────────────────────────

    public function maintenanceRequests(): View
    {
        $requests = \App\Models\MaintenanceRequest::query()
            ->with([
                'lease.property:id,name,city,country_code,landlord_id',
                'lease.property.landlord:id,first_name,last_name',
                'raisedBy:id,first_name,last_name,email',
                'maintenanceTeam:id,name,country_code',
            ])
            ->withCount('followUps')
            ->orderByDesc('created_at')
            ->get();

        $stats = [
            'total'       => $requests->count(),
            'submitted'   => $requests->where('status', 'submitted')->count(),
            'acknowledged'=> $requests->where('status', 'acknowledged')->count(),
            'in_progress' => $requests->where('status', 'in_progress')->count(),
            'resolved'    => $requests->where('status', 'resolved')->count(),
            'unassigned'  => $requests->whereNull('maintenance_team_id')->count(),
        ];

        return view('admin.maintenance-requests.index', compact('requests', 'stats'));
    }

    public function maintenanceRequestShow(\App\Models\MaintenanceRequest $maintenanceRequest): View
    {
        $maintenanceRequest->load([
            'lease.property:id,name,address_line1,city,country_code,currency_code,landlord_id',
            'lease.property.landlord:id,first_name,last_name,email',
            'lease.tenant:id,first_name,last_name,email',
            'raisedBy:id,first_name,last_name,email',
            'maintenanceTeam:id,name,city,country_code,phone',
            'followUps',
        ]);

        return view('admin.maintenance-requests.show', ['request' => $maintenanceRequest]);
    }

    // ── Maintenance invoices ──────────────────────────────────────────────────

    public function maintenanceInvoices(): View
    {
        $invoices = MaintenanceInvoice::query()
            ->with([
                'team:id,name,country_code',
                'property:id,name,city,country_code',
                'landlord:id,first_name,last_name',
            ])
            ->orderByDesc('created_at')
            ->get();

        $stats = [
            'total'    => $invoices->count(),
            'draft'    => $invoices->where('status', 'draft')->count(),
            'sent'     => $invoices->where('status', 'sent')->count(),
            'paid'     => $invoices->where('status', 'paid')->count(),
            'overdue'  => $invoices->where('status', 'sent')
                ->filter(fn ($i) => $i->due_date && $i->due_date->isPast())->count(),
        ];

        return view('admin.maintenance-invoices.index', compact('invoices', 'stats'));
    }

    public function maintenanceInvoiceShow(MaintenanceInvoice $maintenanceInvoice): View
    {
        $maintenanceInvoice->load([
            'team:id,name,city,country_code,phone',
            'property:id,name,address_line1,city,country_code,currency_code',
            'landlord:id,first_name,last_name,email',
            'maintenanceRequest',
            'lines',
            'paymentsReceived',
        ]);

        return view('admin.maintenance-invoices.show', ['invoice' => $maintenanceInvoice]);
    }

    // ── Tenants ──────────────────────────────────────────────────────────────

    public function tenants(): View
    {
        $tenants = User::query()
            ->where('role', 'tenant')
            ->with([
                'leases' => fn ($q) => $q
                    ->where('status', 'active')
                    ->with('property:id,name,city,country_code,currency_code,landlord_id')
                    ->with('property.landlord:id,first_name,last_name')
                    ->latest('activated_at')
                    ->limit(1),
            ])
            ->withCount([
                'leases as active_lease_count'  => fn ($q) => $q->where('status', 'active'),
                'leases as total_lease_count',
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        // Failed payments per tenant via a single query
        $failedByTenant = DB::table('payments')
            ->join('leases', 'payments.lease_id', '=', 'leases.id')
            ->whereNull('leases.deleted_at')
            ->where('payments.status', 'failed')
            ->groupBy('leases.tenant_id')
            ->select('leases.tenant_id', DB::raw('count(*) as cnt'))
            ->pluck('cnt', 'tenant_id');

        $stats = [
            'total'           => $tenants->count(),
            'with_lease'      => $tenants->where('active_lease_count', '>', 0)->count(),
            'no_lease'        => $tenants->where('active_lease_count', 0)->count(),
            'payment_alerts'  => $failedByTenant->count(),
        ];

        return view('admin.tenants.index', compact('tenants', 'failedByTenant', 'stats'));
    }

    public function tenantShow(User $user): View
    {
        abort_if($user->role !== 'tenant', 404);

        $user->load([
            'leases' => fn ($q) => $q
                ->with([
                    'property:id,name,address_line1,city,country_code,currency_code,occupancy_mode,unit_slots_meta,landlord_id',
                    'property.landlord:id,first_name,last_name,email',
                    'payments' => fn ($q) => $q->orderByDesc('due_date'),
                ])
                ->orderByDesc('activated_at'),
        ]);

        $activeLease  = $user->leases->firstWhere('status', 'active');
        $allPayments  = $user->leases->flatMap(fn ($l) => $l->payments);

        $paymentStats = [
            'success'               => $allPayments->where('status', 'success')->count(),
            'failed'                => $allPayments->where('status', 'failed')->count(),
            'pending'               => $allPayments->where('status', 'pending')->count(),
            'total_collected_minor' => $allPayments->where('status', 'success')->sum('amount_minor_units'),
            'currency'              => $activeLease?->currency_code ?? $user->leases->first()?->currency_code,
        ];

        return view('admin.tenants.show', compact('user', 'activeLease', 'allPayments', 'paymentStats'));
    }

    // ── Leases ───────────────────────────────────────────────────────────────

    public function leases(): View
    {
        $leases = Lease::query()
            ->with([
                'property:id,name,address_line1,city,country_code,currency_code,occupancy_mode,landlord_id',
                'property.landlord:id,first_name,last_name',
                'tenant:id,first_name,last_name,email',
            ])
            ->withCount([
                'payments as failed_payment_count'  => fn ($q) => $q->where('status', 'failed'),
                'payments as pending_payment_count' => fn ($q) => $q->where('status', 'pending'),
                'payments as total_payment_count',
            ])
            ->orderByDesc('activated_at')
            ->get();

        $stats = [
            'total'          => $leases->count(),
            'active'         => $leases->where('status', 'active')->count(),
            'expired'        => $leases->where('status', 'expired')->count(),
            'terminated'     => $leases->where('status', 'terminated')->count(),
            'failed_payments'=> $leases->sum('failed_payment_count'),
            'pending_payments'=> $leases->sum('pending_payment_count'),
        ];

        return view('admin.leases.index', compact('leases', 'stats'));
    }

    public function leaseShow(Lease $lease): View
    {
        $lease->load([
            'property:id,name,address_line1,city,country_code,currency_code,occupancy_mode,unit_slots_meta,landlord_id',
            'property.landlord:id,first_name,last_name,email',
            'tenant:id,first_name,last_name,email,phone',
            'payments' => fn ($q) => $q->orderByDesc('due_date'),
        ]);

        $paymentStats = [
            'success' => $lease->payments->where('status', 'success')->count(),
            'failed'  => $lease->payments->where('status', 'failed')->count(),
            'pending' => $lease->payments->where('status', 'pending')->count(),
            'total_collected_minor' => $lease->payments->where('status', 'success')->sum('amount_minor_units'),
        ];

        return view('admin.leases.show', compact('lease', 'paymentStats'));
    }

    // ── Properties ───────────────────────────────────────────────────────────

    public function properties(): View
    {
        $properties = Property::query()
            ->with('landlord:id,first_name,last_name,home_country')
            ->withCount([
                'leases as active_lease_count' => fn ($q) => $q->where('status', 'active'),
                'leases as total_lease_count',
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        $stats = [
            'total'        => $properties->count(),
            'single'       => $properties->where('occupancy_mode', 'single')->count(),
            'multi'        => $properties->where('occupancy_mode', 'multi')->count(),
            'vacant'       => $properties->where('active_lease_count', 0)->count(),
            'active_leases'=> $properties->sum('active_lease_count'),
        ];

        return view('admin.properties.index', compact('properties', 'stats'));
    }

    public function propertyShow(Property $property): View
    {
        $property->load([
            'landlord:id,first_name,last_name,email,home_country,home_currency,landlord_account_status',
            'leases' => fn ($q) => $q->with('tenant:id,first_name,last_name,email')->orderByDesc('activated_at'),
        ]);

        return view('admin.properties.show', compact('property'));
    }

    // ── Executive revenue dashboard ───────────────────────────────────────────

    private const MAINTENANCE_COMMISSION_RATE = 0.05; // 5 %

    public function revenue(): View
    {
        // Earliest lease ever activated — determines how far back the series goes
        $earliest = Lease::query()
            ->whereHas('property', fn ($q) => $q->whereNotNull('landlord_id'))
            ->orderBy('activated_at')
            ->value('activated_at');

        $start   = $earliest ? Carbon::parse($earliest)->startOfMonth() : now()->startOfMonth();
        $current = now()->startOfMonth();

        // Maintenance commissions by month — single aggregate query
        $maintByMonth = DB::table('maintenance_payments_received')
            ->where('method', 'platform')
            ->selectRaw("
                EXTRACT(YEAR  FROM paid_on)::int AS yr,
                EXTRACT(MONTH FROM paid_on)::int AS mo,
                SUM(amount_minor) AS total_paid
            ")
            ->groupByRaw("EXTRACT(YEAR FROM paid_on), EXTRACT(MONTH FROM paid_on)")
            ->get()
            ->keyBy(fn ($r) => "{$r->yr}-{$r->mo}");

        // Rent GMV by month — single aggregate query
        $gmvByMonth = DB::table('payments')
            ->where('status', 'success')
            ->selectRaw("
                EXTRACT(YEAR  FROM due_date)::int AS yr,
                EXTRACT(MONTH FROM due_date)::int AS mo,
                SUM(home_amount_minor_units) AS home_gmv
            ")
            ->groupByRaw("EXTRACT(YEAR FROM due_date), EXTRACT(MONTH FROM due_date)")
            ->get()
            ->keyBy(fn ($r) => "{$r->yr}-{$r->mo}");

        // Build monthly rows
        $months = [];
        for ($m = $current->copy(); $m->greaterThanOrEqualTo($start); $m->subMonth()) {
            $period     = $m->copy();
            $key        = "{$period->year}-{$period->month}";
            $leases     = $this->subscriptionLeaseCountForMonth($period);
            $subRev     = $leases * self::BILLING_RATE_MINOR;
            $maintPaid  = (int) ($maintByMonth->get($key)?->total_paid ?? 0);
            $maintComm  = (int) round($maintPaid * self::MAINTENANCE_COMMISSION_RATE);
            $gmv        = (int) ($gmvByMonth->get($key)?->home_gmv ?? 0);
            $totalRev   = $subRev + $maintComm;

            $months[] = [
                'period'      => $period,
                'lease_count' => $leases,
                'sub_rev'     => $subRev,
                'maint_comm'  => $maintComm,
                'maint_paid'  => $maintPaid,
                'total_rev'   => $totalRev,
                'gmv'         => $gmv,
                'take_rate'   => $gmv > 0 ? round($totalRev / $gmv * 100, 2) : null,
            ];
        }

        $currentRow = $months[0] ?? null;

        // YTD filtered rows
        $ytdMonths = array_filter($months, fn ($r) => $r['period']->year === now()->year);

        // Lifetime totals
        $allSubRev  = array_sum(array_column($months, 'sub_rev'));
        $allMaint   = array_sum(array_column($months, 'maint_comm'));
        $allGmv     = array_sum(array_column($months, 'gmv'));

        $homeCurrency = DB::table('payments')
            ->where('status', 'success')
            ->orderByDesc('collected_at')
            ->value('home_currency_code') ?? 'USD';

        $ytdArr = array_values($ytdMonths);

        $stats = [
            'currency'       => $homeCurrency,
            'mrr'            => $currentRow['sub_rev']     ?? 0,
            'arr'            => ($currentRow['sub_rev']    ?? 0) * 12,
            'active_leases'  => $currentRow['lease_count'] ?? 0,
            'ytd_sub_rev'    => array_sum(array_column($ytdArr, 'sub_rev')),
            'ytd_maint_comm' => array_sum(array_column($ytdArr, 'maint_comm')),
            'ytd_total'      => array_sum(array_column($ytdArr, 'total_rev')),
            'ytd_gmv'        => array_sum(array_column($ytdArr, 'gmv')),
            'all_sub_rev'    => $allSubRev,
            'all_maint_comm' => $allMaint,
            'all_total'      => $allSubRev + $allMaint,
            'all_gmv'        => $allGmv,
            'all_take_rate'  => $allGmv > 0 ? round(($allSubRev + $allMaint) / $allGmv * 100, 2) : null,
            'maint_rate_pct' => round(self::MAINTENANCE_COMMISSION_RATE * 100),
        ];

        return view('admin.revenue.index', compact('months', 'stats'));
    }

    // ── Landlord billing ─────────────────────────────────────────────────────

    private const BILLING_RATE_MINOR = 900; // $9.00 in cents

    /** Platform-wide month-by-month subscription summary. */
    public function landlordBillingIndex(): View
    {
        $earliest = Lease::query()
            ->whereHas('property', fn ($q) => $q->whereNotNull('landlord_id'))
            ->orderBy('activated_at')
            ->value('activated_at');

        $start   = $earliest ? Carbon::parse($earliest)->startOfMonth() : now()->startOfMonth();
        $current = now()->startOfMonth();
        $months  = [];

        for ($m = $current->copy(); $m->greaterThanOrEqualTo($start); $m->subMonth()) {
            $period  = $m->copy();
            $leases  = $this->platformLeasesActiveInMonth($period);
            $count   = $leases->count();
            $landlordCount = $leases->unique('property.landlord_id')->count();

            $months[] = [
                'period'         => $period,
                'lease_count'    => $count,
                'landlord_count' => $landlordCount,
                'total_minor'    => $count * self::BILLING_RATE_MINOR,
                'status'         => $this->billingMonthStatus($period),
            ];
        }

        $currentMonthLeases = count($months) > 0 ? $months[0]['lease_count'] : 0;
        $stats = [
            'mrr_minor'      => $currentMonthLeases * self::BILLING_RATE_MINOR,
            'active_leases'  => $currentMonthLeases,
            'billed_months'  => count($months),
            'rate_minor'     => self::BILLING_RATE_MINOR,
        ];

        return view('admin.landlord-billing.index', compact('months', 'stats'));
    }

    /** Per-landlord breakdown for one calendar month. */
    public function landlordBillingMonth(int $year, int $month): View
    {
        abort_if($month < 1 || $month > 12, 404);
        $period = Carbon::create($year, $month, 1)->startOfMonth();
        abort_if($period->isFuture() && !$period->isCurrentMonth(), 404);

        $leases = $this->platformLeasesActiveInMonth($period);

        // Group by landlord_id → aggregate counts + bill
        $byLandlord = $leases
            ->groupBy(fn ($l) => $l->property->landlord_id)
            ->map(fn ($group) => [
                'landlord'    => $group->first()->property->landlord,
                'lease_count' => $group->count(),
                'total_minor' => $group->count() * self::BILLING_RATE_MINOR,
                'leases'      => $group,
            ])
            ->sortByDesc('lease_count')
            ->values();

        $totalLeases = $leases->count();
        $totalMinor  = $totalLeases * self::BILLING_RATE_MINOR;
        $status      = $this->billingMonthStatus($period);

        return view('admin.landlord-billing.show', compact(
            'period', 'byLandlord', 'totalLeases', 'totalMinor', 'status'
        ));
    }

    private function platformLeasesActiveInMonth(Carbon $period)
    {
        $start = $period->copy()->startOfMonth();
        $end   = $period->copy()->endOfMonth();

        return Lease::query()
            ->whereHas('property', fn ($q) => $q->whereNotNull('landlord_id'))
            ->where('activated_at', '<=', $end)
            ->where(fn ($q) => $q
                ->whereNull('end_date')
                ->orWhere('end_date', '>=', $start)
            )
            ->whereIn('status', ['active', 'expired', 'terminated'])
            ->with([
                'property:id,name,landlord_id,country_code,occupancy_mode,currency_code',
                'property.landlord:id,first_name,last_name,email,landlord_account_status,home_country',
            ])
            ->get();
    }

    /** Lightweight count of leases active in a given month — no model relations loaded. */
    private function subscriptionLeaseCountForMonth(Carbon $period): int
    {
        $start = $period->copy()->startOfMonth();
        $end   = $period->copy()->endOfMonth();

        return Lease::query()
            ->whereHas('property', fn ($q) => $q->whereNotNull('landlord_id'))
            ->where('activated_at', '<=', $end)
            ->where(fn ($q) => $q
                ->whereNull('end_date')
                ->orWhere('end_date', '>=', $start)
            )
            ->whereIn('status', ['active', 'expired', 'terminated'])
            ->count();
    }

    private function billingMonthStatus(Carbon $period): string
    {
        if ($period->isCurrentMonth()) return 'due';
        if ($period->isFuture())       return 'upcoming';
        return 'paid';
    }

    // ── Landlord accounts ─────────────────────────────────────────────────────

    public function landlordShow(User $user): View
    {
        abort_if($user->role !== 'landlord', 404);

        $properties = Property::query()
            ->where('landlord_id', $user->id)
            ->withCount([
                'leases as active_lease_count' => fn ($q) => $q->where('status', 'active'),
                'leases as total_lease_count',
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        $activeLeases = $properties->sum('active_lease_count');
        $mrr          = $activeLeases * 900;

        return view('admin.landlords.show', compact('user', 'properties', 'activeLeases', 'mrr'));
    }

    public function landlords(): View
    {
        $landlords = User::query()
            ->where('role', 'landlord')
            ->withCount([
                'properties as single_count' => fn ($q) => $q->where('occupancy_mode', 'single'),
                'properties as multi_count'  => fn ($q) => $q->where('occupancy_mode', 'multi'),
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        $leaseCounts = DB::table('leases')
            ->join('properties', 'leases.property_id', '=', 'properties.id')
            ->where('leases.status', 'active')
            ->groupBy('properties.landlord_id')
            ->select('properties.landlord_id', DB::raw('count(*) as cnt'))
            ->pluck('cnt', 'landlord_id');

        $stats = [
            'total'              => $landlords->count(),
            'active'             => $landlords->filter(fn ($u) => $u->landlord_account_status === 'active')->count(),
            'inactive'           => $landlords->filter(fn ($u) => $u->landlord_account_status !== 'active')->count(),
            'platform_mrr_minor' => $leaseCounts->sum() * 900,
        ];

        return view('admin.landlords.index', compact('landlords', 'leaseCounts', 'stats'));
    }
}
