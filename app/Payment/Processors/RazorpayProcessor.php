<?php

namespace App\Payment\Processors;

use App\Payment\Contracts\PaymentProcessor;
use App\Payment\Data\{ChargeRequest, ChargeResponse, ChargeStatus, MandateRequest, MandateResponse, RefundResponse, WebhookEvent};
use Illuminate\Http\Request;
use Razorpay\Api\Api;

class RazorpayProcessor implements PaymentProcessor
{
    private Api $api;

    public function __construct()
    {
        $this->api = new Api(
            config('services.razorpay.key'),
            config('services.razorpay.secret'),
        );
    }

    public function createCharge(ChargeRequest $request): ChargeResponse
    {
        try {
            // Razorpay: charge against an existing subscription/mandate
            $payment = $this->api->subscription->fetch($request->mandateId)->charge([
                'amount' => $request->amountMinorUnits, // already in paise
            ]);

            return new ChargeResponse(
                processorRef: $payment['payment_id'],
                status: 'pending', // Razorpay charges are async
            );
        } catch (\Exception $e) {
            return new ChargeResponse(
                processorRef: 'unknown',
                status: 'failed',
                errorMessage: $e->getMessage(),
            );
        }
    }

    public function getChargeStatus(string $processorRef): ChargeStatus
    {
        $payment = $this->api->payment->fetch($processorRef);
        return new ChargeStatus(
            processorRef: $processorRef,
            status: $this->mapStatus($payment['status']),
            amountMinorUnits: $payment['amount'],
            currencyCode: $payment['currency'],
        );
    }

    public function refund(string $processorRef, int $amountMinorUnits): RefundResponse
    {
        $refund = $this->api->payment->fetch($processorRef)->refund([
            'amount' => $amountMinorUnits,
        ]);
        return new RefundResponse(
            refundRef: $refund['id'],
            status: $refund['status'] === 'processed' ? 'success' : 'pending',
            amountMinorUnits: $refund['amount'],
        );
    }

    public function setupMandate(MandateRequest $request): MandateResponse
    {
        // Razorpay: create a subscription for recurring UPI/NACH mandate
        $plan = $this->api->plan->create([
            'period'   => 'monthly',
            'interval' => 1,
            'item'     => [
                'name'     => 'Monthly Rent',
                'amount'   => $request->amountMinorUnits,
                'currency' => 'INR',
            ],
        ]);

        $subscription = $this->api->subscription->create([
            'plan_id'         => $plan['id'],
            'total_count'     => 600, // 50 years max
            'customer_notify' => 1,
            'notify_info'     => [
                'notify_phone' => $request->tenantPhone,
                'notify_email' => $request->tenantEmail,
            ],
            'notes' => [
                'lease_id' => $request->leaseId,
            ],
        ]);

        return new MandateResponse(
            mandateId: $subscription['id'],
            status: 'pending',
            authUrl: $subscription['short_url'],
        );
    }

    public function cancelMandate(string $mandateId): void
    {
        $this->api->subscription->fetch($mandateId)->cancel(['cancel_at_cycle_end' => 0]);
    }

    public function normalizeWebhook(Request $request): WebhookEvent
    {
        $payload = $request->all();
        $event   = $payload['event'] ?? '';
        $entity  = $payload['payload']['payment']['entity'] ?? $payload['payload']['subscription']['entity'] ?? [];

        $eventType = match ($event) {
            'payment.captured'        => 'payment.success',
            'payment.failed'          => 'payment.failed',
            'subscription.activated'  => 'mandate.active',
            'subscription.cancelled'  => 'mandate.cancelled',
            default => $event,
        };

        return new WebhookEvent(
            event:            $eventType,
            processorRef:     $entity['id'] ?? '',
            mandateId:        $entity['subscription_id'] ?? null,
            amountMinorUnits: $entity['amount'] ?? null,
            currencyCode:     $entity['currency'] ?? 'INR',
            leaseId:          $entity['notes']['lease_id'] ?? null,
            rawPayload:       $payload,
            idempotencyKey:   $payload['event'] . '_' . ($entity['id'] ?? uniqid()),
        );
    }

    public function verifyWebhookSignature(Request $request): bool
    {
        $signature = $request->header('X-Razorpay-Signature');
        $body      = $request->getContent();
        $expected  = hash_hmac('sha256', $body, config('services.razorpay.webhook_secret'));
        return hash_equals($expected, $signature ?? '');
    }

    public function currencyFor(string $countryCode): string
    {
        return 'INR';
    }

    private function mapStatus(string $rzpStatus): string
    {
        return match ($rzpStatus) {
            'captured'   => 'success',
            'failed'     => 'failed',
            default      => 'pending',
        };
    }
}
