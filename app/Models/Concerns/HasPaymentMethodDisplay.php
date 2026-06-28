<?php

namespace App\Models\Concerns;

trait HasPaymentMethodDisplay
{
    public function typeLabel(): string
    {
        return match ($this->method_type) {
            'card'   => 'Credit / debit card',
            'ach'    => 'Bank account (ACH)',
            'crypto' => 'Cryptocurrency',
            'paypal' => 'PayPal',
            default  => 'Other',
        };
    }

    /** @return array<int, string> */
    public function tableCells(): array
    {
        $meta = $this->meta ?? [];

        return match ($this->method_type) {
            'card' => [
                $this->brand ?: '—',
                $this->maskedCardNumber(),
                $this->formattedCardExpiry(),
                ($meta['cvc_on_file'] ?? false) ? '•••' : '—',
                $this->label ?: '—',
            ],
            'ach' => [
                $meta['ach_bank_name'] ?? '—',
                $this->label ?: '—',
                $this->maskedAchRouting(),
                $this->maskedAchAccount(),
                ucfirst((string) ($meta['ach_account_type'] ?? '—')),
            ],
            'paypal' => [
                $meta['paypal_email'] ?? '—',
                $this->label ?: '—',
            ],
            'crypto' => [
                strtoupper((string) ($meta['crypto_asset'] ?? '—')),
                $meta['crypto_wallet'] ?? '—',
            ],
            default => [
                $this->label ?: '—',
            ],
        };
    }

    public function billingSameAsIdAddress(): bool
    {
        return (bool) (($this->meta ?? [])['billing_same_as_id'] ?? false);
    }

    public function formattedBillingAddress(?\App\Models\User $user = null): string
    {
        if ($this->method_type !== 'card') {
            return '—';
        }

        $meta = $this->meta ?? [];

        if ($this->billingSameAsIdAddress()) {
            $user = $user ?? $this->user;

            return $user ? $user->formattedIdAddress() : '—';
        }

        $parts = array_filter([
            $meta['billing_line1'] ?? null,
            $meta['billing_line2'] ?? null,
            trim(($meta['billing_city'] ?? '').(! empty($meta['billing_region']) ? ', '.$meta['billing_region'] : '')),
            $meta['billing_postal_code'] ?? null,
            ! empty($meta['billing_country']) ? strtoupper($meta['billing_country']) : null,
        ]);

        return $parts !== [] ? implode(', ', $parts) : '—';
    }

    public function displaySummary(): string
    {
        $meta = $this->meta ?? [];

        return match ($this->method_type) {
            'card' => trim(implode(' ', array_filter([
                $this->brand,
                $this->maskedCardNumber() !== '—' ? $this->maskedCardNumber() : null,
                $this->formattedCardExpiry() !== '—' ? 'exp '.$this->formattedCardExpiry() : null,
            ]))) ?: ($this->label ?: $this->typeLabel()),
            'ach' => trim(implode(' · ', array_filter([
                $meta['ach_bank_name'] ?? null,
                $this->maskedAchRouting() !== '—' ? 'routing '.$this->maskedAchRouting() : null,
                $this->maskedAchAccount() !== '—' ? 'acct '.$this->maskedAchAccount() : null,
            ]))) ?: ($this->label ?: $this->typeLabel()),
            'paypal' => $meta['paypal_email'] ?? $this->label ?? $this->typeLabel(),
            'crypto' => trim(implode(' · ', array_filter([
                isset($meta['crypto_asset']) ? strtoupper($meta['crypto_asset']) : null,
                $meta['crypto_wallet'] ?? null,
            ]))) ?: $this->typeLabel(),
            default => $this->label ?: $this->typeLabel(),
        };
    }

    public function maskedCardNumber(): string
    {
        if (! $this->last4) {
            return '—';
        }
        $meta = $this->meta ?? [];
        $first6 = preg_replace('/\D/', '', (string) ($meta['card_first6'] ?? ''));
        if (strlen($first6) === 6) {
            return substr($first6, 0, 4).' '.substr($first6, 4, 2).'•• •••• '.$this->last4;
        }

        return '•••• •••• •••• '.$this->last4;
    }

    public function maskedAchRouting(): string
    {
        $last4 = ($this->meta ?? [])['ach_routing_last4'] ?? null;

        return $last4 ? '••••'.$last4 : '—';
    }

    public function maskedAchAccount(): string
    {
        return $this->last4 ? '••••'.$this->last4 : '—';
    }

    public function formattedCardExpiry(): string
    {
        $meta = $this->meta ?? [];
        $month = $meta['card_exp_month'] ?? null;
        $year  = $meta['card_exp_year'] ?? null;
        if (! $month || ! $year) {
            return '—';
        }
        $year = strlen((string) $year) === 4 ? substr((string) $year, -2) : $year;

        return str_pad((string) $month, 2, '0', STR_PAD_LEFT).'/'.$year;
    }

    public static function tableHeadersForType(string $type): array
    {
        return match ($type) {
            'card'   => ['Brand', 'Card number', 'Expires', 'CVC', 'Label'],
            'ach'    => ['Bank', 'Account holder', 'Routing', 'Account', 'Type'],
            'paypal' => ['PayPal email', 'Label'],
            'crypto' => ['Asset', 'Wallet'],
            default  => ['Description'],
        };
    }
}
