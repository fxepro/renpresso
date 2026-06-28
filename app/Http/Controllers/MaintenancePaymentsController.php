<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesMaintenanceTeam;
use App\Models\MaintenanceInvoice;
use App\Models\MaintenancePaymentReceived;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MaintenancePaymentsController extends Controller
{
    use ResolvesMaintenanceTeam;

    public function index()
    {
        $team = $this->maintenanceTeamOrAbort();

        $payments = $team->paymentsReceived()
            ->whereNotNull('maintenance_invoice_id')
            ->with(['invoice.landlord', 'landlord'])
            ->orderByDesc('paid_on')
            ->orderByDesc('created_at')
            ->get();

        $awaitingInvoices = $team->invoices()
            ->whereIn('status', ['sent', 'partially_paid'])
            ->with('landlord')
            ->orderByDesc('issued_at')
            ->get();

        return view('dashboard.maintenance-portal.payments.index', compact('team', 'payments', 'awaitingInvoices'));
    }

    public function storeForInvoice(Request $request, MaintenanceInvoice $invoice)
    {
        $team = $this->maintenanceTeamOrAbort();
        abort_unless($invoice->maintenance_team_id === $team->id, 404);
        abort_if($invoice->isDraft(), 403, 'Send the invoice before logging a payment.');
        abort_if($invoice->isCancelled(), 403);
        abort_if($invoice->amountDueMinor() <= 0, 403, 'This invoice is already paid in full.');

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01', 'max:9999999'],
            'paid_on' => ['required', 'date'],
            'method' => ['nullable', 'string', 'max:40'],
            'reference' => ['nullable', 'string', 'max:120'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $amountMinor = (int) round((float) $data['amount'] * 100);
        if ($amountMinor > $invoice->amountDueMinor()) {
            throw ValidationException::withMessages([
                'amount' => 'Amount cannot exceed the balance due ('.$invoice->formattedAmountDue().').',
            ]);
        }

        DB::transaction(function () use ($team, $invoice, $data, $amountMinor, $request) {
            $payment = $team->paymentsReceived()->create([
                'maintenance_invoice_id' => $invoice->id,
                'landlord_id'            => $invoice->landlord_id,
                'amount_minor'           => $amountMinor,
                'currency_code'          => $invoice->currency_code,
                'paid_on'                => $data['paid_on'],
                'method'                 => $data['method'] ?? null,
                'reference'              => $data['reference'] ?? null,
                'notes'                  => $data['notes'] ?? null,
            ]);

            MaintenanceInvoice::syncPaymentStatus($invoice->fresh());
            $invoice->recordEvent('payment_linked', [
                'payment_id'   => $payment->id,
                'amount_minor' => $payment->amount_minor,
            ], $request->user());
        });

        return back()->with('success', 'Payment logged against '.$invoice->invoice_number.'.');
    }

    public function destroy(Request $request, MaintenancePaymentReceived $payment)
    {
        $team = $this->maintenanceTeamOrAbort();
        abort_unless($payment->maintenance_team_id === $team->id, 404);
        abort_unless($payment->maintenance_invoice_id, 403, 'Only invoice payments can be removed here.');

        $invoiceId = $payment->maintenance_invoice_id;
        $payment->delete();

        if ($invoiceId) {
            $invoice = MaintenanceInvoice::find($invoiceId);
            if ($invoice) {
                MaintenanceInvoice::syncPaymentStatus($invoice);
            }
        }

        return back()->with('success', 'Payment removed.');
    }
}
