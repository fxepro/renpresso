<?php

namespace App\Payment\Processors;

use App\Payment\Contracts\PaymentProcessor;
use App\Payment\Data\{ChargeRequest, ChargeResponse, ChargeStatus, MandateRequest, MandateResponse, RefundResponse, WebhookEvent};
use Illuminate\Http\Request;
use Stripe\StripeClient;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

class StripeProcessor implements PaymentProcessor
{
    private StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('services.stripe.secret'));
    }

    public function createCharge(ChargeRequest $request): ChargeResponse
    {
        try {
            $intent = $this->stripe->paymentIntents->create([
                'amount'               => $request->amountMinorUnits,
                'currency'             => strtolower($request->currencyCode),
                'payment_method_types' => ['sepa_debit', 'bacs_debit', 'ach_debit', 'card'],
                'customer'             => $request->metadata['stripe_customer_id'] ?? null,
                'payment_method'       => $request->mandateId,
                'confirm'              => true,
                'off_session'          => true,
                'metadata'             => [
                    'lease_id'  => $request->leaseId,
                    'tenant_id' => $request->tenantId,
                ],
                'description' => $request->description,
            ]);

            return new ChargeResponse(
                processorRef: $intent->id,
                status: $this->mapStatus($intent->status),
            );
        } catch (\Stripe\Exception\CardException $e) {
            return new ChargeResponse(
                processorRef: $e->getStripeParam() ?? 'unknown',
                status: 'failed',
                errorCode: $e->getStripeCode(),
                errorMessage: $e->getMessage(),
            );
        }
    }

    public function getChargeStatus(string $processorRef): ChargeStatus
    {
        $intent = $this->stripe->paymentIntents->retrieve($processorRef);
        return new ChargeStatus(
            processorRef: $processorRef,
            status: $this->mapStatus($intent->status),
            amountMinorUnits: $intent->amount,
            currencyCode: strtoupper($intent->currency),
        );
    }

    public function refund(string $processorRef, int $amountMinorUnits): RefundResponse
    {
        $refund = $this->stripe->refunds->create([
            'payment_intent' => $processorRef,
            'amount'         => $amountMinorUnits,
        ]);
        return new RefundResponse(
            refundRef: $refund->id,
            status: $refund->status === 'succeeded' ? 'success' : $refund->status,
            amountMinorUnits: $refund->amount,
        );
    }

    public function setupMandate(MandateRequest $request): MandateResponse
    {
        // Create or retrieve customer
        $customers = $this->stripe->customers->search([
            'query' => "email:'{$request->tenantEmail}'",
        ]);
        $customer = $customers->data[0] ?? $this->stripe->customers->create([
            'email' => $request->tenantEmail,
            'name'  => $request->tenantName,
            'phone' => $request->tenantPhone,
        ]);

        // Create a SetupIntent for the mandate
        $setupIntent = $this->stripe->setupIntents->create([
            'customer'             => $customer->id,
            'payment_method_types' => ['sepa_debit', 'bacs_debit', 'ach_debit'],
            'usage'                => 'off_session',
            'metadata'             => [
                'lease_id' => $request->leaseId,
            ],
        ]);

        return new MandateResponse(
            mandateId: $customer->id . '|' . $setupIntent->payment_method,
            status: 'pending',
            authUrl: $request->returnUrl . '?setup_intent=' . $setupIntent->id,
        );
    }

    public function cancelMandate(string $mandateId): void
    {
        // Detach the payment method
        [$customerId, $paymentMethodId] = explode('|', $mandateId);
        if ($paymentMethodId) {
            $this->stripe->paymentMethods->detach($paymentMethodId);
        }
    }

    public function normalizeWebhook(Request $request): WebhookEvent
    {
        $payload = $request->all();
        $type    = $payload['type'] ?? '';
        $object  = $payload['data']['object'] ?? [];

        $eventType = match (true) {
            str_contains($type, 'payment_intent.succeeded')             => 'payment.success',
            str_contains($type, 'payment_intent.payment_failed')        => 'payment.failed',
            str_contains($type, 'setup_intent.succeeded')               => 'mandate.active',
            str_contains($type, 'mandate.updated') => 'mandate.cancelled',
            default => $type,
        };

        return new WebhookEvent(
            event:            $eventType,
            processorRef:     $object['id'] ?? '',
            mandateId:        $object['payment_method'] ?? null,
            amountMinorUnits: $object['amount'] ?? null,
            currencyCode:     isset($object['currency']) ? strtoupper($object['currency']) : null,
            leaseId:          $object['metadata']['lease_id'] ?? null,
            rawPayload:       $payload,
            idempotencyKey:   $payload['id'] ?? uniqid(),
        );
    }

    public function verifyWebhookSignature(Request $request): bool
    {
        try {
            Webhook::constructEvent(
                $request->getContent(),
                $request->header('Stripe-Signature'),
                config('services.stripe.webhook_secret'),
            );
            return true;
        } catch (SignatureVerificationException) {
            return false;
        }
    }

    public function currencyFor(string $countryCode): string
    {
        return config('countries')[strtoupper($countryCode)]['currency'] ?? 'USD';
    }

    private function mapStatus(string $stripeStatus): string
    {
        return match ($stripeStatus) {
            'succeeded'         => 'success',
            'requires_payment_method',
            'canceled'          => 'failed',
            default             => 'pending',
        };
    }
}
