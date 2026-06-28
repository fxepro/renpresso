<?php

namespace App\Models;

use App\Support\CrossBorderPayout;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasUuids, Notifiable, SoftDeletes;

    protected $keyType  = 'string';
    public    $incrementing = false;

    protected $fillable = [
        'first_name', 'last_name', 'email', 'phone', 'password',
        'role', 'home_country', 'home_currency', 'locale', 'timezone',
        'kyc_verified', 'kyc_verified_at',
        'kyc_status', 'kyc_legal_name', 'kyc_first_name', 'kyc_middle_name', 'kyc_last_name', 'kyc_name_suffix',
        'kyc_date_of_birth',
        'kyc_address_line1', 'kyc_address_line2', 'kyc_city', 'kyc_region', 'kyc_postal_code', 'kyc_address_country',
        'kyc_id_document_path', 'kyc_submitted_at', 'kyc_rejection_reason',
        'business_legal_name', 'business_entity_type', 'business_ein',
        'business_address_line1', 'business_address_line2', 'business_city', 'business_region',
        'business_postal_code', 'business_address_country', 'business_state_of_formation',
        'use_business_entity_in_lease',
        'landlord_account_status', 'portfolio_committed_units', 'portfolio_activation_fee_minor',
        'portfolio_activation_units', 'portfolio_activation_paid_at',
        'billing_company_name', 'billing_line1', 'billing_line2', 'billing_city', 'billing_region',
        'billing_postal_code', 'billing_country', 'billing_same_as_id_address',
        'stripe_customer_id', 'billing_pm_brand', 'billing_pm_last4',
        'landlord_payment_preferences',
        'default_multi_unit_capacity',
    ];

    protected $hidden = ['password', 'remember_token', 'kyc_id_document_path'];

    protected function casts(): array
    {
        return [
            'email_verified_at'   => 'datetime',
            'kyc_verified_at'     => 'datetime',
            'kyc_date_of_birth'   => 'date',
            'kyc_submitted_at'              => 'datetime',
            'portfolio_activation_paid_at'  => 'datetime',
            'password'                      => 'hashed',
            'kyc_verified'                 => 'boolean',
            'billing_same_as_id_address'     => 'boolean',
            'use_business_entity_in_lease'   => 'boolean',
            'landlord_payment_preferences'   => 'array',
        ];
    }

    /** @return array{subscription_method: string, tenant_payment_methods: list<string>} */
    public function landlordPaymentPreferences(): array
    {
        $defaults = [
            'subscription_method'      => 'card',
            'tenant_payment_methods'   => PlatformSetting::DEFAULT_PAYMENT_METHODS,
        ];

        $stored = $this->landlord_payment_preferences;
        if (! is_array($stored)) {
            return $defaults;
        }

        return array_merge($defaults, $stored);
    }

    public function properties() { return $this->hasMany(Property::class, 'landlord_id'); }

    public function landlordProfile(): HasOne
    {
        return $this->hasOne(LandlordProfile::class);
    }

    public function maintenanceInvoicesReceived(): HasMany
    {
        return $this->hasMany(MaintenanceInvoice::class, 'landlord_id');
    }
    public function leases()     { return $this->hasMany(Lease::class, 'tenant_id'); }
    public function payoutAccounts() { return $this->hasMany(LandlordPayoutAccount::class); }
    public function landlordPaymentMethods() { return $this->hasMany(LandlordPaymentMethod::class); }
    public function tenantPaymentMethods() { return $this->hasMany(TenantPaymentMethod::class); }

    /** @deprecated Prefer engagedMaintenanceTeams(); kept for legacy invite flows. */
    public function linkedMaintenanceStaff(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'landlord_maintenance_staff', 'landlord_id', 'staff_id')->withTimestamps();
    }

    /** @deprecated Prefer engagedMaintenanceTeams() on landlord / ownedMaintenanceTeam on staff. */
    public function linkedLandlords(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'landlord_maintenance_staff', 'staff_id', 'landlord_id')->withTimestamps();
    }

    /** Maintenance teams this landlord has engaged (hired). */
    public function engagedMaintenanceTeams(): BelongsToMany
    {
        return $this->belongsToMany(MaintenanceTeam::class, 'landlord_maintenance_team', 'landlord_id', 'maintenance_team_id')->withTimestamps();
    }

    public function engagedCleaningTeams(): BelongsToMany
    {
        return $this->belongsToMany(CleaningTeam::class, 'landlord_cleaning_team', 'landlord_id', 'cleaning_team_id')->withTimestamps();
    }

    public function ownedMaintenanceTeam(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(MaintenanceTeam::class, 'owner_id');
    }

    public function ownedCleaningTeam(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(CleaningTeam::class, 'owner_id');
    }

    public function maintenanceStaffInvites(): HasMany
    {
        return $this->hasMany(MaintenanceStaffInvite::class, 'landlord_id');
    }

    public function cleaningStaffInvites(): HasMany
    {
        return $this->hasMany(CleaningStaffInvite::class, 'landlord_id');
    }

    public function isLandlord(): bool { return $this->role === 'landlord'; }

    public function isLandlordAccountActive(): bool
    {
        return ! $this->isLandlord() || $this->landlord_account_status === 'active';
    }

    public function needsPortfolioActivation(): bool
    {
        return $this->isLandlord() && ! $this->isLandlordAccountActive();
    }

    /** @return array{first: ?string, middle: ?string, last: ?string, suffix: ?string} */
    public function kycNamePartsForForm(): array
    {
        if (filled($this->kyc_first_name) || filled($this->kyc_last_name)) {
            return [
                'first'  => $this->kyc_first_name,
                'middle' => $this->kyc_middle_name,
                'last'   => $this->kyc_last_name,
                'suffix' => $this->kyc_name_suffix,
            ];
        }

        $parsed = \App\Support\KycLegalName::parseFullName(
            (string) ($this->kyc_legal_name ?: $this->fullName())
        );

        if (! filled($parsed['last']) && filled($this->last_name)) {
            $parsed['last'] = $this->last_name;
        }
        if (! filled($parsed['first']) && filled($this->first_name)) {
            $parsed['first'] = $this->first_name;
        }

        return $parsed;
    }

    public function formattedKycLegalName(): string
    {
        if (filled($this->kyc_first_name) && filled($this->kyc_last_name)) {
            return \App\Support\KycLegalName::build(
                $this->kyc_first_name,
                $this->kyc_middle_name,
                $this->kyc_last_name,
                $this->kyc_name_suffix,
            );
        }

        $legacy = trim((string) ($this->kyc_legal_name ?? ''));

        return $legacy !== '' ? $legacy : $this->fullName();
    }

    public function syncKycLegalNameFromParts(): void
    {
        $this->kyc_legal_name = \App\Support\KycLegalName::build(
            $this->kyc_first_name,
            $this->kyc_middle_name,
            $this->kyc_last_name,
            $this->kyc_name_suffix,
        );
    }
    public function isTenant(): bool   { return $this->role === 'tenant'; }
    public function isMaintenance(): bool { return $this->role === 'maintenance'; }
    public function isCleaning(): bool { return $this->role === 'cleaning'; }
    public function isAdmin(): bool    { return $this->role === 'admin'; }
    public function fullName(): string { return "{$this->first_name} {$this->last_name}"; }

    public function hasPayoutAccountForCountry(string $countryCode, ?string $purpose = null): bool
    {
        $query = $this->payoutAccounts()->where('country_code', strtoupper($countryCode));
        if ($purpose !== null) {
            $query->where('purpose', $purpose);
        }

        return $query->exists();
    }

    /** Human-readable blocker when payout setup is incomplete for a property. */
    public function missingPayoutSetupForProperty(Property $property): ?string
    {
        if (! $this->isLandlord()) {
            return null;
        }

        $propertyCountry = strtoupper($property->country_code);
        $homeCountry     = strtoupper($this->home_country ?? 'US');

        if (CrossBorderPayout::requiresLocalCollectionAccount($propertyCountry)
            && ! $this->hasPayoutAccountForCountry($propertyCountry, LandlordPayoutAccount::PURPOSE_COLLECTION)) {
            return 'Add a local collection bank account in '.$propertyCountry.' (Account → Payouts) before creating a lease. Rent is collected in-country; we do not wire directly to your home bank.';
        }

        if (CrossBorderPayout::requiresRepatriationAccount($propertyCountry, $homeCountry)
            && ! $this->hasPayoutAccountForCountry($homeCountry, LandlordPayoutAccount::PURPOSE_REPATRIATION)) {
            return 'Add a home repatriation bank account in '.$homeCountry.' (Account → Payouts). Move funds from your local balance yourself — direct cross-border wires are not handled here.';
        }

        return null;
    }

    /** Primary lease for renter UI (MVP: first active lease). */
    public function primaryActiveLease(): ?Lease
    {
        return $this->leases()
            ->where('status', 'active')
            ->with(['property.landlord', 'mandates', 'payments' => fn ($q) => $q->orderByDesc('due_date')])
            ->orderByDesc('activated_at')
            ->orderByDesc('start_date')
            ->first();
    }

    /** KYC workflow: none → pending → verified | rejected */
    public function kycWorkflowStatus(): string
    {
        if ($this->kyc_verified) {
            return 'verified';
        }

        return $this->kyc_status ?? 'none';
    }

    public function landlordKycEditable(): bool
    {
        return $this->isLandlord() && ! in_array($this->kycWorkflowStatus(), ['pending', 'verified'], true);
    }

    /**
     * Maintenance director ID workflow — verified only when flag, status, and file all match.
     * Ignores stale kyc_verified rows from seed/data without an uploaded ID.
     */
    public function maintenanceDirectorIdentityStatus(): string
    {
        if (! $this->isMaintenance()) {
            return $this->kycWorkflowStatus();
        }

        if ($this->kyc_verified
            && $this->kyc_id_document_path
            && ($this->kyc_status ?? 'none') === 'verified') {
            return 'verified';
        }

        if (($this->kyc_status ?? 'none') === 'pending' && $this->kyc_id_document_path) {
            return 'pending';
        }

        if (($this->kyc_status ?? 'none') === 'rejected') {
            return 'rejected';
        }

        return 'none';
    }

    public function maintenanceDirectorIdentityVerified(): bool
    {
        return $this->maintenanceDirectorIdentityStatus() === 'verified';
    }

    /** Maintenance company director — same KYC fields as landlord. */
    public function maintenanceKycEditable(): bool
    {
        return $this->isMaintenance()
            && ! in_array($this->maintenanceDirectorIdentityStatus(), ['pending', 'verified'], true);
    }

    /** Person on government ID — not the maintenance company name. */
    public function maintenanceLegalOwnerName(?MaintenanceTeam $team = null): string
    {
        if (! $this->isMaintenance()) {
            return trim($this->kyc_legal_name ?: $this->fullName());
        }

        $team ??= $this->ownedMaintenanceTeam;
        $stored = trim((string) ($this->kyc_legal_name ?? ''));

        if ($stored !== '' && ! $this->kycLegalNameIsCompanyName($stored, $team)) {
            return $stored;
        }

        return $this->fullName();
    }

    /** Default for the legal-name field when editing director ID. */
    public function maintenanceLegalOwnerNameForForm(?MaintenanceTeam $team = null): string
    {
        $team ??= $this->ownedMaintenanceTeam;
        $stored = trim((string) ($this->kyc_legal_name ?? ''));

        if ($stored !== '' && ! $this->kycLegalNameIsCompanyName($stored, $team)) {
            return $stored;
        }

        return $this->fullName();
    }

    public function maintenanceHasDirectorLegalNameOnFile(?MaintenanceTeam $team = null): bool
    {
        $stored = trim((string) ($this->kyc_legal_name ?? ''));

        return $stored !== ''
            && ! $this->kycLegalNameIsCompanyName($stored, $team ?? $this->ownedMaintenanceTeam);
    }

    public function kycLegalNameIsCompanyName(string $legalName, ?MaintenanceTeam $team): bool
    {
        if (! $team) {
            return false;
        }

        $normalize = static fn (string $value): string => strtolower(
            preg_replace('/[^a-z0-9]+/i', ' ', $value) ?? ''
        );

        $legal = trim($normalize($legalName));
        $company = trim($normalize($team->name));

        if ($legal === '' || $company === '') {
            return false;
        }

        if ($legal === $company) {
            return true;
        }

        if (str_contains($legal, $company) || str_contains($company, $legal)) {
            return true;
        }

        return false;
    }

    /** Tenant profile / ID on file — editable unless ID is pending review. */
    public function tenantProfileEditable(): bool
    {
        return $this->isTenant() && $this->kycWorkflowStatus() !== 'pending';
    }

    public function formattedIdAddress(): string
    {
        return $this->formatAddressParts(
            $this->kyc_address_line1,
            $this->kyc_address_line2,
            $this->kyc_city,
            $this->kyc_region,
            $this->kyc_postal_code,
            $this->kyc_address_country,
        );
    }

    public function businessEntityReadyForLease(): bool
    {
        return filled($this->business_legal_name)
            && filled($this->business_address_line1)
            && filled($this->business_city)
            && filled($this->business_address_country);
    }

    public function formattedBusinessAddress(): string
    {
        return $this->formatAddressParts(
            $this->business_address_line1,
            $this->business_address_line2,
            $this->business_city,
            $this->business_region,
            $this->business_postal_code,
            $this->business_address_country,
        );
    }

    /** Landlord party name for lease merge — personal ID name or business entity. */
    public function leasePartyName(?bool $useBusiness = null): string
    {
        if ($this->shouldUseBusinessEntityInLease($useBusiness) && filled($this->business_legal_name)) {
            return $this->business_legal_name;
        }

        return $this->formattedKycLegalName();
    }

    /** Landlord notice / signature address for lease merge. */
    public function leasePartyAddress(?bool $useBusiness = null): string
    {
        if ($this->shouldUseBusinessEntityInLease($useBusiness) && filled($this->business_address_line1)) {
            return $this->formattedBusinessAddress();
        }

        $id = $this->formattedIdAddress();

        if ($id !== '—') {
            return $id;
        }

        return $this->formattedBillingAddress();
    }

    public function shouldUseBusinessEntityInLease(?bool $override = null): bool
    {
        if ($override !== null) {
            return $override && $this->businessEntityReadyForLease();
        }

        return (bool) $this->use_business_entity_in_lease && $this->businessEntityReadyForLease();
    }

    /** Rent receipts / card billing — separate from landlord platform billing fields. */
    public function billingSameAsIdAddress(): bool
    {
        if ($this->billing_same_as_id_address === null) {
            return ! filled($this->billing_line1);
        }

        return (bool) $this->billing_same_as_id_address;
    }

    public function formattedBillingAddress(): string
    {
        if ($this->billingSameAsIdAddress()) {
            return $this->formattedIdAddress();
        }

        return $this->formatAddressParts(
            $this->billing_line1,
            $this->billing_line2,
            $this->billing_city,
            $this->billing_region,
            $this->billing_postal_code,
            $this->billing_country,
        );
    }

    public function hasRentBillingAddress(): bool
    {
        return $this->formattedBillingAddress() !== '—';
    }

    protected function formatAddressParts(
        ?string $line1,
        ?string $line2,
        ?string $city,
        ?string $region,
        ?string $postal,
        ?string $country,
    ): string {
        $parts = array_filter([
            $line1,
            $line2,
            trim(($city ?? '').($region ? ', '.$region : '')),
            $postal,
            $country ? strtoupper($country) : null,
        ]);

        return $parts !== [] ? implode(', ', $parts) : '—';
    }

    /** Identity fields safe to show on a maintenance team public profile. */
    public function publicKycProfile(): array
    {
        $status = $this->kycWorkflowStatus();

        return [
            'status'           => $status,
            'verified'         => $this->kyc_verified,
            'verified_at'      => $this->kyc_verified_at?->format('M Y'),
            'legal_name'       => $this->kyc_legal_name,
            'city'             => $this->kyc_city,
            'region'           => $this->kyc_region,
            'country_code'     => $this->kyc_address_country ? strtoupper($this->kyc_address_country) : null,
        ];
    }

    public function maintenanceTeamReviews(): HasMany
    {
        return $this->hasMany(MaintenanceTeamReview::class, 'landlord_id');
    }
}
