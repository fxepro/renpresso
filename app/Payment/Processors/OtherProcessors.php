<?php

namespace App\Payment\Processors;

use App\Payment\Contracts\PaymentProcessor;
use App\Payment\Data\{ChargeRequest, ChargeResponse, ChargeStatus, MandateRequest, MandateResponse, RefundResponse, WebhookEvent};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

// ─────────────────────────────────────────────────────────────
// FLUTTERWAVE — Africa (NG, KE, GH, ZA, EG + 30 more)
// ─────────────────────────────────────────────────────────────
class FlutterwaveProcessor implements PaymentProcessor
{
    private string $baseUrl = 'https://api.flutterwave.com/v3';
    private string $secretKey;

    public function __construct()
    {
        $this->secretKey = config('services.flutterwave.secret_key');
    }

    public function createCharge(ChargeRequest $request): ChargeResponse
    {
        $response = Http::withToken($this->secretKey)->post("{$this->baseUrl}/charges?type=direct-debit", [
            'amount'      => $request->amountMinorUnits / 100,
            'currency'    => $request->currencyCode,
            'token'       => $request->mandateId,
            'email'       => $request->metadata['tenant_email'] ?? '',
            'tx_ref'      => 'rmx_' . $request->leaseId . '_' . time(),
            'narration'   => $request->description,
        ]);

        $data = $response->json();
        return new ChargeResponse(
            processorRef: $data['data']['id'] ?? 'unknown',
            status:       $data['status'] === 'success' ? 'success' : 'pending',
            errorMessage: $data['message'] ?? null,
        );
    }

    public function getChargeStatus(string $processorRef): ChargeStatus
    {
        $response = Http::withToken($this->secretKey)->get("{$this->baseUrl}/transactions/{$processorRef}/verify");
        $data     = $response->json()['data'] ?? [];
        return new ChargeStatus(
            processorRef:     $processorRef,
            status:           $data['status'] === 'successful' ? 'success' : ($data['status'] ?? 'pending'),
            amountMinorUnits: isset($data['amount']) ? (int)($data['amount'] * 100) : null,
            currencyCode:     $data['currency'] ?? null,
        );
    }

    public function refund(string $processorRef, int $amountMinorUnits): RefundResponse
    {
        $response = Http::withToken($this->secretKey)->post("{$this->baseUrl}/transactions/{$processorRef}/refund", [
            'amount' => $amountMinorUnits / 100,
        ]);
        $data = $response->json()['data'] ?? [];
        return new RefundResponse(
            refundRef:        $data['id'] ?? 'unknown',
            status:           'pending',
            amountMinorUnits: $amountMinorUnits,
        );
    }

    public function setupMandate(MandateRequest $request): MandateResponse
    {
        // Flutterwave: create a payment plan for recurring charges
        $plan = Http::withToken($this->secretKey)->post("{$this->baseUrl}/payment-plans", [
            'amount'   => $request->amountMinorUnits / 100,
            'name'     => 'Rent - Lease ' . $request->leaseId,
            'interval' => 'monthly',
            'currency' => $request->currencyCode,
        ])->json()['data'] ?? [];

        return new MandateResponse(
            mandateId: (string)($plan['id'] ?? ''),
            status:    'pending',
            authUrl:   "https://checkout.flutterwave.com/v3/hosted/pay?plan={$plan['id']}",
        );
    }

    public function cancelMandate(string $mandateId): void
    {
        Http::withToken($this->secretKey)->put("{$this->baseUrl}/payment-plans/{$mandateId}", [
            'status' => 'cancelled',
        ]);
    }

    public function normalizeWebhook(Request $request): WebhookEvent
    {
        $payload = $request->all();
        $data    = $payload['data'] ?? [];
        $event   = $payload['event'] ?? '';

        return new WebhookEvent(
            event:            $event === 'charge.completed' ? 'payment.success' : $event,
            processorRef:     (string)($data['id'] ?? ''),
            mandateId:        $data['payment_plan'] ?? null,
            amountMinorUnits: isset($data['amount']) ? (int)($data['amount'] * 100) : null,
            currencyCode:     $data['currency'] ?? null,
            leaseId:          $data['meta']['lease_id'] ?? null,
            rawPayload:       $payload,
            idempotencyKey:   $data['txRef'] ?? uniqid(),
        );
    }

    public function verifyWebhookSignature(Request $request): bool
    {
        return $request->header('verif-hash') === config('services.flutterwave.webhook_secret');
    }

    public function currencyFor(string $countryCode): string
    {
        return config('countries')[strtoupper($countryCode)]['currency'] ?? 'NGN';
    }
}

// ─────────────────────────────────────────────────────────────
// XENDIT — Southeast Asia (ID, PH, MY, VN, TH)
// ─────────────────────────────────────────────────────────────
class XenditProcessor implements PaymentProcessor
{
    private string $baseUrl  = 'https://api.xendit.co';
    private string $secretKey;

    public function __construct()
    {
        $this->secretKey = config('services.xendit.secret_key');
    }

    private function http()
    {
        return Http::withBasicAuth($this->secretKey, '');
    }

    public function createCharge(ChargeRequest $request): ChargeResponse
    {
        $response = $this->http()->post("{$this->baseUrl}/recurring/schedules/now", [
            'reference_id' => 'rmx_' . $request->leaseId . '_' . time(),
            'plan_id'      => $request->mandateId,
            'metadata'     => ['lease_id' => $request->leaseId],
        ]);
        $data = $response->json();
        return new ChargeResponse(
            processorRef: $data['id'] ?? 'unknown',
            status:       'pending',
            errorMessage: $data['message'] ?? null,
        );
    }

    public function getChargeStatus(string $processorRef): ChargeStatus
    {
        $data = $this->http()->get("{$this->baseUrl}/payment_requests/{$processorRef}")->json();
        return new ChargeStatus(
            processorRef:     $processorRef,
            status:           $data['status'] === 'SUCCEEDED' ? 'success' : strtolower($data['status'] ?? 'pending'),
            amountMinorUnits: isset($data['amount']) ? (int)($data['amount'] * 100) : null,
            currencyCode:     $data['currency'] ?? null,
        );
    }

    public function refund(string $processorRef, int $amountMinorUnits): RefundResponse
    {
        $data = $this->http()->post("{$this->baseUrl}/refunds", [
            'payment_request_id' => $processorRef,
            'amount'             => $amountMinorUnits / 100,
        ])->json();
        return new RefundResponse(
            refundRef:        $data['id'] ?? 'unknown',
            status:           'pending',
            amountMinorUnits: $amountMinorUnits,
        );
    }

    public function setupMandate(MandateRequest $request): MandateResponse
    {
        $plan = $this->http()->post("{$this->baseUrl}/recurring/plans", [
            'reference_id'   => 'rmx_lease_' . $request->leaseId,
            'customer_id'    => $request->metadata['xendit_customer_id'] ?? null,
            'recurring_action' => 'PAYMENT',
            'currency'       => $request->currencyCode,
            'amount'         => $request->amountMinorUnits / 100,
            'schedule'       => ['reference_id' => 'monthly', 'interval' => 'MONTH', 'interval_count' => 1],
            'metadata'       => ['lease_id' => $request->leaseId],
            'success_return_url' => $request->returnUrl,
        ])->json();

        return new MandateResponse(
            mandateId: $plan['id'] ?? '',
            status:    'pending',
            authUrl:   $plan['actions'][0]['url'] ?? null,
        );
    }

    public function cancelMandate(string $mandateId): void
    {
        $this->http()->post("{$this->baseUrl}/recurring/plans/{$mandateId}/deactivate");
    }

    public function normalizeWebhook(Request $request): WebhookEvent
    {
        $payload = $request->all();
        $event   = $payload['event'] ?? '';
        $data    = $payload['data'] ?? [];

        return new WebhookEvent(
            event:            str_contains($event, 'succeeded') ? 'payment.success' : 'payment.failed',
            processorRef:     $data['id'] ?? '',
            mandateId:        $data['plan_id'] ?? null,
            amountMinorUnits: isset($data['amount']) ? (int)($data['amount'] * 100) : null,
            currencyCode:     $data['currency'] ?? null,
            leaseId:          $data['metadata']['lease_id'] ?? null,
            rawPayload:       $payload,
            idempotencyKey:   $data['reference_id'] ?? uniqid(),
        );
    }

    public function verifyWebhookSignature(Request $request): bool
    {
        $token = $request->header('x-callback-token');
        return $token === config('services.xendit.webhook_token');
    }

    public function currencyFor(string $countryCode): string
    {
        return config('countries')[strtoupper($countryCode)]['currency'] ?? 'IDR';
    }
}

// ─────────────────────────────────────────────────────────────
// MERCADO PAGO — Latin America (BR, MX, AR, CO, CL)
// ─────────────────────────────────────────────────────────────
class MercadoPagoProcessor implements PaymentProcessor
{
    private string $baseUrl = 'https://api.mercadopago.com';
    private string $accessToken;

    public function __construct()
    {
        $this->accessToken = config('services.mercadopago.access_token');
    }

    private function http()
    {
        return Http::withToken($this->accessToken)
            ->withHeaders(['X-Idempotency-Key' => uniqid('rmx_', true)]);
    }

    public function createCharge(ChargeRequest $request): ChargeResponse
    {
        $response = $this->http()->post("{$this->baseUrl}/v1/payments", [
            'transaction_amount' => $request->amountMinorUnits / 100,
            'currency_id'        => $request->currencyCode,
            'payment_method_id'  => 'pix',
            'token'              => $request->mandateId,
            'description'        => $request->description,
            'external_reference' => $request->leaseId,
            'installments'       => 1,
        ]);
        $data = $response->json();
        return new ChargeResponse(
            processorRef: (string)($data['id'] ?? 'unknown'),
            status:       $data['status'] === 'approved' ? 'success' : 'pending',
            errorMessage: $data['status_detail'] ?? null,
        );
    }

    public function getChargeStatus(string $processorRef): ChargeStatus
    {
        $data = $this->http()->get("{$this->baseUrl}/v1/payments/{$processorRef}")->json();
        return new ChargeStatus(
            processorRef:     $processorRef,
            status:           $data['status'] === 'approved' ? 'success' : strtolower($data['status'] ?? 'pending'),
            amountMinorUnits: isset($data['transaction_amount']) ? (int)($data['transaction_amount'] * 100) : null,
            currencyCode:     $data['currency_id'] ?? null,
        );
    }

    public function refund(string $processorRef, int $amountMinorUnits): RefundResponse
    {
        $data = $this->http()->post("{$this->baseUrl}/v1/payments/{$processorRef}/refunds", [
            'amount' => $amountMinorUnits / 100,
        ])->json();
        return new RefundResponse(
            refundRef:        (string)($data['id'] ?? 'unknown'),
            status:           'pending',
            amountMinorUnits: $amountMinorUnits,
        );
    }

    public function setupMandate(MandateRequest $request): MandateResponse
    {
        $preapproval = $this->http()->post("{$this->baseUrl}/preapproval", [
            'reason'              => 'Monthly Rent',
            'external_reference'  => $request->leaseId,
            'payer_email'         => $request->tenantEmail,
            'auto_recurring'      => [
                'frequency'       => 1,
                'frequency_type'  => 'months',
                'transaction_amount' => $request->amountMinorUnits / 100,
                'currency_id'     => $request->currencyCode,
            ],
            'back_url' => $request->returnUrl,
            'status'   => 'pending',
        ])->json();

        return new MandateResponse(
            mandateId: $preapproval['id'] ?? '',
            status:    'pending',
            authUrl:   $preapproval['init_point'] ?? null,
        );
    }

    public function cancelMandate(string $mandateId): void
    {
        $this->http()->put("{$this->baseUrl}/preapproval/{$mandateId}", [
            'status' => 'cancelled',
        ]);
    }

    public function normalizeWebhook(Request $request): WebhookEvent
    {
        $payload = $request->all();
        $action  = $payload['action'] ?? '';
        $data    = $payload['data'] ?? [];

        return new WebhookEvent(
            event:            $action === 'payment.updated' ? 'payment.success' : $action,
            processorRef:     (string)($data['id'] ?? ''),
            mandateId:        null,
            amountMinorUnits: null,
            currencyCode:     null,
            leaseId:          $payload['external_reference'] ?? null,
            rawPayload:       $payload,
            idempotencyKey:   (string)($data['id'] ?? uniqid()),
        );
    }

    public function verifyWebhookSignature(Request $request): bool
    {
        // Mercado Pago sends x-signature header
        $signature = $request->header('x-signature');
        $requestId = $request->header('x-request-id');
        $dataId    = $request->query('data.id', '');
        $manifest  = "id:{$dataId};request-id:{$requestId};";
        $expected  = hash_hmac('sha256', $manifest, config('services.mercadopago.webhook_secret'));
        return str_contains($signature ?? '', $expected);
    }

    public function currencyFor(string $countryCode): string
    {
        return config('countries')[strtoupper($countryCode)]['currency'] ?? 'BRL';
    }
}
