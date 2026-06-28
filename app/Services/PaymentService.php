<?php

namespace App\Services;

use App\Models\{Lease, Payment, PaymentEvent, PaymentMandate};
use App\Payment\ProcessorFactory;
use App\Payment\Data\{ChargeRequest, WebhookEvent};
use Brick\Money\Money;
use Brick\Money\Currency;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    public function __construct(
        private readonly LedgerService $ledger,
        private readonly FxService $fx,
    ) {}

    /**
     * Collect rent for a lease. Called by the CollectRentJob cron.
     */
    public function collectRent(Lease $lease): Payment
    {
        $mandate   = $lease->activeMandate();
        $processor = ProcessorFactory::for($lease->property->country_code);
        $landlord  = $lease->property->landlord;

        // Snapshot FX rate at this exact moment — never recalculated later
        $fxRate      = $this->fx->rateToHome($lease->currency_code, $landlord->home_currency);
        $homeAmount  = (int)round($lease->rent_minor_units * ($fxRate / 1_000_000));

        // Create pending payment record first
        $payment = Payment::create([
            'lease_id'              => $lease->id,
            'mandate_id'            => $mandate->id,
            'processor_ref'         => 'pending_' . uniqid(),
            'amount_minor_units'    => $lease->rent_minor_units,
            'currency_code'         => $lease->currency_code,
            'fx_rate_snapshot'      => $fxRate,
            'home_currency_code'    => $landlord->home_currency,
            'home_amount_minor_units' => $homeAmount,
            'status'                => 'pending',
            'due_date'              => now(),
        ]);

        $chargeRequest = new ChargeRequest(
            mandateId:        $mandate->processor_mandate_id,
            amountMinorUnits: $lease->rent_minor_units,
            currencyCode:     $lease->currency_code,
            tenantId:         $lease->tenant_id,
            leaseId:          $lease->id,
            description:      "Rent - {$lease->property->name}",
            metadata:         ['landlord_id' => $landlord->id],
        );

        $response = $processor->createCharge($chargeRequest);

        // Update with real processor reference
        $payment->update([
            'processor_ref' => $response->processorRef,
            'status'        => $response->status,
        ]);

        if ($response->status === 'success') {
            $payment->update(['collected_at' => now()]);
        }

        $this->logEvent($payment, 'charge.created', ['response' => (array)$response]);

        return $payment;
    }

    /**
     * Tenant-initiated pay for the current pending rent (or the open due row).
     */
    public function payCurrentRent(Lease $lease, ?Payment $payment = null): Payment
    {
        $mandate = $lease->activeMandate();
        if (! $mandate) {
            throw new \RuntimeException('Rent payments require an active payment authorization on your lease. Contact your landlord or add a method under Account → Payment.');
        }

        $payment = $payment ?? Payment::query()
            ->where('lease_id', $lease->id)
            ->where('status', 'pending')
            ->orderBy('due_date')
            ->first();

        if (! $payment) {
            $landlord = $lease->property->landlord;
            $fxRate = $this->fx->rateToHome($lease->currency_code, $landlord->home_currency);
            $homeAmount = (int) round($lease->rent_minor_units * ($fxRate / 1_000_000));

            $payment = Payment::create([
                'lease_id'                => $lease->id,
                'mandate_id'              => $mandate->id,
                'processor_ref'           => 'pending_'.uniqid(),
                'amount_minor_units'      => $lease->rent_minor_units,
                'currency_code'           => $lease->currency_code,
                'fx_rate_snapshot'        => $fxRate,
                'home_currency_code'      => $landlord->home_currency,
                'home_amount_minor_units' => $homeAmount,
                'status'                  => 'pending',
                'due_date'                => $lease->nextRentDueDate(),
            ]);
        }

        $processor = ProcessorFactory::for($lease->property->country_code);
        $landlord = $lease->property->landlord;

        $chargeRequest = new ChargeRequest(
            mandateId:        $mandate->processor_mandate_id,
            amountMinorUnits: $payment->amount_minor_units,
            currencyCode:     $payment->currency_code,
            tenantId:         $lease->tenant_id,
            leaseId:          $lease->id,
            description:      "Rent - {$lease->property->name}",
            metadata:         ['landlord_id' => $landlord->id, 'payment_id' => $payment->id],
        );

        $response = $processor->createCharge($chargeRequest);

        $payment->update([
            'mandate_id'    => $mandate->id,
            'processor_ref' => $response->processorRef,
            'status'        => $response->status,
            'collected_at'  => $response->status === 'success' ? now() : null,
        ]);

        $this->logEvent($payment, 'charge.tenant_initiated', ['response' => (array) $response]);

        return $payment->fresh();
    }

    /**
     * Handle a normalised webhook event from any processor.
     * Called by ProcessWebhookJob — always idempotent.
     */
    public function handleWebhookEvent(WebhookEvent $event): void
    {
        // Idempotency check — silently discard duplicates
        if (PaymentEvent::where('idempotency_key', $event->idempotencyKey)->exists()) {
            Log::info("Duplicate webhook discarded: {$event->idempotencyKey}");
            return;
        }

        DB::transaction(function () use ($event) {
            $payment = Payment::where('processor_ref', $event->processorRef)->first();

            if (! $payment && $event->leaseId) {
                $payment = Payment::where('lease_id', $event->leaseId)
                    ->where('status', 'pending')
                    ->latest()
                    ->first();
            }

            if (! $payment) {
                Log::warning("No payment found for webhook event: {$event->processorRef}");
                return;
            }

            match ($event->event) {
                'payment.success'    => $this->onPaymentSuccess($payment, $event),
                'payment.failed'     => $this->onPaymentFailed($payment, $event),
                'mandate.active'     => $this->onMandateActive($payment, $event),
                'mandate.cancelled'  => $this->onMandateCancelled($payment, $event),
                default              => null,
            };

            $this->logEvent($payment, $event->event, $event->rawPayload, $event->idempotencyKey);
        });
    }

    private function onPaymentSuccess(Payment $payment, WebhookEvent $event): void
    {
        $payment->update(['status' => 'success', 'collected_at' => now()]);
        // TODO: notify landlord and tenant
    }

    private function onPaymentFailed(Payment $payment, WebhookEvent $event): void
    {
        $payment->increment('retry_count');
        if ($payment->retry_count >= 2) {
            $payment->update(['status' => 'failed']);
            // TODO: trigger arrears notification
        }
    }

    private function onMandateActive(Payment $payment, WebhookEvent $event): void
    {
        PaymentMandate::where('lease_id', $payment->lease_id)
            ->where('status', 'pending')
            ->update(['status' => 'active', 'authorised_at' => now()]);

        $payment->lease->update(['status' => 'active', 'activated_at' => now()]);
    }

    private function onMandateCancelled(Payment $payment, WebhookEvent $event): void
    {
        if ($event->mandateId) {
            PaymentMandate::where('processor_mandate_id', $event->mandateId)
                ->update(['status' => 'cancelled', 'cancelled_at' => now()]);
        }
        // TODO: notify landlord
    }

    private function logEvent(Payment $payment, string $type, array $payload, string $key = null): void
    {
        PaymentEvent::create([
            'payment_id'        => $payment->id,
            'event_type'        => $type,
            'idempotency_key'   => $key ?? uniqid('evt_'),
            'processor_payload' => $payload,
            'occurred_at'       => now(),
        ]);
    }
}
