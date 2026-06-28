<?php

namespace App\Payment\Contracts;

use App\Payment\Data\ChargeRequest;
use App\Payment\Data\ChargeResponse;
use App\Payment\Data\ChargeStatus;
use App\Payment\Data\MandateRequest;
use App\Payment\Data\MandateResponse;
use App\Payment\Data\RefundResponse;
use App\Payment\Data\WebhookEvent;
use Illuminate\Http\Request;

interface PaymentProcessor
{
    /**
     * Initiate a one-time or recurring charge.
     */
    public function createCharge(ChargeRequest $request): ChargeResponse;

    /**
     * Get the current status of a charge by processor reference.
     */
    public function getChargeStatus(string $processorRef): ChargeStatus;

    /**
     * Issue a full or partial refund.
     */
    public function refund(string $processorRef, int $amountMinorUnits): RefundResponse;

    /**
     * Set up a recurring payment mandate (direct debit, UPI autopay, etc.).
     */
    public function setupMandate(MandateRequest $request): MandateResponse;

    /**
     * Cancel an active mandate.
     */
    public function cancelMandate(string $mandateId): void;

    /**
     * Normalise the processor's inbound webhook payload
     * into a standard WebhookEvent DTO.
     */
    public function normalizeWebhook(Request $request): WebhookEvent;

    /**
     * Verify the webhook signature to confirm it came from the processor.
     * Throw an exception if invalid.
     */
    public function verifyWebhookSignature(Request $request): bool;

    /**
     * Return the ISO 4217 currency code this processor handles
     * for a given country code.
     */
    public function currencyFor(string $countryCode): string;
}
