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
// WEBHOOK CONTROLLER
// One endpoint per processor — immediately queued, never processed inline.
// ─────────────────────────────────────────────────────────────
class WebhookController extends Controller
{
    public function handle(Request $request, string $processor): \Illuminate\Http\Response
    {
        // Verify signature synchronously before queuing
        try {
            $proc = ProcessorFactory::for(
                collect(config('countries'))
                    ->where('processor', $processor)
                    ->keys()
                    ->first() ?? 'US'
            );

            if (! $proc->verifyWebhookSignature($request)) {
                return response('Unauthorized', 401);
            }
        } catch (\Exception $e) {
            return response('Bad Request', 400);
        }

        // Dispatch immediately — never process webhooks inline
        ProcessWebhookJob::dispatch(
            $processor,
            $request->all(),
            $request->headers->all(),
        );

        return response('OK', 200);
    }
}
