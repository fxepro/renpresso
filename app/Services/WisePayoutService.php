<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

/**
 * Wise Platform API — landlord payout service.
 *
 * NOT a PaymentProcessor. Wise does not collect rent.
 * Used exclusively for outbound transfers: platform → landlord's bank account
 * in the landlord's home currency, using Wise's FX rates.
 *
 * Docs: https://docs.wise.com/api-docs/api-reference
 *
 * Environment vars:
 *   WISE_API_KEY       — from Wise Platform dashboard (strong customer auth key)
 *   WISE_PROFILE_ID    — your business profile ID
 *   WISE_ENVIRONMENT   — sandbox | live
 */
class WisePayoutService
{
    private string $baseUrl;
    private string $apiKey;
    private string $profileId;

    public function __construct()
    {
        $env = config('services.wise.environment', 'sandbox');

        $this->baseUrl   = $env === 'live'
            ? 'https://api.wise.com'
            : 'https://api.sandbox.transferwise.tech';

        $this->apiKey    = config('services.wise.api_key', '');
        $this->profileId = config('services.wise.profile_id', '');
    }

    // ── FX Quote ─────────────────────────────────────────────────────────────

    /**
     * Get a Wise FX quote — how much the recipient receives for a given source amount.
     *
     * @param  string  $sourceCurrency   e.g. 'USD'
     * @param  string  $targetCurrency   e.g. 'NGN'
     * @param  int     $sourceMinorUnits Amount in source minor units (cents)
     * @return WiseQuote
     */
    public function getQuote(string $sourceCurrency, string $targetCurrency, int $sourceMinorUnits): WiseQuote
    {
        $response = $this->api('POST', '/v3/profiles/' . $this->profileId . '/quotes', [
            'sourceCurrency'  => $sourceCurrency,
            'targetCurrency'  => $targetCurrency,
            'sourceAmount'    => $sourceMinorUnits / 100,
            'payOut'          => 'BANK_TRANSFER',
        ]);

        $rate          = $response['rate'] ?? 1.0;
        $targetAmount  = $response['targetAmount'] ?? 0;
        $fee           = $response['paymentOptions'][0]['fee']['total'] ?? 0;
        $expiresAt     = $response['expirationTime'] ?? null;

        return new WiseQuote(
            quoteId:          $response['id'] ?? '',
            rate:             (float) $rate,
            sourceMinorUnits: $sourceMinorUnits,
            targetMinorUnits: (int) round($targetAmount * 100),
            sourceCurrency:   $sourceCurrency,
            targetCurrency:   $targetCurrency,
            feeMinorUnits:    (int) round($fee * 100),
            expiresAt:        $expiresAt,
        );
    }

    // ── Recipient ─────────────────────────────────────────────────────────────

    /**
     * Look up an existing Wise recipient account by ID.
     */
    public function getRecipient(string $accountId): array
    {
        return $this->api('GET', "/v1/accounts/{$accountId}");
    }

    // ── Transfer ─────────────────────────────────────────────────────────────

    /**
     * Create a Wise transfer using a confirmed quote.
     *
     * @param  string  $quoteId         From getQuote()
     * @param  string  $recipientId     Wise recipient/account ID stored on the landlord
     * @param  string  $reference       Free-text reference (e.g. "Rent – May 2026")
     * @return WiseTransferResponse
     */
    public function createTransfer(string $quoteId, string $recipientId, string $reference = ''): WiseTransferResponse
    {
        $response = $this->api('POST', '/v1/transfers', [
            'targetAccount'           => (int) $recipientId,
            'quoteUuid'               => $quoteId,
            'customerTransactionId'   => uniqid('rmx_payout_', true),
            'details'                 => [
                'reference'           => $reference ?: 'Renpresso payout',
                'transferPurpose'     => 'verification.transfers.purpose.pay.bills',
                'sourceOfFunds'       => 'verification.source.of.funds.business',
            ],
        ]);

        return new WiseTransferResponse(
            transferId: (string) ($response['id'] ?? ''),
            status:     $response['status'] ?? 'unknown',
            reference:  $reference,
        );
    }

    /**
     * Fund a created transfer from the platform's Wise balance.
     * This triggers the actual money movement.
     *
     * @param  string  $transferId  From createTransfer()
     */
    public function fundTransfer(string $transferId): array
    {
        return $this->api('POST', "/v3/profiles/{$this->profileId}/transfers/{$transferId}/payments", [
            'type' => 'BALANCE',
        ]);
    }

    /**
     * Cancel a transfer that hasn't been funded yet.
     */
    public function cancelTransfer(string $transferId): array
    {
        return $this->api('PUT', "/v1/transfers/{$transferId}/cancel");
    }

    // ── Status ───────────────────────────────────────────────────────────────

    /**
     * Get the current status of a transfer.
     *
     * @return string  incoming_payment_waiting | processing | funds_converted | outgoing_payment_sent | bounced_back | cancelled | funds_refunded
     */
    public function getTransferStatus(string $transferId): string
    {
        $response = $this->api('GET', "/v1/transfers/{$transferId}");

        return $response['status'] ?? 'unknown';
    }

    // ── Balance ──────────────────────────────────────────────────────────────

    /**
     * Get all Wise balance accounts for this profile.
     */
    public function getBalances(): array
    {
        return $this->api('GET', "/v4/profiles/{$this->profileId}/balances?types=STANDARD");
    }

    // ── HTTP ─────────────────────────────────────────────────────────────────

    private function api(string $method, string $path, array $body = []): array
    {
        $req = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type'  => 'application/json',
        ])->acceptJson();

        $response = match (strtoupper($method)) {
            'POST' => $req->post($this->baseUrl . $path, $body),
            'PUT'  => $req->put($this->baseUrl . $path, $body),
            'GET'  => $req->get($this->baseUrl . $path),
            default => $req->send($method, $this->baseUrl . $path),
        };

        return $response->json() ?? [];
    }
}

// ── DTOs ─────────────────────────────────────────────────────────────────────

class WiseQuote
{
    public function __construct(
        public readonly string $quoteId,
        public readonly float  $rate,
        public readonly int    $sourceMinorUnits,
        public readonly int    $targetMinorUnits,
        public readonly string $sourceCurrency,
        public readonly string $targetCurrency,
        public readonly int    $feeMinorUnits,
        public readonly ?string $expiresAt,
    ) {}

    public function netTargetMinorUnits(): int
    {
        return $this->targetMinorUnits - $this->feeMinorUnits;
    }
}

class WiseTransferResponse
{
    public function __construct(
        public readonly string $transferId,
        public readonly string $status,
        public readonly string $reference,
    ) {}

    public function isPending(): bool
    {
        return in_array($this->status, ['incoming_payment_waiting', 'processing', 'funds_converted']);
    }

    public function isComplete(): bool
    {
        return $this->status === 'outgoing_payment_sent';
    }

    public function isFailed(): bool
    {
        return in_array($this->status, ['bounced_back', 'cancelled', 'funds_refunded']);
    }
}
