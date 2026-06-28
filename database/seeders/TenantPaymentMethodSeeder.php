<?php

namespace Database\Seeders;

use App\Models\Lease;
use App\Models\TenantPaymentMethod;
use App\Models\User;
use Illuminate\Database\Seeder;

class TenantPaymentMethodSeeder extends Seeder
{
    /** @var array<string, list<array<string, mixed>>> */
    private array $byEmail = [
        'sophie@example.com' => [
            ['method_type' => 'card', 'brand' => 'Visa', 'last4' => '4242', 'label' => 'Personal', 'is_default' => true, 'meta' => ['card_first6' => '424242', 'card_exp_month' => '12', 'card_exp_year' => '2028', 'cvc_on_file' => true]],
            ['method_type' => 'paypal', 'label' => 'Backup', 'meta' => ['paypal_email' => 'sophie@example.com']],
        ],
        'priya@example.com' => [
            ['method_type' => 'card', 'brand' => 'Visa', 'last4' => '8912', 'label' => 'Rent card', 'is_default' => true, 'meta' => ['card_first6' => '411111', 'card_exp_month' => '06', 'card_exp_year' => '2027', 'cvc_on_file' => true]],
            ['method_type' => 'ach', 'label' => 'Priya Sharma', 'last4' => '7890', 'meta' => ['ach_bank_name' => 'HDFC Bank', 'ach_routing_last4' => '1234', 'ach_account_type' => 'checking']],
        ],
        'james@example.com' => [
            ['method_type' => 'card', 'brand' => 'Mastercard', 'last4' => '5512', 'label' => 'Main', 'is_default' => true, 'meta' => ['card_first6' => '555555', 'card_exp_month' => '09', 'card_exp_year' => '2029', 'cvc_on_file' => true]],
            ['method_type' => 'ach', 'label' => 'James Wilson', 'last4' => '3301', 'meta' => ['ach_bank_name' => 'Barclays']],
        ],
        'emeka@example.com' => [
            ['method_type' => 'card', 'brand' => 'Visa', 'last4' => '1024', 'label' => 'Debit', 'is_default' => true, 'meta' => ['card_first6' => '400000', 'card_exp_month' => '03', 'card_exp_year' => '2028', 'cvc_on_file' => true]],
            ['method_type' => 'other', 'label' => 'GTBank transfer reference'],
        ],
        'budi@example.com' => [
            ['method_type' => 'card', 'brand' => 'Visa', 'last4' => '6677', 'label' => 'Primary', 'is_default' => true, 'meta' => ['card_first6' => '424242', 'card_exp_month' => '11', 'card_exp_year' => '2027', 'cvc_on_file' => true]],
        ],
        'maya.265@example.com' => [
            ['method_type' => 'card', 'brand' => 'Visa', 'last4' => '2201', 'label' => 'Unit 265', 'is_default' => true, 'meta' => ['card_first6' => '424242', 'card_exp_month' => '12', 'card_exp_year' => '2028', 'cvc_on_file' => true]],
        ],
        'ravi.388@example.com' => [
            ['method_type' => 'ach', 'label' => 'Ravi Menon', 'last4' => '4455', 'meta' => ['ach_bank_name' => 'DBS', 'ach_routing_last4' => '7171', 'ach_account_type' => 'savings'], 'is_default' => true],
        ],
        'elena.12a@example.com' => [
            ['method_type' => 'paypal', 'label' => 'PayPal', 'meta' => ['paypal_email' => 'elena.12a@example.com'], 'is_default' => true],
        ],
        'tom.ph@example.com' => [
            ['method_type' => 'crypto', 'label' => 'USDC', 'meta' => ['crypto_asset' => 'USDC', 'crypto_wallet' => '0x742d…8f3a'], 'is_default' => true],
        ],
    ];

    public function run(): void
    {
        foreach ($this->byEmail as $email => $methods) {
            $tenant = User::where('email', $email)->where('role', 'tenant')->first();
            if (! $tenant) {
                continue;
            }

            if ($tenant->tenantPaymentMethods()->where('status', '!=', 'removed')->exists()) {
                continue;
            }

            $lease = Lease::query()
                ->where('tenant_id', $tenant->id)
                ->where('status', 'active')
                ->orderByDesc('activated_at')
                ->first();

            foreach ($methods as $i => $row) {
                TenantPaymentMethod::create([
                    'user_id'     => $tenant->id,
                    'lease_id'    => $lease?->id,
                    'method_type' => $row['method_type'],
                    'brand'       => $row['brand'] ?? null,
                    'last4'       => $row['last4'] ?? null,
                    'label'       => $row['label'] ?? null,
                    'is_default'  => (bool) ($row['is_default'] ?? $i === 0),
                    'status'      => 'active',
                    'meta'        => $row['meta'] ?? null,
                ]);
            }
        }

        $this->backfillCardMeta();
        $this->backfillAchMeta();
    }

    private function backfillCardMeta(): void
    {
        $first6ByLast4 = [
            '4242' => '424242',
            '8912' => '411111',
            '5512' => '555555',
            '1024' => '400000',
            '6677' => '424242',
            '2201' => '424242',
        ];

        TenantPaymentMethod::query()
            ->where('method_type', 'card')
            ->where('status', '!=', 'removed')
            ->each(function (TenantPaymentMethod $method) use ($first6ByLast4) {
                $meta = $method->meta ?? [];
                $changed = false;

                if (empty($meta['card_exp_month'])) {
                    $meta['card_exp_month'] = '12';
                    $changed = true;
                }
                if (empty($meta['card_exp_year'])) {
                    $meta['card_exp_year'] = '2028';
                    $changed = true;
                }
                if (empty($meta['cvc_on_file'])) {
                    $meta['cvc_on_file'] = true;
                    $changed = true;
                }
                if (empty($meta['card_first6']) && $method->last4 && isset($first6ByLast4[$method->last4])) {
                    $meta['card_first6'] = $first6ByLast4[$method->last4];
                    $changed = true;
                }

                if ($changed) {
                    $method->update(['meta' => $meta]);
                }
            });
    }

    private function backfillAchMeta(): void
    {
        $defaults = [
            '7890' => ['ach_routing_last4' => '1234', 'ach_account_type' => 'checking'],
            '3301' => ['ach_routing_last4' => '0000', 'ach_account_type' => 'checking'],
            '4455' => ['ach_routing_last4' => '7171', 'ach_account_type' => 'savings'],
        ];

        TenantPaymentMethod::query()
            ->where('method_type', 'ach')
            ->where('status', '!=', 'removed')
            ->each(function (TenantPaymentMethod $method) use ($defaults) {
                $meta = $method->meta ?? [];
                $changed = false;
                $fallback = $method->last4 ? ($defaults[$method->last4] ?? null) : null;

                if (empty($meta['ach_routing_last4']) && $fallback) {
                    $meta['ach_routing_last4'] = $fallback['ach_routing_last4'];
                    $changed = true;
                }
                if (empty($meta['ach_account_type']) && $fallback) {
                    $meta['ach_account_type'] = $fallback['ach_account_type'];
                    $changed = true;
                }
                if ($changed) {
                    $method->update(['meta' => $meta]);
                }
            });
    }
}
