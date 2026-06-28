<?php

namespace App\Http\Controllers\Concerns;

use App\Models\LandlordPaymentMethod;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

trait ManagesStoredPaymentMethods
{
    protected function validatePaymentMethod(Request $request, string $type, bool $updating): array
    {
        $rules = [
            'label'              => 'nullable|string|max:120',
            'brand'              => 'nullable|string|max:32',
            'last4'              => 'nullable|string|max:8',
            'paypal_email'       => 'nullable|email|max:255',
            'crypto_asset'       => 'nullable|string|max:32',
            'crypto_wallet'      => 'nullable|string|max:120',
            'ach_bank_name'      => 'nullable|string|max:120',
            'ach_routing'        => 'nullable|string|max:32',
            'ach_account_number' => 'nullable|string|max:32',
            'ach_account_type'   => 'nullable|in:checking,savings',
            'is_default'         => 'sometimes|boolean',
            'billing_same_as_id' => 'sometimes|boolean',
            'billing_line1'      => 'nullable|string|max:255',
            'billing_line2'      => 'nullable|string|max:255',
            'billing_city'       => 'nullable|string|max:120',
            'billing_region'     => 'nullable|string|max:120',
            'billing_postal_code'=> 'nullable|string|max:32',
            'billing_country'    => 'nullable|string|size:2',
        ];

        if ($type === 'card') {
            $rules['card_number']    = ($updating ? 'nullable' : 'required').'|string|regex:/^[0-9\s\-]{13,23}$/';
            $rules['card_exp_month'] = 'required|regex:/^(0[1-9]|1[0-2])$/';
            $rules['card_exp_year']  = 'required|regex:/^[0-9]{2}$/';
            $rules['card_cvc']       = ($updating ? 'nullable' : 'required').'|string|regex:/^[0-9]{3,4}$/';
        } elseif ($type === 'ach') {
            $rules['ach_bank_name']      = 'required|string|max:120';
            $rules['ach_routing']       = ($updating ? 'nullable' : 'required').'|string|regex:/^[0-9A-Za-z\-\s]{4,32}$/';
            $rules['ach_account_number'] = ($updating ? 'nullable' : 'required').'|string|regex:/^[0-9]{4,17}$/';
            $rules['ach_account_type']   = 'required|in:checking,savings';
        } elseif ($type === 'paypal') {
            $rules['paypal_email'] = 'required|email|max:255';
        } elseif ($type === 'crypto') {
            $rules['crypto_asset'] = 'required|string|max:32';
            if (! $updating) {
                $rules['crypto_wallet'] = 'required|string|max:120';
            }
        } elseif ($type === 'other') {
            $rules['label'] = 'required|string|max:120';
        }

        return $request->validate($rules);
    }

    /** @return array{label: ?string, brand: ?string, last4: ?string} */
    protected function normalizePaymentFields(string $type, array $validated, array $meta): array
    {
        $label = $validated['label'] ?? null;
        $brand = $validated['brand'] ?? null;
        $last4 = null;

        if ($type === 'card') {
            $pan = preg_replace('/\D/', '', (string) ($validated['card_number'] ?? ''));
            if (strlen($pan) >= 13) {
                $last4 = substr($pan, -4);
            } elseif (! empty($validated['last4'])) {
                $last4 = substr(preg_replace('/\D/', '', (string) $validated['last4']), -4);
            }
        } elseif ($type === 'ach') {
            $acct = preg_replace('/\D/', '', (string) ($validated['ach_account_number'] ?? ''));
            if (strlen($acct) >= 4) {
                $last4 = substr($acct, -4);
            }
        } elseif (isset($validated['last4'])) {
            $digits = preg_replace('/\D/', '', (string) $validated['last4']);
            $last4 = $digits !== '' ? substr($digits, -4) : null;
        }

        if ($type === 'paypal') {
            $label = $label ?: 'PayPal';
        }
        if ($type === 'crypto') {
            $asset = $meta['crypto_asset'] ?? null;
            $label = $label ?: ($asset ? strtoupper($asset) : null);
        }

        return [
            'label' => $label ?: null,
            'brand' => $brand ?: null,
            'last4' => $last4 ?: null,
        ];
    }

    protected function buildPaymentMeta(array $validated, ?Model $existing, ?User $user = null): array
    {
        $wallet = $validated['crypto_wallet'] ?? null;
        if ($wallet) {
            $wallet = $this->maskWallet($wallet);
        } elseif ($existing && ! empty($existing->meta['crypto_wallet'])) {
            $wallet = $existing->meta['crypto_wallet'];
        }

        $meta = array_filter([
            'paypal_email'     => $validated['paypal_email'] ?? null,
            'crypto_asset'     => $validated['crypto_asset'] ?? null,
            'crypto_wallet'    => $wallet,
            'ach_bank_name'    => $validated['ach_bank_name'] ?? null,
            'ach_account_type' => $validated['ach_account_type'] ?? null,
        ]);

        $routing = strtoupper(preg_replace('/[^0-9A-Za-z]/', '', (string) ($validated['ach_routing'] ?? '')));
        if (strlen($routing) >= 4) {
            $meta['ach_routing_last4'] = substr($routing, -4);
        } elseif ($existing && ! empty($existing->meta['ach_routing_last4'])) {
            $meta['ach_routing_last4'] = $existing->meta['ach_routing_last4'];
        }

        if (isset($validated['card_exp_month'], $validated['card_exp_year'])) {
            $year = (int) $validated['card_exp_year'];
            if ($year < 100) {
                $year += 2000;
            }
            $meta['card_exp_month'] = str_pad((string) (int) $validated['card_exp_month'], 2, '0', STR_PAD_LEFT);
            $meta['card_exp_year']  = (string) $year;
        } elseif ($existing) {
            $meta['card_exp_month'] = $existing->meta['card_exp_month'] ?? null;
            $meta['card_exp_year']  = $existing->meta['card_exp_year'] ?? null;
        }

        $pan = preg_replace('/\D/', '', (string) ($validated['card_number'] ?? ''));
        if (strlen($pan) >= 13) {
            $meta['card_first6'] = substr($pan, 0, 6);
        } elseif ($existing && ! empty($existing->meta['card_first6'])) {
            $meta['card_first6'] = $existing->meta['card_first6'];
        }

        if (! empty($validated['card_cvc'])) {
            $meta['cvc_on_file'] = true;
        } elseif ($existing && ($existing->meta['cvc_on_file'] ?? false)) {
            $meta['cvc_on_file'] = true;
        }

        if (isset($validated['billing_same_as_id']) || isset($validated['billing_line1'])) {
            $sameAsId = (bool) ($validated['billing_same_as_id'] ?? false);
            $meta['billing_same_as_id'] = $sameAsId;
            if ($sameAsId) {
                unset($meta['billing_line1'], $meta['billing_line2'], $meta['billing_city'],
                    $meta['billing_region'], $meta['billing_postal_code'], $meta['billing_country']);
            } else {
                $meta['billing_line1']       = $validated['billing_line1'] ?? null;
                $meta['billing_line2']       = $validated['billing_line2'] ?? null;
                $meta['billing_city']        = $validated['billing_city'] ?? null;
                $meta['billing_region']      = $validated['billing_region'] ?? null;
                $meta['billing_postal_code'] = $validated['billing_postal_code'] ?? null;
                $meta['billing_country']     = isset($validated['billing_country'])
                    ? strtoupper($validated['billing_country'])
                    : null;
            }
        } elseif ($existing) {
            foreach (['billing_same_as_id', 'billing_line1', 'billing_line2', 'billing_city',
                'billing_region', 'billing_postal_code', 'billing_country'] as $key) {
                if (isset($existing->meta[$key])) {
                    $meta[$key] = $existing->meta[$key];
                }
            }
        }

        return array_filter($meta, fn ($v) => $v !== null && $v !== '');
    }

    protected function maskWallet(string $wallet): string
    {
        $wallet = trim($wallet);
        if (strlen($wallet) <= 8) {
            return str_repeat('•', strlen($wallet));
        }

        return substr($wallet, 0, 4).str_repeat('•', max(4, strlen($wallet) - 8)).substr($wallet, -4);
    }

    protected function migrateLegacyLandlordBilling(User $user): void
    {
        if (! filled($user->billing_line1)) {
            return;
        }
        if ($user->landlordPaymentMethods()->where('status', '!=', 'removed')->exists()) {
            return;
        }

        $user->landlordPaymentMethods()->create([
            'method_type' => 'card',
            'label'       => $user->billing_company_name ?: 'Platform billing',
            'brand'       => $user->billing_pm_brand,
            'last4'       => $user->billing_pm_last4,
            'is_default'  => true,
            'status'      => 'active',
            'meta'        => array_filter([
                'billing_line1'       => $user->billing_line1,
                'billing_line2'       => $user->billing_line2,
                'billing_city'        => $user->billing_city,
                'billing_region'      => $user->billing_region,
                'billing_postal_code' => $user->billing_postal_code,
                'billing_country'     => $user->billing_country,
            ]),
        ]);
    }
}
