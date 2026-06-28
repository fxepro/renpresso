<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceInvoice;
use App\Models\MaintenancePaymentReceived;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(Auth::user()->isLandlord(), 403);

        $tab = $request->query('tab', 'rent');
        if (! in_array($tab, ['rent', 'maintenance'], true)) {
            $tab = 'rent';
        }

        $uid = Auth::id();

        $rentPayments = null;
        $thisMonth = 0;
        $thisYear = 0;
        $pending = 0;
        $failed = 0;

        if ($tab === 'rent') {
            $rentPayments = Payment::whereHas('lease.property', fn ($q) => $q->where('landlord_id', $uid))
                ->with(['lease.property', 'lease.tenant'])
                ->orderByDesc('due_date')
                ->paginate(20)
                ->withQueryString();

            $thisMonth = Payment::whereHas('lease.property', fn ($q) => $q->where('landlord_id', $uid))
                ->where('status', 'success')
                ->whereMonth('collected_at', now()->month)
                ->whereYear('collected_at', now()->year)
                ->sum('home_amount_minor_units');
            $thisYear = Payment::whereHas('lease.property', fn ($q) => $q->where('landlord_id', $uid))
                ->where('status', 'success')
                ->whereYear('collected_at', now()->year)
                ->sum('home_amount_minor_units');
            $pending = Payment::whereHas('lease.property', fn ($q) => $q->where('landlord_id', $uid))->where('status', 'pending')->count();
            $failed = Payment::whereHas('lease.property', fn ($q) => $q->where('landlord_id', $uid))->where('status', 'failed')->count();
        }

        $awaitingApprovalCount = MaintenanceInvoice::query()
            ->where('landlord_id', $uid)
            ->visibleToLandlord()
            ->awaitingLandlordApproval()
            ->get()
            ->filter(fn (MaintenanceInvoice $inv) => $inv->amountDueMinor() > 0)
            ->count();

        $maintenanceInvoices = null;
        $maintPaidThisMonth = 0;
        $maintPaidThisYear = 0;

        if ($tab === 'maintenance') {
            $maintenanceInvoices = MaintenanceInvoice::query()
                ->where('landlord_id', $uid)
                ->visibleToLandlord()
                ->with(['team', 'property'])
                ->orderByDesc('sent_at')
                ->orderByDesc('created_at')
                ->paginate(20)
                ->withQueryString();

            $maintPaidThisMonth = (int) MaintenancePaymentReceived::query()
                ->where('landlord_id', $uid)
                ->whereNotNull('maintenance_invoice_id')
                ->whereMonth('paid_on', now()->month)
                ->whereYear('paid_on', now()->year)
                ->sum('amount_minor');

            $maintPaidThisYear = (int) MaintenancePaymentReceived::query()
                ->where('landlord_id', $uid)
                ->whereNotNull('maintenance_invoice_id')
                ->whereYear('paid_on', now()->year)
                ->sum('amount_minor');
        }

        return view('dashboard.payments.index', compact(
            'tab',
            'rentPayments',
            'thisMonth',
            'thisYear',
            'pending',
            'failed',
            'awaitingApprovalCount',
            'maintenanceInvoices',
            'maintPaidThisMonth',
            'maintPaidThisYear',
        ));
    }
}
