<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceInvoice;
use App\Models\MaintenanceInvoiceAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class LandlordMaintenanceInvoiceController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(Auth::user()->isLandlord(), 403);

        $query = Auth::user()->maintenanceInvoicesReceived()
            ->visibleToLandlord()
            ->with(['team', 'property'])
            ->orderByDesc('sent_at')
            ->orderByDesc('created_at');

        if ($status = $request->query('status')) {
            if ($status === 'awaiting') {
                $query->awaitingLandlordApproval();
            } else {
                $query->where('status', $status);
            }
        }

        $invoices = $query->get();

        return view('dashboard.invoices.index', compact('invoices'));
    }

    public function show(MaintenanceInvoice $invoice)
    {
        abort_unless(Auth::user()->isLandlord(), 403);
        $this->authorizeLandlord($invoice);

        $invoice->load([
            'team',
            'property',
            'maintenanceRequest',
            'lines',
            'attachments',
            'paymentsReceived',
            'events.actor',
            'landlordApprover',
        ]);

        return view('dashboard.invoices.show', compact('invoice'));
    }

    public function approve(Request $request, MaintenanceInvoice $invoice)
    {
        abort_unless(Auth::user()->isLandlord(), 403);
        $this->authorizeLandlord($invoice);

        if (! $invoice->needsLandlordApproval()) {
            return back()->with('error', 'This invoice cannot be approved right now.');
        }

        MaintenanceInvoice::processLandlordApproval($invoice, $request->user());

        return redirect()
            ->route('landlord.invoices.show', $invoice)
            ->with('success', 'Approved. Payment has been processed through your linked billing account.');
    }

    public function attachmentFile(MaintenanceInvoiceAttachment $attachment)
    {
        abort_unless(Auth::user()->isLandlord(), 403);

        $invoice = $attachment->invoice;
        abort_unless($invoice && $invoice->landlord_id === Auth::id(), 404);

        if (! Storage::disk('local')->exists($attachment->file_path)) {
            abort(404);
        }

        return Storage::disk('local')->response(
            $attachment->file_path,
            $attachment->original_filename,
            ['Content-Type' => $attachment->mime_type ?: Storage::disk('local')->mimeType($attachment->file_path)]
        );
    }

    private function authorizeLandlord(MaintenanceInvoice $invoice): void
    {
        abort_unless($invoice->landlord_id === Auth::id(), 404);
        abort_if($invoice->isDraft(), 404);
    }
}
