<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ManagesStoredPaymentMethods;
use App\Models\LandlordPaymentMethod;
use App\Models\LandlordPayoutAccount;
use App\Services\LandlordAccountActivation;
use App\Support\KycLegalName;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class LandlordAccountController extends Controller
{
    use ManagesStoredPaymentMethods;

    private const TABS = ['identity', 'business', 'payment', 'banks', 'portfolio'];

    public function show(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->isLandlord(), 403);

        $this->migrateLegacyLandlordBilling($user);

        $user->load('payoutAccounts');

        $tab = $this->resolveTab($request);
        $banksSec = 'accounts';

        if ($tab === 'banks') {
            $sec = $request->query('sec');
            $banksSec = in_array($sec, ['accounts', 'add'], true) ? $sec : 'accounts';
        }

        $paymentMethods = collect();
        $editingMethod = null;
        $paymentType = 'card';
        $paymentTypeOptions = [
            'card'   => 'Card',
            'ach'    => 'Bank (ACH)',
            'paypal' => 'PayPal',
            'crypto' => 'Crypto',
            'other'  => 'Other',
        ];

        if ($tab === 'payment') {
            $paymentMethods = $user->landlordPaymentMethods()
                ->where('status', '!=', 'removed')
                ->orderByDesc('is_default')
                ->orderByDesc('created_at')
                ->get();

            if ($request->filled('edit')) {
                $editingMethod = $user->landlordPaymentMethods()
                    ->where('id', $request->query('edit'))
                    ->where('status', '!=', 'removed')
                    ->first();
            }

            $paymentType = $editingMethod?->method_type
                ?? ($request->query('pm') && isset($paymentTypeOptions[$request->query('pm')])
                    ? $request->query('pm')
                    : null);

            if (! $paymentType) {
                $preferred = $paymentMethods->firstWhere('is_default', true) ?? $paymentMethods->first();
                $paymentType = $preferred?->method_type ?? 'card';
            }
        }

        $activation = app(LandlordAccountActivation::class);

        return view('dashboard.landlord.account', [
            'user'               => $user,
            'tab'                => $tab,
            'banksSec'           => $banksSec,
            'payoutAccountCount' => $user->payoutAccounts->count(),
            'paymentMethods'     => $paymentMethods,
            'editingMethod'      => $editingMethod,
            'paymentType'        => $paymentType,
            'paymentTypeOptions' => $paymentTypeOptions,
            'activation'         => $activation,
            'propertyCount'      => $user->properties()->count(),
        ]);
    }

    public function updateIdentity(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->isLandlord(), 403);

        if (! $user->landlordKycEditable()) {
            return $this->accountRedirect('identity')
                ->with('error', $user->kycWorkflowStatus() === 'verified'
                    ? 'Your identity is already verified.'
                    : 'Your submission is under review; you cannot change it yet.');
        }

        $idRule = $user->kyc_id_document_path ? 'nullable' : 'required';
        $validated = $request->validate([
            'kyc_first_name'      => 'required|string|max:120',
            'kyc_middle_name'     => 'nullable|string|max:120',
            'kyc_last_name'       => 'required|string|max:120',
            'kyc_name_suffix'     => 'nullable|string|max:16',
            'kyc_date_of_birth'   => 'required|date|before:-18 years',
            'kyc_address_line1'   => 'required|string|max:255',
            'kyc_address_line2'   => 'nullable|string|max:255',
            'kyc_city'            => 'required|string|max:120',
            'kyc_region'          => 'nullable|string|max:120',
            'kyc_postal_code'     => 'nullable|string|max:32',
            'kyc_address_country' => 'required|string|size:2|in:'.implode(',', array_keys(config('countries', []))),
            'kyc_id_document'     => "{$idRule}|file|mimes:jpeg,jpg,png,webp,pdf|max:10240",
        ]);

        $path = $user->kyc_id_document_path;
        if ($request->hasFile('kyc_id_document')) {
            if ($path) {
                Storage::disk('local')->delete($path);
            }
            $file = $request->file('kyc_id_document');
            $dir  = 'kyc-documents/'.$user->id;
            $ext  = $file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'bin';
            $path = $file->storeAs($dir, 'government-id.'.strtolower($ext), 'local');
        }

        if (! $path) {
            return $this->accountRedirect('identity')
                ->withInput()
                ->with('error', 'Please upload a photo or scan of your government-issued ID.');
        }

        $user->fill([
            'kyc_first_name'       => $validated['kyc_first_name'],
            'kyc_middle_name'      => $validated['kyc_middle_name'] ?? null,
            'kyc_last_name'        => $validated['kyc_last_name'],
            'kyc_name_suffix'      => KycLegalName::normalizeSuffix($validated['kyc_name_suffix'] ?? null),
            'kyc_date_of_birth'    => $validated['kyc_date_of_birth'],
            'kyc_address_line1'    => $validated['kyc_address_line1'],
            'kyc_address_line2'    => $validated['kyc_address_line2'] ?? null,
            'kyc_city'             => $validated['kyc_city'],
            'kyc_region'           => $validated['kyc_region'] ?? null,
            'kyc_postal_code'      => $validated['kyc_postal_code'] ?? null,
            'kyc_address_country'  => strtoupper($validated['kyc_address_country']),
            'kyc_id_document_path' => $path,
            'kyc_status'           => 'pending',
            'kyc_submitted_at'     => now(),
            'kyc_rejection_reason' => null,
        ]);
        $user->kyc_verified    = false;
        $user->kyc_verified_at = null;
        $user->syncKycLegalNameFromParts();
        $user->save();

        return $this->accountRedirect('identity')
            ->with('success', 'Identity details submitted for review.');
    }

    public function activatePortfolio(Request $request, LandlordAccountActivation $activation)
    {
        $user = Auth::user();
        abort_unless($user->isLandlord(), 403);

        if ($user->isLandlordAccountActive()) {
            return $this->accountRedirect('portfolio')
                ->with('success', 'Account is already active.');
        }

        $validated = $request->validate([
            'portfolio_units' => 'required|integer|min:1|max:9999',
        ]);

        if (! $activation->hasDefaultPaymentMethod($user)) {
            return $this->accountRedirect('portfolio')
                ->with('error', 'Set a default payment method on the Payment tab first.');
        }

        $units = (int) $validated['portfolio_units'];
        $activation->activate($user, $units);

        return $this->accountRedirect('portfolio')
            ->with('success', 'Account active. You can add properties and leases.');
    }

    public function updatePortfolioDefaults(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->isLandlord(), 403);

        if ($request->input('default_multi_unit_capacity') === '' || $request->input('default_multi_unit_capacity') === null) {
            $request->merge(['default_multi_unit_capacity' => null]);
        }

        $validated = $request->validate([
            'default_multi_unit_capacity' => 'nullable|integer|min:1|max:999',
        ]);

        $user->default_multi_unit_capacity = $validated['default_multi_unit_capacity'] ?? null;
        $user->save();

        return $this->accountRedirect('portfolio')
            ->with('success', 'Portfolio defaults saved.');
    }

    public function storePaymentMethod(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->isLandlord(), 403);

        $type = $request->validate(['method_type' => 'required|in:card,ach,crypto,paypal,other'])['method_type'];
        $validated = $this->validatePaymentMethod($request, $type, false);

        if ($type === 'card' && $request->boolean('billing_same_as_id') && ! filled($user->kyc_address_line1)) {
            return $this->accountRedirect('payment', null, ['pm' => 'card'])
                ->withInput()
                ->withErrors(['billing_same_as_id' => 'No ID address on file.']);
        }

        $meta = $this->buildPaymentMeta($validated, null, $user);
        $makeDefault = (bool) ($validated['is_default'] ?? false)
            || $user->landlordPaymentMethods()->where('status', '!=', 'removed')->count() === 0;

        if ($makeDefault) {
            $user->landlordPaymentMethods()->update(['is_default' => false]);
        }

        $fields = $this->normalizePaymentFields($type, $validated, $meta);
        if ($type === 'card' && empty($fields['last4'])) {
            return $this->accountRedirect('payment', null, ['pm' => $type])
                ->withInput()
                ->withErrors(['card_number' => 'Enter a valid card number (13–19 digits).']);
        }
        if ($type === 'ach' && empty($fields['last4'])) {
            return $this->accountRedirect('payment', null, ['pm' => $type])
                ->withInput()
                ->withErrors(['ach_account_number' => 'Enter a valid account number (at least 4 digits).']);
        }

        $user->landlordPaymentMethods()->create(array_merge([
            'method_type' => $type,
            'is_default'  => $makeDefault,
            'status'      => 'active',
            'meta'        => $meta !== [] ? $meta : null,
        ], $fields));

        $this->syncDefaultCardToUserBilling($user);

        return $this->accountRedirect('payment', null, ['pm' => $type])
            ->with('success', 'Payment method saved.');
    }

    public function updatePaymentMethod(Request $request, LandlordPaymentMethod $method)
    {
        $user = Auth::user();
        abort_unless($user->isLandlord(), 403);
        abort_unless($method->user_id === $user->id && $method->status !== 'removed', 403);

        $validated = $this->validatePaymentMethod($request, $method->method_type, true);
        $meta = $this->buildPaymentMeta($validated, $method, $user);

        if ($validated['is_default'] ?? false) {
            $user->landlordPaymentMethods()->where('id', '!=', $method->id)->update(['is_default' => false]);
        }

        $fields = $this->normalizePaymentFields($method->method_type, $validated, $meta);
        if (in_array($method->method_type, ['card', 'ach'], true) && empty($fields['last4'])) {
            $fields['last4'] = $method->last4;
        }

        $method->update(array_merge([
            'is_default' => (bool) ($validated['is_default'] ?? $method->is_default),
            'status'     => 'active',
            'meta'       => $meta !== [] ? $meta : null,
        ], $fields));

        $this->syncDefaultCardToUserBilling($user);

        return $this->accountRedirect('payment', null, ['pm' => $method->method_type])
            ->with('success', 'Payment method updated.');
    }

    public function destroyPaymentMethod(LandlordPaymentMethod $method)
    {
        $user = Auth::user();
        abort_unless($user->isLandlord(), 403);
        abort_unless($method->user_id === $user->id, 403);

        $wasDefault = $method->is_default;
        $type = $method->method_type;
        $method->update(['status' => 'removed', 'is_default' => false]);

        if ($wasDefault) {
            $next = $user->landlordPaymentMethods()->where('status', '!=', 'removed')->first();
            $next?->update(['is_default' => true]);
        }

        $this->syncDefaultCardToUserBilling($user);

        return $this->accountRedirect('payment', null, ['pm' => $type])
            ->with('success', 'Payment method removed.');
    }

    public function defaultPaymentMethod(LandlordPaymentMethod $method)
    {
        $user = Auth::user();
        abort_unless($user->isLandlord(), 403);
        abort_unless($method->user_id === $user->id && $method->status !== 'removed', 403);

        $user->landlordPaymentMethods()->update(['is_default' => false]);
        $method->update(['is_default' => true, 'status' => 'active']);

        $this->syncDefaultCardToUserBilling($user);

        return $this->accountRedirect('payment', null, ['pm' => $method->method_type])
            ->with('success', 'Default payment method updated.');
    }

    public function storePayoutAccount(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->isLandlord(), 403);

        $validated = $request->validate([
            'payout_purpose'       => 'required|in:collection,repatriation',
            'payout_country'       => 'required|string|size:2|in:'.implode(',', array_keys(config('countries', []))),
            'payout_label'         => 'required|string|max:120',
            'payout_holder_name'   => 'required|string|max:255',
            'payout_bank_name'     => 'nullable|string|max:255',
            'payout_iban'          => 'nullable|string|max:64',
            'payout_local_account' => 'nullable|string|max:64',
            'payout_local_routing' => 'nullable|string|max:64',
        ]);

        if (blank($validated['payout_iban']) && blank($validated['payout_local_account'])) {
            return $this->accountRedirect('banks', 'add')
                ->withInput()
                ->with('error', 'Enter an IBAN or a local account number (with routing when required).');
        }

        $cc   = strtoupper($validated['payout_country']);
        $curr = config("countries.{$cc}.currency", 'USD');
        $raw  = preg_replace('/\s+/', '', (string) (($validated['payout_iban'] ?? '') ?: ($validated['payout_local_account'] ?? '')));
        $hint = strlen($raw) >= 4 ? '…'.substr($raw, -4) : 'On file';

        LandlordPayoutAccount::create([
            'user_id'           => $user->id,
            'country_code'      => $cc,
            'currency_code'     => $curr,
            'purpose'           => $validated['payout_purpose'],
            'label'             => $validated['payout_label'],
            'holder_name'       => $validated['payout_holder_name'],
            'bank_name'         => $validated['payout_bank_name'] ?: null,
            'iban'              => filled($validated['payout_iban']) ? trim($validated['payout_iban']) : null,
            'local_account_ref' => filled($validated['payout_local_account']) ? trim($validated['payout_local_account']) : null,
            'local_routing_ref' => filled($validated['payout_local_routing']) ? trim($validated['payout_local_routing']) : null,
            'display_hint'      => $hint,
            'status'            => 'saved',
        ]);

        return $this->accountRedirect('banks', 'accounts')
            ->with('success', 'Bank account saved.');
    }

    public function destroyPayoutAccount(LandlordPayoutAccount $payoutAccount)
    {
        $user = Auth::user();
        abort_unless($user->isLandlord(), 403);
        abort_unless($payoutAccount->user_id === $user->id, 403);

        $payoutAccount->delete();

        return $this->accountRedirect('banks', 'accounts')
            ->with('success', 'Account removed.');
    }

    private function syncDefaultCardToUserBilling($user): void
    {
        $card = $user->landlordPaymentMethods()
            ->where('status', '!=', 'removed')
            ->where('method_type', 'card')
            ->where('is_default', true)
            ->first();

        if (! $card) {
            return;
        }

        $meta = $card->meta ?? [];
        $user->billing_pm_brand = $card->brand;
        $user->billing_pm_last4 = $card->last4;

        if ($card->billingSameAsIdAddress()) {
            $user->billing_company_name = null;
            $user->billing_line1       = $user->kyc_address_line1;
            $user->billing_line2       = $user->kyc_address_line2;
            $user->billing_city        = $user->kyc_city;
            $user->billing_region      = $user->kyc_region;
            $user->billing_postal_code = $user->kyc_postal_code;
            $user->billing_country     = $user->kyc_address_country;
        } else {
            $user->billing_line1       = $meta['billing_line1'] ?? null;
            $user->billing_line2       = $meta['billing_line2'] ?? null;
            $user->billing_city        = $meta['billing_city'] ?? null;
            $user->billing_region      = $meta['billing_region'] ?? null;
            $user->billing_postal_code = $meta['billing_postal_code'] ?? null;
            $user->billing_country     = $meta['billing_country'] ?? null;
        }

        $user->save();
    }

    private function resolveTab(Request $request): string
    {
        $tab = $request->query('tab');

        if (in_array($tab, self::TABS, true)) {
            return $tab;
        }

        $legacy = $request->query('section');
        if ($legacy) {
            return match ($legacy) {
                'identity', 'kyc' => 'identity',
                'business' => 'business',
                'billing', 'payment' => 'payment',
                'payouts', 'banks' => 'banks',
                default => 'identity',
            };
        }

        return 'identity';
    }

    private function accountRedirect(string $tab, ?string $sec = null, array $extra = []): RedirectResponse
    {
        $params = array_merge(['tab' => $tab], $extra);
        if ($sec !== null) {
            $params['sec'] = $sec;
        }

        return redirect()->route('landlord.account', $params);
    }
}
