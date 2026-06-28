<?php



namespace App\Http\Controllers;



use App\Http\Controllers\Concerns\ResolvesMaintenanceTeam;

use App\Models\MaintenanceTeamDocument;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Hash;

use Illuminate\Support\Facades\Storage;

use Illuminate\Validation\Rules\Password;



class MaintenanceAccountController extends Controller

{

    use ResolvesMaintenanceTeam;



    public function show(Request $request)

    {

        $user = Auth::user();

        $team = $this->maintenanceTeam();

        $team?->load(['documents', 'reviews.landlord']);

        $team?->loadCount('reviews');



        $operatingCities = $team

            ? $team->cities()->orderByDesc('is_primary')->orderBy('city')->get()

            : collect();



        $complianceSections = config('maintenance_compliance_documents.sections', []);

        $documentsByType = $team ? $team->documentsByType() : [];

        $compliance = $team ? $team->complianceSummary() : null;

        $kycFlow = $user->maintenanceDirectorIdentityStatus();

        $kycEditable = $user->maintenanceKycEditable();



        $tab = $request->query('tab', 'profile');

        $allowedTabs = ['profile', 'services', 'company', 'financials', 'trade', 'reviews'];

        if (! in_array($tab, $allowedTabs, true)) {

            $tab = 'profile';

        }



        $profileSec = $this->resolveSection($request, ['overview', 'security', 'owner', 'kyc', 'contact'], 'overview');

        if (in_array($profileSec, ['owner', 'kyc', 'contact'], true)) {

            $profileSec = 'overview';

        }

        $companySec = $this->resolveSection($request, ['overview', 'address', 'legal'], 'overview');

        $financialSec = $this->resolveSection($request, ['receiving', 'billing'], 'receiving');



        $tradeDocTypes = array_keys($complianceSections['operations']['documents'] ?? []);

        $tradeSec = $request->query('sec');

        if ($tab === 'trade' && (! is_string($tradeSec) || ! in_array($tradeSec, $tradeDocTypes, true))) {

            $tradeSec = $tradeDocTypes[0] ?? 'trade_licence';

        }



        $docCounts = $this->documentCountsByArea($documentsByType, $complianceSections);



        $reviews = $team

            ? $team->reviews()->with('landlord')->latest()->get()

            : collect();



        $invoiceCount = $team ? $team->invoices()->count() : 0;

        $recentInvoices = $team

            ? $team->invoices()->with('landlord')->orderByDesc('issued_at')->orderByDesc('created_at')->limit(8)->get()

            : collect();



        return view('dashboard.maintenance-portal.account', compact(

            'user',

            'team',

            'operatingCities',

            'complianceSections',

            'documentsByType',

            'compliance',

            'kycFlow',

            'kycEditable',

            'tab',

            'profileSec',

            'companySec',

            'financialSec',

            'tradeSec',

            'docCounts',

            'reviews',

            'invoiceCount',

            'recentInvoices',

        ));

    }



    public function updateProfile(Request $request)

    {

        $user = Auth::user();



        $data = $request->validate([

            'first_name' => ['required', 'string', 'max:100'],

            'last_name'  => ['required', 'string', 'max:100'],

            'phone'      => ['nullable', 'string', 'max:30'],

        ]);



        $user->update($data);



        return $this->accountRedirect($request, 'profile', 'overview')

            ->with('success', 'Contact details saved.');

    }



    public function updatePassword(Request $request)

    {

        $user = Auth::user();



        $data = $request->validate([

            'current_password' => ['required', 'current_password'],

            'password'         => ['required', 'confirmed', Password::min(8)],

        ]);



        $user->update(['password' => Hash::make($data['password'])]);



        return $this->accountRedirect($request, 'profile', 'security')

            ->with('success', 'Password changed.');

    }



    public function updateTeam(Request $request)

    {

        $team = $this->maintenanceTeamOrAbort();



        $data = $request->validate([

            'name' => ['sometimes', 'required', 'string', 'max:255'],

            'description' => ['nullable', 'string', 'max:2000'],

            'phone' => ['nullable', 'string', 'max:30'],

            'services' => ['nullable', 'array'],

            'services.*' => ['string', 'max:80'],

            'services_extra' => ['nullable', 'string', 'max:500'],

            'is_listed' => ['sometimes', 'boolean'],

        ]);



        $payload = [];



        if ($request->has('name')) {

            $payload['name'] = $data['name'];

            $payload['description'] = $data['description'] ?? null;

            $payload['phone'] = $data['phone'] ?? null;

            $payload['is_listed'] = $request->boolean('is_listed');

        }



        if ($request->has('services') || $request->has('services_extra')) {

            $options = config('maintenance_services', []);

            $picked = collect($request->input('services', []))

                ->map(fn ($s) => trim((string) $s))

                ->filter()

                ->unique()

                ->values();



            $extra = array_filter(array_map('trim', explode(',', (string) $request->input('services_extra', ''))));

            foreach ($extra as $item) {

                if ($item !== '' && ! $picked->contains($item)) {

                    $picked->push($item);

                }

            }



            $payload['services'] = $picked->isEmpty() ? null : $picked->all();

        }



        if ($payload !== []) {

            $team->update($payload);

        }



        $tab = $request->input('redirect_tab', 'company');

        $sec = $request->input('redirect_sec', $tab === 'services' ? null : 'overview');



        return $this->accountRedirect($request, $tab, $sec)

            ->with('success', $tab === 'services' ? 'Services saved.' : 'Company profile saved.');

    }



    public function updateDirectorIdentity(Request $request)

    {

        $user = Auth::user();

        abort_unless($user->isMaintenance(), 403);



        if (! $user->maintenanceKycEditable()) {

            return $this->accountRedirect($request, 'profile', 'overview')

                ->with('error', $user->maintenanceDirectorIdentityStatus() === 'verified'

                    ? 'Director identity is already verified.'

                    : 'Your submission is under review; you cannot change it yet.');

        }



        $redirectSec = $request->input('redirect_sec', 'owner');

        $idRule = 'nullable';

        $validated = $request->validate([

            'kyc_legal_name'      => 'required|string|max:255',

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

            $dir  = 'maintenance-kyc-documents/'.$user->id;

            $ext  = $file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'bin';

            $path = $file->storeAs($dir, 'government-id.'.strtolower($ext), 'local');

        }



        if (! $path && $redirectSec === 'owner') {

            $user->fill([

                'kyc_legal_name'      => $validated['kyc_legal_name'],

                'kyc_date_of_birth'   => $validated['kyc_date_of_birth'],

                'kyc_address_line1'   => $validated['kyc_address_line1'],

                'kyc_address_line2'   => $validated['kyc_address_line2'] ?? null,

                'kyc_city'            => $validated['kyc_city'],

                'kyc_region'          => $validated['kyc_region'] ?? null,

                'kyc_postal_code'     => $validated['kyc_postal_code'] ?? null,

                'kyc_address_country' => strtoupper($validated['kyc_address_country']),

            ]);

            $user->save();

            return $this->accountRedirect($request, 'profile', 'overview')

                ->with('success', 'Saved.');

        }



        if (! $path) {

            return $this->accountRedirect($request, 'profile', 'overview', keepEdit: true, editKey: 'owner')

                ->withInput()

                ->with('error', 'Upload a photo or scan of the director’s government-issued ID.');

        }



        $user->fill([

            'kyc_legal_name'       => $validated['kyc_legal_name'],

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



        return $this->accountRedirect($request, 'profile', 'overview')

            ->with('success', 'Director identity submitted for review.');

    }



    public function directorIdDocument()

    {

        $user = Auth::user();

        abort_unless($user->isMaintenance() && $user->kyc_id_document_path, 404);



        if (! Storage::disk('local')->exists($user->kyc_id_document_path)) {

            abort(404);

        }



        return Storage::disk('local')->response(

            $user->kyc_id_document_path,

            'director-id',

            ['Content-Type' => Storage::disk('local')->mimeType($user->kyc_id_document_path)]

        );

    }



    public function storeDocument(Request $request, string $documentType)

    {

        $team = $this->maintenanceTeamOrAbort();

        $definition = $this->documentDefinition($documentType);

        abort_unless($definition, 404);



        $existing = $team->documents()->where('document_type', $documentType)->first();

        if ($existing && $existing->status === 'pending') {

            return $this->accountRedirectForDocumentType($request, $documentType)

                ->with('error', 'This document is under review and cannot be changed yet.');

        }



        $fileRule = ($existing && $existing->file_path) ? 'nullable' : 'required';

        $rules = [

            'file' => "{$fileRule}|file|mimes:jpeg,jpg,png,webp,pdf|max:10240",

        ];

        if (! empty($definition['fields']['reference_number'])) {

            $rules['reference_number'] = 'nullable|string|max:120';

        }

        if (! empty($definition['fields']['expires_on'])) {

            $rules['expires_on'] = 'nullable|date';

        }

        $validated = $request->validate($rules);



        $path = $existing?->file_path;

        if ($request->hasFile('file')) {

            if ($path) {

                Storage::disk('local')->delete($path);

            }

            $file = $request->file('file');

            $dir  = 'maintenance-team-documents/'.$team->id;

            $ext  = $file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'bin';

            $path = $file->storeAs($dir, $documentType.'.'.strtolower($ext), 'local');

        }



        if (! $path) {

            return $this->accountRedirectForDocumentType($request, $documentType, keepEdit: true)

                ->with('error', 'Please upload a file for this requirement.');

        }



        $team->documents()->updateOrCreate(

            ['document_type' => $documentType],

            [

                'file_path'        => $path,

                'status'           => 'pending',

                'reference_number' => $validated['reference_number'] ?? null,

                'expires_on'       => $validated['expires_on'] ?? null,

                'submitted_at'     => now(),

                'verified_at'      => null,

                'rejection_reason' => null,

            ]

        );



        return $this->accountRedirectForDocumentType($request, $documentType)

            ->with('success', $definition['label'].' submitted for review.');

    }



    public function destroyDocument(Request $request, MaintenanceTeamDocument $document)

    {

        $team = $this->maintenanceTeamOrAbort();

        abort_unless($document->maintenance_team_id === $team->id, 403);

        abort_unless($document->isEditable(), 403);



        if ($document->file_path && Storage::disk('local')->exists($document->file_path)) {

            Storage::disk('local')->delete($document->file_path);

        }

        $type = $document->document_type;

        $document->delete();



        return $this->accountRedirectForDocumentType($request, $type)

            ->with('success', 'Document removed.');

    }



    public function documentFile(MaintenanceTeamDocument $document)

    {

        $team = $this->maintenanceTeamOrAbort();

        abort_unless($document->maintenance_team_id === $team->id, 403);



        if (! Storage::disk('local')->exists($document->file_path)) {

            abort(404);

        }



        return Storage::disk('local')->response(

            $document->file_path,

            $document->document_type,

            ['Content-Type' => Storage::disk('local')->mimeType($document->file_path)]

        );

    }



    /** @param  list<string>  $allowed */

    private function resolveSection(Request $request, array $allowed, string $default): string

    {

        $sec = $request->query('sec', $default);



        return is_string($sec) && in_array($sec, $allowed, true) ? $sec : $default;

    }



  /** @return array<string, int> */

    private function documentCountsByArea(array $documentsByType, array $sections): array

    {

        $counts = [

            'business' => 0,

            'financial_receiving' => 0,

        ];



        foreach (['company_registration', 'certificate_of_incorporation', 'ubo_declaration'] as $type) {

            if (($documentsByType[$type] ?? null)?->file_path) {

                $counts['business']++;

            }

        }



        foreach (['bank_account_verification', 'tax_identification'] as $type) {

            if (($documentsByType[$type] ?? null)?->file_path) {

                $counts['financial_receiving']++;

            }

        }



        return $counts;

    }



    private function accountRedirect(Request $request, string $tab, ?string $sec = null, bool $keepEdit = false, ?string $editKey = null)

    {

        $params = array_filter(['tab' => $tab, 'sec' => $sec]);

        if ($keepEdit) {

            $params['edit'] = $editKey ?? $request->input('redirect_sec') ?? $sec ?? 1;

        } elseif ($request->filled('edit')) {

            $params['edit'] = $request->input('edit');

        }



        return redirect()->route('maint.account', $params);

    }



    private function accountRedirectForDocumentType(Request $request, string $documentType, bool $keepEdit = false)

    {

        $sectionKey = $this->sectionKeyForType($documentType);



        return match ($sectionKey) {

            'address' => $this->accountRedirect($request, 'company', 'address', $keepEdit, $documentType),

            'business' => $this->accountRedirect($request, 'company', 'legal', $keepEdit, $documentType),

            'financial' => $this->accountRedirect($request, 'financials', 'receiving', $keepEdit, $documentType),

            'operations' => $this->accountRedirect($request, 'trade', $documentType, $keepEdit, $documentType),

            default => $this->accountRedirect($request, 'company', 'legal', $keepEdit, $documentType),

        };

    }



    private function sectionKeyForType(string $type): string

    {

        foreach (config('maintenance_compliance_documents.sections', []) as $sectionKey => $section) {

            if (isset($section['documents'][$type])) {

                return $sectionKey;

            }

        }



        return 'business';

    }



    /** @return array<string, array<string, mixed>> */

    private function flatDocumentDefinitions(): array

    {

        $flat = [];

        foreach (config('maintenance_compliance_documents.sections', []) as $section) {

            foreach ($section['documents'] ?? [] as $key => $doc) {

                $flat[$key] = $doc;

            }

        }



        return $flat;

    }



    /** @return array<string, mixed>|null */

    private function documentDefinition(string $type): ?array

    {

        return $this->flatDocumentDefinitions()[$type] ?? null;

    }

}

