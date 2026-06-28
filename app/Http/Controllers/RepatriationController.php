<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessWebhookJob;
use App\Models\{Lease, Payment, Property, WaitlistEmail, MaintenanceRequest, Message};
use App\Payment\ProcessorFactory;
use App\Services\{PaymentService, LedgerService};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

// ─────────────────────────────────────────────────────────────
// REPATRIATION CONTROLLER
// Landlord manually logs cross-border transfers
// ─────────────────────────────────────────────────────────────
class RepatriationController extends Controller
{
    public function store(Request $request, Property $property)
    {
        $this->authorize('update', $property);

        $validated = $request->validate([
            'amount_minor_units'  => 'required|integer|min:1',
            'currency_code'       => 'required|string|size:3',
            'home_currency_code'  => 'required|string|size:3',
            'home_amount_minor_units' => 'required|integer|min:1',
            'repatriated_on'      => 'required|date',
            'notes'               => 'nullable|string|max:1000',
        ]);

        $fxRate = (int)round(
            ($validated['home_amount_minor_units'] / $validated['amount_minor_units']) * 1_000_000
        );

        \App\Models\RepatriationLog::create(array_merge($validated, [
            'landlord_id'       => Auth::id(),
            'property_id'       => $property->id,
            'fx_rate_snapshot'  => $fxRate,
        ]));

        return back()->with('success', 'Repatriation logged.');
    }
}
