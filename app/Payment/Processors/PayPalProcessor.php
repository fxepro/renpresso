<?php

namespace App\Payment\Processors;

use App\Payment\Contracts\PaymentProcessor;
use App\Payment\Data\{
    ChargeRequest, ChargeResponse, ChargeStatus,
    MandateRequest, MandateResponse, RefundResponse, WebhookEvent,
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * PayPal processor — Vault API v2 for recurring rent collection.
 *
 * Mandate model: PayPal Vault (payment method token)
 *   mandate_id stored in PaymentMandate = PayPal vault token ID
 *
 * Flow:
 *   1. setupMandate() → create vault setup token → redirect tenant to PayPal
 *   2. Tenant approves → PayPal redirects to returnUrl with token
 *   3. Controller calls confirmVault() to exchange setup token for payment token
 *   4. createCharge() uses payment token for off-session charges
 *
 * Supports cards and PayPal wallet balances.
 */
class PayPalProcessor implements PaymentProcessor
{
    private string $baseUrl;
    private string $clientId;
    private string $clientSecret;
    private string $webhookId;

    public function __construct()
    {
        $mode = config('services.paypal.mode', 'sandbox');

        $this->baseUrl       = $mode === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';

        $this->clientId      = config('services.paypal.client_id', '');
        $this->clientSecret  = config('services.paypal.client_secret', '');
        $this->webhookId     = config('services.paypal.webhook_id', '');
    }

    // ── Mandate ──────────────────────────────────────────────────────────────

    public function setupMandate(MandateRequest $request): MandateResponse
    {
        // Create a vault setup token — tenant approves on PayPal side
        $response = $this->api('POST', '/v3/vault/setup-tokens', [
            'payment_source' => [
                'paypal' => [
                    'description'      => 'Renpresso rent autopay',
                    'usage_type'       => 'MERCHANT',
                    'customer_type'    => 'CONSUMER',
                    'permit_multiple_payment_tokens' => false,
                    'usage_pattern'    => 'RECURRING_PREPAID',
                ],
            ],
            'customer' => [
                'id' => $request->leaseId,  // use lease ID as customer reference
            ],
        ]);

        if (! isset($response['id'])) {
            return new MandateResponse(mandateId: '', status: 'failed');
        }

        $setupTokenId = $response['id'];

        // Find the approval URL from links
        $approveLink = collect($response['links'] ?? [])
            ->firstWhere('rel', 'approve');

        return new MandateResponse(
            mandateId: $setupTokenId,    // temporary — replaced by confirmVault()
            status:    'pending',
            authUrl:   $approveLink['href'] ?? $request->returnUrl,
        );
    }

    /**
     * Exchange a setup token for a permanent payment token after tenant approval.
     * Call this from the controller that handles PayPal's redirect back.
     *
     * @return string  The permanent vault token ID to store as mandate_id
     */
    public function confirmVault(string $setupTokenId): string
    {
        $response = $this->api('POST', '/v3/vault/payment-tokens', [
            'payment_source' => [
                'token' => [
                    'id'   => $setupTokenId,
                    'type' => 'SETUP_TOKEN',
                ],
            ],
        ]);

        $tokenId = $response['id'] ?? null;

        if (! $tokenId) {
            throw new \RuntimeException('PayPal vault confirmation failed: ' . json_encode($response));
        }

        return $tokenId;
    }

    public function cancelMandate(string $mandateId): void
    {
        $this->api('DELETE', "/v3/vault/payment-tokens/{$mandateId}");
    }

    // ── Charge ───────────────────────────────────────────────────────────────

    public function createCharge(ChargeRequest $request): ChargeResponse
    {
        // Create and auto-capture order using the vaulted payment token
        $response = $this->api('POST', '/v2/checkout/orders', [
            'intent' => 'CAPTURE',
            'payment_source' => [
                'token' => [
                    'id'   => $request->mandateId,
                    'type' => 'PAYMENT_METHOD_TOKEN',
                ],
            ],
            'purchase_units' => [
                [
                    'reference_id' => $request->leaseId,
                    'description'  => $request->description,
                    'amount'       => [
                        'currency_code' => $request->currencyCode,
                        'value'         => number_format($request->amountMinorUnits / 100, 2, '.', ''),
                    ],
                ],
            ],
        ]);

        if (! isset($response['id'])) {
            return new ChargeResponse(
                processorRef: 'unknown',
                status:       'failed',
                errorMessage: $response['message'] ?? 'Order creation failed',
            );
        }

        $orderId = $response['id'];
        $status  = $response['status'] ?? '';

        // If order was created but not captured, capture it now
        if ($status === 'CREATED' || $status === 'APPROVED') {
            $capture  = $this->api('POST', "/v2/checkout/orders/{$orderId}/capture");
            $status   = $capture['status'] ?? $status;
            $captures = $capture['purchase_units'][0]['payments']['captures'] ?? [];
            $captureId = $captures[0]['id'] ?? $orderId;
        } else {
            $captures  = $response['purchase_units'][0]['payments']['captures'] ?? [];
            $captureId = $captures[0]['id'] ?? $orderId;
        }

        return new ChargeResponse(
            processorRef: $captureId,
            status: match ($status) {
                'COMPLETED' => 'success',
                'VOIDED', 'DECLINED' => 'failed',
                default => 'pending',
            },
            errorMessage: null,
        );
    }

    public function getChargeStatus(string $processorRef): ChargeStatus
    {
        // processorRef is a capture ID — need to check via orders
        // PayPal doesn't have a direct capture lookup without order ID,
        // so we use the payments API which does support capture ID lookup
        $response = $this->api('GET', "/v2/payments/captures/{$processorRef}");

        return new ChargeStatus(
            processorRef:     $processorRef,
            status: match ($response['status'] ?? '') {
                'COMPLETED' => 'success',
                'DECLINED', 'FAILED', 'REVERSED' => 'failed',
                'REFUNDED' => 'refunded',
                default => 'pending',
            },
            amountMinorUnits: isset($response['amount']['value'])
                ? (int) round((float) $response['amount']['value'] * 100)
                : null,
            currencyCode: $response['amount']['currency_code'] ?? null,
        );
    }

    public function refund(string $processorRef, int $amountMinorUnits): RefundResponse
    {
        $statusRes = $this->getChargeStatus($processorRef);
        $currency  = $statusRes->currencyCode ?? 'USD';

        $response = $this->api('POST', "/v2/payments/captures/{$processorRef}/refund", [
            'amount' => [
                'value'         => number_format($amountMinorUnits / 100, 2, '.', ''),
                'currency_code' => $currency,
            ],
        ]);

        return new RefundResponse(
            refundRef:        $response['id'] ?? 'unknown',
            status: match ($response['status'] ?? '') {
                'COMPLETED' => 'success',
                'FAILED'    => 'failed',
                default     => 'pending',
            },
            amountMinorUnits: $amountMinorUnits,
        );
    }

    // ── Webhooks ─────────────────────────────────────────────────────────────

    public function verifyWebhookSignature(Request $request): bool
    {
        if (empty($this->webhookId)) {
            return true; // sandbox / dev
        }

        $response = $this->api('POST', '/v1/notifications/verify-webhook-signature', [
            'auth_algo'         => $request->header('PAYPAL-AUTH-ALGO'),
            'cert_url'          => $request->header('PAYPAL-CERT-URL'),
            'transmission_id'   => $request->header('PAYPAL-TRANSMISSION-ID'),
            'transmission_sig'  => $request->header('PAYPAL-TRANSMISSION-SIG'),
            'transmission_time' => $request->header('PAYPAL-TRANSMISSION-TIME'),
            'webhook_id'        => $this->webhookId,
            'webhook_event'     => $request->json()->all(),
        ]);

        return ($response['verification_status'] ?? '') === 'SUCCESS';
    }

    public function normalizeWebhook(Request $request): WebhookEvent
    {
        $payload   = $request->json()->all();
        $eventType = $payload['event_type'] ?? '';
        $resource  = $payload['resource'] ?? [];

        $event = match ($eventType) {
            'PAYMENT.CAPTURE.COMPLETED'            => 'payment.success',
            'PAYMENT.CAPTURE.DENIED',
            'PAYMENT.CAPTURE.DECLINED',
            'PAYMENT.CAPTURE.REVERSED'             => 'payment.failed',
            'VAULT.PAYMENT-TOKEN.CREATED'          => 'mandate.active',
            'VAULT.PAYMENT-TOKEN.DELETED',
            'VAULT.PAYMENT-TOKEN.DELETION-INITIATED' => 'mandate.cancelled',
            default                                => $eventType,
        };

        $amount       = $resource['amount'] ?? [];
        $minor        = isset($amount['value'])
            ? (int) round((float) $amount['value'] * 100)
            : null;
        $currency     = $amount['currency_code'] ?? null;

        // Lease ID is in supplementary_data or custom_id
        $leaseId = $resource['supplementary_data']['related_ids']['order_id'] ?? null
            ?? $resource['custom_id'] ?? null;

        return new WebhookEvent(
            event:            $event,
            processorRef:     $resource['id'] ?? '',
            mandateId:        $resource['id'] ?? null,
            amountMinorUnits: $minor,
            currencyCode:     $currency,
            leaseId:          $leaseId,
            rawPayload:       $payload,
            idempotencyKey:   $payload['id'] ?? uniqid('pp_', true),
        );
    }

    // ── Utility ──────────────────────────────────────────────────────────────

    public function currencyFor(string $countryCode): string
    {
        return match (strtoupper($countryCode)) {
            'CA' => 'CAD',
            'GB' => 'GBP',
            'AU' => 'AUD',
            'EU', 'FR', 'DE', 'ES', 'IT', 'NL', 'PT', 'BE', 'IE', 'AT',
            'CH', 'SE', 'NO', 'DK', 'FI', 'PL', 'CZ', 'RO', 'LU', 'GR' => 'EUR',
            default => 'USD',
        };
    }

    // ── OAuth token (cached) ──────────────────────────────────────────────────

    private function accessToken(): string
    {
        return Cache::remember('paypal_access_token', 30000, function () {
            $response = Http::withBasicAuth($this->clientId, $this->clientSecret)
                ->asForm()
                ->post("{$this->baseUrl}/v1/oauth2/token", [
                    'grant_type' => 'client_credentials',
                ]);

            return $response->json('access_token', '');
        });
    }

    private function api(string $method, string $path, array $body = []): array
    {
        $token = $this->accessToken();

        $req = Http::withToken($token)
            ->withHeaders([
                'PayPal-Request-Id' => uniqid('rmx_', true),
                'Prefer'            => 'return=representation',
            ])
            ->acceptJson();

        $response = match (strtoupper($method)) {
            'POST'   => $req->post($this->baseUrl . $path, $body),
            'GET'    => $req->get($this->baseUrl . $path),
            'DELETE' => $req->delete($this->baseUrl . $path),
            default  => $req->send($method, $this->baseUrl . $path),
        };

        return $response->json() ?? [];
    }
}
