<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\BackgroundCheck;
use App\Models\Payment;
use App\Models\TenantPaymentMethod;
use App\Models\User;
use App\Services\PaymentService;
use App\Services\TenantAccountLedgerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TenantPortalController extends Controller
{
    public function index()
    {
        $user = $this->tenantUser();
        $lease = $user->primaryActiveLease();

        $nextDue = null;
        $openMaintenance = 0;
        $unreadMessages = 0;

        if ($lease) {
            $nextDue = $lease->payments
                ->where('status', 'pending')
                ->sortBy('due_date')
                ->first();

            $openMaintenance = $lease->maintenanceRequests()
                ->whereIn('status', ['submitted', 'acknowledged', 'in_progress'])
                ->count();

            $unreadMessages = $lease->messages()
                ->where('sender_id', '!=', $user->id)
                ->whereNull('read_at')
                ->count();
        }

        return view('dashboard.tenant.index', compact('user', 'lease', 'nextDue', 'openMaintenance', 'unreadMessages'));
    }

    public function home()
    {
        $user = $this->tenantUser();
        $lease = $user->primaryActiveLease();

        return view('dashboard.tenant.home', compact('user', 'lease'));
    }

    public function payments(Request $request)
    {
        $user = $this->tenantUser()->load('tenantPaymentMethods');
        $lease = $user->primaryActiveLease();
        if ($lease) {
            $lease->load(['property', 'mandates']);
        }

        $tab = $request->query('tab', 'current');
        if (! in_array($tab, ['current', 'history'], true)) {
            $tab = 'current';
        }

        $payments = collect();
        $nextDue = null;
        $pendingCount = 0;
        $failedCount = 0;
        $defaultMethod = null;
        $mandate = null;
        $amountDueMinor = 0;
        $amountDueCurrency = '';
        $dueDate = null;
        $canPay = false;

        if ($lease) {
            $payments = Payment::query()
                ->where('lease_id', $lease->id)
                ->orderByDesc('due_date')
                ->paginate(20, ['*'], 'history_page')
                ->withQueryString();

            $nextDue = Payment::query()
                ->where('lease_id', $lease->id)
                ->where('status', 'pending')
                ->orderBy('due_date')
                ->first();

            $pendingCount = Payment::query()
                ->where('lease_id', $lease->id)
                ->where('status', 'pending')
                ->count();

            $failedCount = Payment::query()
                ->where('lease_id', $lease->id)
                ->where('status', 'failed')
                ->count();

            $defaultMethod = $user->tenantPaymentMethods()
                ->where('status', '!=', 'removed')
                ->orderByDesc('is_default')
                ->orderByDesc('created_at')
                ->first();

            $mandate = $lease->mandates->where('status', 'active')->first()
                ?? $lease->mandates->first();

            $amountDueMinor = $nextDue?->amount_minor_units ?? $lease->rent_minor_units;
            $amountDueCurrency = $nextDue?->currency_code ?? $lease->currency_code;
            $dueDate = $nextDue?->due_date ?? $lease->nextRentDueDate();
            $canPay = $defaultMethod !== null;
        }

        return view('dashboard.tenant.payments', compact(
            'user',
            'lease',
            'tab',
            'payments',
            'nextDue',
            'pendingCount',
            'failedCount',
            'defaultMethod',
            'mandate',
            'amountDueMinor',
            'amountDueCurrency',
            'dueDate',
            'canPay',
        ));
    }

    public function accountLedger(TenantAccountLedgerService $ledgerService)
    {
        $user = $this->tenantUser();
        $lease = $user->primaryActiveLease();
        if ($lease) {
            $lease->load('property');
        }

        $ledger = $lease
            ? $ledgerService->build($lease)
            : ['starting_minor' => 0, 'rows' => collect(), 'ending_minor' => 0];

        return view('dashboard.tenant.account-ledger', compact('user', 'lease', 'ledger'));
    }

    public function completePayment(Request $request, PaymentService $paymentService): RedirectResponse
    {
        $user = $this->tenantUser();
        $lease = $user->primaryActiveLease();

        if (! $lease) {
            return redirect()
                ->route('tenant.payments', ['tab' => 'current'])
                ->with('error', 'No active lease to pay rent for.');
        }

        $defaultMethod = $user->tenantPaymentMethods()
            ->where('status', '!=', 'removed')
            ->orderByDesc('is_default')
            ->orderByDesc('created_at')
            ->first();

        if (! $defaultMethod) {
            return redirect()
                ->route('tenant.payments', ['tab' => 'current'])
                ->with('error', 'Add a payment method under Account → Payment.');
        }

        $nextDue = Payment::query()
            ->where('lease_id', $lease->id)
            ->where('status', 'pending')
            ->orderBy('due_date')
            ->first();

        try {
            $payment = $paymentService->payCurrentRent($lease, $nextDue);
        } catch (\Throwable $e) {
            return redirect()
                ->route('tenant.payments', ['tab' => 'current'])
                ->with('error', $e->getMessage());
        }

        if ($payment->status === 'success') {
            return redirect()
                ->route('tenant.payments', ['tab' => 'current'])
                ->with('success', 'Payment authorized.');
        }

        return redirect()
            ->route('tenant.payments', ['tab' => 'current'])
            ->with('error', 'Payment could not be completed. Status: '.ucfirst($payment->status).'.');
    }

    public function account(Request $request)
    {
        $user = $this->tenantUser()->load('tenantPaymentMethods');
        $lease = $user->primaryActiveLease();
        if ($lease) {
            $lease->load('mandates');
        }

        $tab = $request->query('tab', 'profile');
        if (! in_array($tab, ['profile', 'background', 'payment'], true)) {
            $tab = 'profile';
        }

        $applications = Application::query()
            ->where('email', $user->email)
            ->with(['property', 'backgroundChecks'])
            ->orderByDesc('created_at')
            ->get();

        $pendingCheckTypes = $applications
            ->flatMap(fn (Application $app) => $app->backgroundChecks)
            ->whereIn('status', ['requested', 'pending', 'manual_review'])
            ->pluck('type')
            ->unique()
            ->values()
            ->all();

        $lastBackgroundCheck = $applications
            ->flatMap(fn (Application $app) => $app->backgroundChecks)
            ->sortByDesc(fn (BackgroundCheck $c) => $c->completed_at ?? $c->updated_at)
            ->first();

        $paymentMethods = $user->tenantPaymentMethods()
            ->where('status', '!=', 'removed')
            ->orderByDesc('is_default')
            ->orderByDesc('created_at')
            ->get();

        $mandate = $lease
            ? ($lease->mandates->where('status', 'active')->first() ?? $lease->mandates->first())
            : null;

        $editingMethod = null;
        if ($tab === 'payment' && $request->filled('edit')) {
            $editingMethod = $user->tenantPaymentMethods()
                ->where('id', $request->query('edit'))
                ->where('status', '!=', 'removed')
                ->first();
        }

        $paymentTypeOptions = [
            'card'   => 'Card',
            'ach'    => 'Bank (ACH)',
            'paypal' => 'PayPal',
            'crypto' => 'Crypto',
            'other'  => 'Other',
        ];
        $paymentType = 'card';
        if ($tab === 'payment') {
            $paymentType = $editingMethod?->method_type
                ?? ($request->query('pm') && isset($paymentTypeOptions[$request->query('pm')])
                    ? $request->query('pm')
                    : null);
            if (! $paymentType) {
                $preferred = $paymentMethods->firstWhere('is_default', true) ?? $paymentMethods->first();
                $paymentType = $preferred?->method_type ?? 'card';
            }
        }

        return view('dashboard.tenant.account', compact(
            'user', 'lease', 'tab', 'applications', 'lastBackgroundCheck',
            'paymentMethods', 'mandate', 'pendingCheckTypes', 'editingMethod',
            'paymentType', 'paymentTypeOptions'
        ));
    }

    public function updateProfile(Request $request)
    {
        $user = $this->tenantUser();
        $section = $request->input('section', 'identity');

        if ($section === 'contact') {
            $validated = $request->validate([
                'section' => 'required|in:contact',
                'phone'   => 'nullable|string|max:30',
            ]);
            $user->phone = $validated['phone'] ?? null;
            $user->save();

            return redirect()
                ->route('tenant.account', ['tab' => 'profile'])
                ->with('success', 'Phone number saved.');
        }

        if (! $user->tenantProfileEditable()) {
            return redirect()
                ->route('tenant.account', ['tab' => 'profile'])
                ->with('error', 'Your ID is under review — you cannot change it until review completes.');
        }

        $idRule = $user->kyc_id_document_path ? 'nullable' : 'required';

        $validated = $request->validate([
            'section'             => 'required|in:identity',
            'kyc_date_of_birth'     => 'required|date|before:-18 years',
            'kyc_address_line1'     => 'required|string|max:255',
            'kyc_address_line2'     => 'nullable|string|max:255',
            'kyc_city'              => 'required|string|max:120',
            'kyc_region'            => 'nullable|string|max:120',
            'kyc_postal_code'       => 'nullable|string|max:32',
            'kyc_address_country'   => 'required|string|size:2|in:'.implode(',', array_keys(config('countries', []))),
            'kyc_id_document'       => "{$idRule}|file|mimes:jpeg,jpg,png,webp,pdf|max:10240",
        ]);

        $path = $user->kyc_id_document_path;
        if ($request->hasFile('kyc_id_document')) {
            if ($path) {
                Storage::disk('local')->delete($path);
            }
            $file = $request->file('kyc_id_document');
            $dir  = 'tenant-id-documents/'.$user->id;
            $ext  = $file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'bin';
            $path = $file->storeAs($dir, 'government-id.'.strtolower($ext), 'local');
        }

        if (! $path) {
            return redirect()
                ->route('tenant.account', ['tab' => 'profile'])
                ->withInput()
                ->with('error', 'Upload a photo or scan of your government-issued ID.');
        }

        $user->fill([
            'kyc_legal_name'       => $user->kyc_legal_name ?: $user->fullName(),
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
            'kyc_verified'         => false,
            'kyc_verified_at'      => null,
        ]);
        $user->save();

        return redirect()
            ->route('tenant.account', ['tab' => 'profile'])
            ->with('success', 'ID details saved. Your document is pending review.');
    }

    public function idDocument()
    {
        $user = $this->tenantUser();
        abort_unless($user->kyc_id_document_path, 404);

        if (! Storage::disk('local')->exists($user->kyc_id_document_path)) {
            abort(404);
        }

        return Storage::disk('local')->response(
            $user->kyc_id_document_path,
            'government-id',
            ['Content-Type' => Storage::disk('local')->mimeType($user->kyc_id_document_path)]
        );
    }

    public function storeBackgroundCheck(Request $request)
    {
        $user = $this->tenantUser();
        $lease = $user->primaryActiveLease();

        $validated = $request->validate([
            'types'   => 'required|array|min:1',
            'types.*' => 'in:credit,criminal,eviction,right_to_rent,employment,references',
        ]);

        $application = $this->resolveTenantApplication($user, $lease);
        if (! $application) {
            return redirect()
                ->route('tenant.account', ['tab' => 'background'])
                ->with('error', 'No lease or application on file — contact your landlord to link your account.');
        }

        $requested = 0;
        $skipped = [];

        foreach ($validated['types'] as $type) {
            $exists = $application->backgroundChecks()
                ->where('type', $type)
                ->whereIn('status', ['requested', 'pending', 'manual_review'])
                ->exists();

            if ($exists) {
                $skipped[] = str_replace('_', ' ', $type);
                continue;
            }

            $application->backgroundChecks()->create([
                'property_id' => $application->property_id,
                'type'        => $type,
                'method'      => 'document_upload',
                'status'      => 'requested',
                'notes'       => 'Requested by tenant via account portal.',
            ]);
            $requested++;
        }

        if ($requested === 0) {
            return redirect()
                ->route('tenant.account', ['tab' => 'background'])
                ->with('error', 'All selected checks are already in progress.'.($skipped ? ' ('.implode(', ', $skipped).')' : ''));
        }

        $msg = $requested === 1
            ? '1 check requested.'
            : "{$requested} checks requested.";

        if ($skipped !== []) {
            $msg .= ' Skipped (already in progress): '.implode(', ', $skipped).'.';
        }

        return redirect()
            ->route('tenant.account', ['tab' => 'background'])
            ->with('success', $msg);
    }

    public function storePaymentMethod(Request $request)
    {
        $user = $this->tenantUser();
        $lease = $user->primaryActiveLease();

        $type = $request->validate(['method_type' => 'required|in:card,ach,crypto,paypal,other'])['method_type'];
        $validated = $this->validatePaymentMethod($request, $type, false);
        if ($type === 'card' && $request->boolean('billing_same_as_id') && ! filled($user->kyc_address_line1)) {
            return redirect()
                ->route('tenant.account', ['tab' => 'payment', 'pm' => 'card'])
                ->withInput()
                ->withErrors(['billing_same_as_id' => 'No ID address on file.']);
        }
        $meta = $this->buildPaymentMeta($validated, null, $user);

        $makeDefault = (bool) ($validated['is_default'] ?? false)
            || $user->tenantPaymentMethods()->where('status', '!=', 'removed')->count() === 0;

        if ($makeDefault) {
            $user->tenantPaymentMethods()->update(['is_default' => false]);
        }

        $fields = $this->normalizePaymentFields($type, $validated, $meta);
        if ($type === 'card' && empty($fields['last4'])) {
            return redirect()
                ->route('tenant.account', ['tab' => 'payment', 'pm' => $type])
                ->withInput()
                ->withErrors(['card_number' => 'Enter a valid card number (13–19 digits).']);
        }
        if ($type === 'ach' && empty($fields['last4'])) {
            return redirect()
                ->route('tenant.account', ['tab' => 'payment', 'pm' => $type])
                ->withInput()
                ->withErrors(['ach_account_number' => 'Enter a valid account number (at least 4 digits).']);
        }

        $user->tenantPaymentMethods()->create(array_merge([
            'lease_id'    => $lease?->id,
            'method_type' => $type,
            'is_default'  => $makeDefault,
            'status'      => 'active',
            'meta'        => $meta !== [] ? $meta : null,
        ], $fields));

        return redirect()
            ->route('tenant.account', ['tab' => 'payment', 'pm' => $type])
            ->with('success', 'Payment method saved.');
    }

    private function validatePaymentMethod(Request $request, string $type, bool $updating): array
    {
        $rules = [
            'label'       => 'nullable|string|max:120',
            'brand'       => 'nullable|string|max:32',
            'last4'       => 'nullable|string|max:8',
            'paypal_email'=> 'nullable|email|max:255',
            'crypto_asset'=> 'nullable|string|max:32',
            'crypto_wallet'=> 'nullable|string|max:120',
            'ach_bank_name'      => 'nullable|string|max:120',
            'ach_routing'        => 'nullable|string|max:32',
            'ach_account_number' => 'nullable|string|max:32',
            'ach_account_type'   => 'nullable|in:checking,savings',
            'is_default'  => 'sometimes|boolean',
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
    private function normalizePaymentFields(string $type, array $validated, array $meta): array
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

    private function buildPaymentMeta(array $validated, ?TenantPaymentMethod $existing, ?User $user = null): array
    {
        $wallet = $validated['crypto_wallet'] ?? null;
        if ($wallet) {
            $wallet = $this->maskWallet($wallet);
        } elseif ($existing && ! empty($existing->meta['crypto_wallet'])) {
            $wallet = $existing->meta['crypto_wallet'];
        }

        $meta = array_filter([
            'paypal_email'  => $validated['paypal_email'] ?? null,
            'crypto_asset'  => $validated['crypto_asset'] ?? null,
            'crypto_wallet' => $wallet,
            'ach_bank_name'      => $validated['ach_bank_name'] ?? null,
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

    public function updatePaymentMethod(Request $request, TenantPaymentMethod $method)
    {
        $user = $this->tenantUser();
        abort_unless($method->user_id === $user->id && $method->status !== 'removed', 403);

        $lease = $user->primaryActiveLease();
        $validated = $this->validatePaymentMethod($request, $method->method_type, true);

        $meta = $this->buildPaymentMeta($validated, $method);

        if ($validated['is_default'] ?? false) {
            $user->tenantPaymentMethods()->where('id', '!=', $method->id)->update(['is_default' => false]);
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

        return redirect()
            ->route('tenant.account', ['tab' => 'payment', 'pm' => $method->method_type])
            ->with('success', 'Payment method updated.');
    }

    public function destroyPaymentMethod(TenantPaymentMethod $method)
    {
        $user = $this->tenantUser();
        abort_unless($method->user_id === $user->id, 403);

        $wasDefault = $method->is_default;
        $method->update(['status' => 'removed', 'is_default' => false]);

        if ($wasDefault) {
            $next = $user->tenantPaymentMethods()->where('status', '!=', 'removed')->first();
            $next?->update(['is_default' => true]);
        }

        return redirect()
            ->route('tenant.account', ['tab' => 'payment', 'pm' => $method->method_type])
            ->with('success', 'Payment method removed.');
    }

    public function defaultPaymentMethod(TenantPaymentMethod $method)
    {
        $user = $this->tenantUser();
        abort_unless($method->user_id === $user->id && $method->status !== 'removed', 403);

        $user->tenantPaymentMethods()->update(['is_default' => false]);
        $method->update(['is_default' => true, 'status' => 'active']);

        return redirect()
            ->route('tenant.account', ['tab' => 'payment', 'pm' => $method->method_type])
            ->with('success', 'Default payment method updated.');
    }

    private function resolveTenantApplication($user, $lease): ?Application
    {
        if ($lease) {
            return Application::firstOrCreate(
                [
                    'property_id' => $lease->property_id,
                    'email'       => $user->email,
                ],
                [
                    'first_name' => $user->first_name,
                    'last_name'  => $user->last_name,
                    'phone'      => $user->phone,
                    'status'     => 'approved',
                ]
            );
        }

        return Application::query()
            ->where('email', $user->email)
            ->orderByDesc('created_at')
            ->first();
    }

    private function maskWallet(string $wallet): string
    {
        $wallet = trim($wallet);
        if (strlen($wallet) <= 8) {
            return str_repeat('•', strlen($wallet));
        }

        return substr($wallet, 0, 4).str_repeat('•', max(4, strlen($wallet) - 8)).substr($wallet, -4);
    }

    private function tenantUser()
    {
        $user = Auth::user();
        abort_unless($user->isTenant(), 403);

        return $user;
    }
}
