<?php

namespace App\Payment\Processors;

use App\Payment\Contracts\PaymentProcessor;
use App\Payment\Data\{
    ChargeRequest, ChargeResponse, ChargeStatus,
    MandateRequest, MandateResponse, RefundResponse, WebhookEvent,
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

/**
 * Square processor — card-on-file collection for US and CA.
 *
 * Mandate model: Square Customer + Card on File
 *   mandate_id stored in PaymentMandate = "<customer_id>|<card_id>"
 *
 * Does NOT support ACH / bank debit. Card only.
 */
class SquareProcessor implements PaymentProcessor
{
    private string $baseUrl;
    private string $accessToken;
    private string $locationId;
    private string $webhookSignatureKey;

    public function __construct()
    {
        $env = config('services.square.environment', 'sandbox');

        $this->baseUrl            = $env === 'production'
            ? 'https://connect.squareup.com/v2'
            : 'https://connect.squareupsandbox.com/v2';

        $this->accessToken        = config('services.square.access_token', '');
        $this->locationId         = config('services.square.location_id', '');
        $this->webhookSignatureKey = config('services.square.webhook_key', '');
    }

    // ── Mandate ─────────────────────────────────────────────────────────────

    /**
     * Create a Square Customer and return an auth URL that redirects the
     * tenant to tokenise their card via Square Web Payments SDK.
     *
     * In this server-side step we only create the customer record.
     * The card nonce is submitted by the tenant's browser and POSTed back
     * to our /payment-methods/square/confirm endpoint, which calls
     * confirmCard() (not part of this interface — called by the controller).
     */
    public function setupMandate(MandateRequest $request): MandateResponse
    {
        $response = $this->api('POST', '/customers', [
            'given_name'    => explode(' ', $request->tenantName)[0] ?? $request->tenantName,
            'family_name'   => explode(' ', $request->tenantName, 2)[1] ?? '',
            'email_address' => $request->tenantEmail,
            'phone_number'  => $request->tenantPhone,
            'reference_id'  => $request->leaseId,
        ]);

        if (! isset($response['customer']['id'])) {
            return new MandateResponse(
                mandateId: '',
                status:    'failed',
            );
        }

        $customerId = $response['customer']['id'];

        // Return auth URL pointing to our card entry page with the customer ID embedded.
        $authUrl = $request->returnUrl . '?square_customer_id=' . $customerId;

        return new MandateResponse(
            mandateId: $customerId,   // partial — card ID appended after card entry
            status:    'pending',
            authUrl:   $authUrl,
        );
    }

    /**
     * Called by the controller after Square Web Payments SDK returns a card nonce.
     * Vaults the card under the existing customer and returns the full mandate ID.
     *
     * @return string  "<customer_id>|<card_id>"
     */
    public function vaultCard(string $customerId, string $cardNonce, string $cardholderName): string
    {
        $response = $this->api('POST', '/cards', [
            'idempotency_key' => uniqid('rmx_card_', true),
            'source_id'       => $cardNonce,
            'card'            => [
                'customer_id'    => $customerId,
                'cardholder_name'=> $cardholderName,
            ],
        ]);

        $cardId = $response['card']['id'] ?? null;

        if (! $cardId) {
            throw new \RuntimeException('Square card vault failed: ' . json_encode($response));
        }

        return "{$customerId}|{$cardId}";
    }

    public function cancelMandate(string $mandateId): void
    {
        [, $cardId] = $this->splitMandateId($mandateId);

        $this->api('POST', "/cards/{$cardId}/disable");
    }

    // ── Charge ───────────────────────────────────────────────────────────────

    public function createCharge(ChargeRequest $request): ChargeResponse
    {
        [, $cardId] = $this->splitMandateId($request->mandateId);

        $response = $this->api('POST', '/payments', [
            'idempotency_key' => uniqid('rmx_pay_', true),
            'source_id'       => $cardId,
            'amount_money'    => [
                'amount'   => $request->amountMinorUnits,
                'currency' => $request->currencyCode,
            ],
            'location_id'     => $this->locationId,
            'note'            => $request->description,
            'reference_id'    => $request->leaseId,
            'autocomplete'    => true,
        ]);

        $payment = $response['payment'] ?? [];
        $status  = match ($payment['status'] ?? '') {
            'COMPLETED' => 'success',
            'FAILED'    => 'failed',
            default     => 'pending',
        };

        return new ChargeResponse(
            processorRef: $payment['id'] ?? 'unknown',
            status:       $status,
            errorMessage: $response['errors'][0]['detail'] ?? null,
        );
    }

    public function getChargeStatus(string $processorRef): ChargeStatus
    {
        $response = $this->api('GET', "/payments/{$processorRef}");
        $payment  = $response['payment'] ?? [];

        return new ChargeStatus(
            processorRef:     $processorRef,
            status:           match ($payment['status'] ?? '') {
                'COMPLETED' => 'success',
                'FAILED'    => 'failed',
                default     => 'pending',
            },
            amountMinorUnits: $payment['amount_money']['amount'] ?? null,
            currencyCode:     $payment['amount_money']['currency'] ?? null,
        );
    }

    public function refund(string $processorRef, int $amountMinorUnits): RefundResponse
    {
        $statusRes = $this->getChargeStatus($processorRef);
        $currency  = $statusRes->currencyCode ?? 'USD';

        $response = $this->api('POST', '/refunds', [
            'idempotency_key' => uniqid('rmx_refund_', true),
            'payment_id'      => $processorRef,
            'amount_money'    => [
                'amount'   => $amountMinorUnits,
                'currency' => $currency,
            ],
        ]);

        $refund = $response['refund'] ?? [];

        return new RefundResponse(
            refundRef:        $refund['id'] ?? 'unknown',
            status:           match ($refund['status'] ?? '') {
                'COMPLETED' => 'success',
                'FAILED'    => 'failed',
                default     => 'pending',
            },
            amountMinorUnits: $refund['amount_money']['amount'] ?? $amountMinorUnits,
        );
    }

    // ── Webhooks ─────────────────────────────────────────────────────────────

    public function verifyWebhookSignature(Request $request): bool
    {
        if (empty($this->webhookSignatureKey)) {
            return true; // dev/sandbox — skip
        }

        $notificationUrl = url('/webhooks/square');
        $body            = $request->getContent();
        $squareSig       = $request->header('x-square-hmacsha256-signature', '');

        $expected = base64_encode(
            hash_hmac('sha256', $notificationUrl . $body, $this->webhookSignatureKey, true)
        );

        return hash_equals($expected, $squareSig);
    }

    public function normalizeWebhook(Request $request): WebhookEvent
    {
        $payload  = $request->json()->all();
        $type     = $payload['type'] ?? '';
        $data     = $payload['data']['object'] ?? [];

        $event = match (true) {
            str_starts_with($type, 'payment.') && ($data['payment']['status'] ?? '') === 'COMPLETED'
                => 'payment.success',
            str_starts_with($type, 'payment.') && ($data['payment']['status'] ?? '') === 'FAILED'
                => 'payment.failed',
            $type === 'card.updated'
                => 'mandate.active',
            $type === 'card.disable'
                => 'mandate.cancelled',
            default => $type,
        };

        $payment = $data['payment'] ?? [];
        $card    = $data['card'] ?? [];

        // mandate_id in our system is "<customer_id>|<card_id>"
        $mandateId = $card['customer_id'] && $card['id']
            ? "{$card['customer_id']}|{$card['id']}"
            : null;

        return new WebhookEvent(
            event:            $event,
            processorRef:     $payment['id'] ?? $card['id'] ?? '',
            mandateId:        $mandateId,
            amountMinorUnits: $payment['amount_money']['amount'] ?? null,
            currencyCode:     $payment['amount_money']['currency'] ?? null,
            leaseId:          $payment['reference_id'] ?? null,
            rawPayload:       $payload,
            idempotencyKey:   $payload['event_id'] ?? uniqid('sq_', true),
        );
    }

    // ── Utility ──────────────────────────────────────────────────────────────

    public function currencyFor(string $countryCode): string
    {
        return match (strtoupper($countryCode)) {
            'CA'    => 'CAD',
            default => 'USD',
        };
    }

    private function splitMandateId(string $mandateId): array
    {
        $parts = explode('|', $mandateId, 2);

        if (count($parts) !== 2) {
            throw new InvalidArgumentException(
                "Invalid Square mandate ID [{$mandateId}]. Expected format: <customer_id>|<card_id>"
            );
        }

        return $parts;
    }

    private function api(string $method, string $path, array $body = []): array
    {
        $request = Http::withToken($this->accessToken)
            ->withHeaders(['Square-Version' => '2024-06-04'])
            ->acceptJson();

        $response = match (strtoupper($method)) {
            'POST'  => $request->post($this->baseUrl . $path, $body),
            'GET'   => $request->get($this->baseUrl . $path),
            default => $request->send($method, $this->baseUrl . $path, ['json' => $body]),
        };

        return $response->json() ?? [];
    }
}
