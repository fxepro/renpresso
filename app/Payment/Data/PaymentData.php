<?php

namespace App\Payment\Data;

/**
 * Inbound request to create a charge.
 */
class ChargeRequest
{
    public function __construct(
        public readonly string $mandateId,
        public readonly int    $amountMinorUnits,  // always in minor units (paise, pence, cents)
        public readonly string $currencyCode,       // ISO 4217
        public readonly string $tenantId,
        public readonly string $leaseId,
        public readonly string $description,
        public readonly array  $metadata = [],
    ) {}
}

/**
 * Response from a charge creation.
 */
class ChargeResponse
{
    public function __construct(
        public readonly string $processorRef,       // processor's own transaction ID
        public readonly string $status,             // pending | success | failed
        public readonly ?string $errorCode = null,
        public readonly ?string $errorMessage = null,
    ) {}
}

/**
 * Status check response.
 */
class ChargeStatus
{
    public function __construct(
        public readonly string $processorRef,
        public readonly string $status,             // pending | success | failed | refunded
        public readonly ?int   $amountMinorUnits = null,
        public readonly ?string $currencyCode = null,
    ) {}
}

/**
 * Inbound request to set up a recurring mandate.
 */
class MandateRequest
{
    public function __construct(
        public readonly string $tenantEmail,
        public readonly string $tenantName,
        public readonly string $tenantPhone,
        public readonly string $currencyCode,
        public readonly int    $amountMinorUnits,
        public readonly int    $dueDayOfMonth,     // 1–28
        public readonly string $leaseId,
        public readonly string $returnUrl,          // redirect after mandate auth
        public readonly array  $metadata = [],
    ) {}
}

/**
 * Response from mandate setup.
 */
class MandateResponse
{
    public function __construct(
        public readonly string  $mandateId,
        public readonly string  $status,            // pending | active | failed
        public readonly ?string $authUrl = null,    // redirect tenant here to authorise
    ) {}
}

/**
 * Refund response.
 */
class RefundResponse
{
    public function __construct(
        public readonly string  $refundRef,
        public readonly string  $status,            // pending | success | failed
        public readonly int     $amountMinorUnits,
    ) {}
}

/**
 * Normalised inbound webhook event.
 * All processors produce this standard shape after normalizeWebhook().
 */
class WebhookEvent
{
    public function __construct(
        public readonly string  $event,             // payment.success | payment.failed | mandate.active | mandate.cancelled
        public readonly string  $processorRef,
        public readonly ?string $mandateId,
        public readonly ?int    $amountMinorUnits,
        public readonly ?string $currencyCode,
        public readonly ?string $leaseId,
        public readonly array   $rawPayload,
        public readonly string  $idempotencyKey,    // used to deduplicate retries
    ) {}
}
