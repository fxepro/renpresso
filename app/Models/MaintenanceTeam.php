<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class MaintenanceTeam extends Model
{
    use HasUuids, SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'owner_id',
        'name',
        'description',
        'city',
        'country_code',
        'phone',
        'services',
        'is_listed',
    ];

    protected function casts(): array
    {
        return [
            'is_listed' => 'boolean',
            'services'  => 'array',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function engagedLandlords(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'landlord_maintenance_team', 'maintenance_team_id', 'landlord_id')->withTimestamps();
    }

    /** Landlord user IDs (qualified for pivot joins — avoids ambiguous `id`). */
    public function engagedLandlordIds(): Collection
    {
        return $this->engagedLandlords()->pluck('users.id');
    }

    public function isEngagedWithLandlord(string $landlordId): bool
    {
        return $this->engagedLandlordIds()->contains($landlordId);
    }

    /**
     * Properties a team may bill a landlord for: landlord portfolio in this team's
     * operating cities, plus any property tied to assigned maintenance work.
     */
    public function billablePropertiesForLandlord(string $landlordId): Collection
    {
        if (! $this->isEngagedWithLandlord($landlordId)) {
            return collect();
        }

        $fromWork = $this->assignedMaintenanceRequestsQuery()
            ->whereHas('lease.property', fn ($q) => $q->where('landlord_id', $landlordId))
            ->with('lease.property:id,landlord_id')
            ->get()
            ->pluck('lease.property_id')
            ->filter()
            ->unique();

        $fromPortfolio = Property::query()
            ->where('landlord_id', $landlordId)
            ->get(['id', 'name', 'address_line1', 'city', 'country_code', 'landlord_id'])
            ->filter(fn (Property $p) => $this->coversCityCountry($p->city, $p->country_code))
            ->pluck('id');

        $ids = $fromWork->merge($fromPortfolio)->unique()->values();

        if ($ids->isEmpty()) {
            return collect();
        }

        return Property::query()
            ->whereIn('id', $ids)
            ->orderBy('name')
            ->orderBy('address_line1')
            ->get(['id', 'name', 'address_line1', 'city', 'country_code', 'landlord_id']);
    }

    /** Maintenance requests for this landlord that are assigned to this team. */
    public function billableMaintenanceRequestsForLandlord(string $landlordId): Collection
    {
        if (! $this->isEngagedWithLandlord($landlordId)) {
            return collect();
        }

        return $this->assignedMaintenanceRequestsQuery()
            ->whereHas('lease.property', fn ($q) => $q->where('landlord_id', $landlordId))
            ->with('lease.property:id,name,city')
            ->orderByDesc('created_at')
            ->limit(100)
            ->get();
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(MaintenanceTeamReview::class, 'maintenance_team_id');
    }

    public function cities(): HasMany
    {
        return $this->hasMany(MaintenanceTeamCity::class, 'maintenance_team_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(MaintenanceInvoice::class, 'maintenance_team_id');
    }

    public function paymentsReceived(): HasMany
    {
        return $this->hasMany(MaintenancePaymentReceived::class, 'maintenance_team_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(MaintenanceTeamDocument::class, 'maintenance_team_id');
    }

    /** @return array<string, MaintenanceTeamDocument> */
    public function documentsByType(): array
    {
        return $this->documents->keyBy('document_type')->all();
    }

    /** @return list<string> */
    public function requiredComplianceDocumentTypes(): array
    {
        $types = [];
        foreach (config('maintenance_compliance_documents.sections', []) as $section) {
            foreach ($section['documents'] ?? [] as $key => $doc) {
                if ($doc['required'] ?? true) {
                    $types[] = $key;
                }
            }
        }

        return $types;
    }

    public function complianceSummary(): array
    {
        $required = $this->requiredComplianceDocumentTypes();
        $byType = $this->documentsByType();
        $uploaded = 0;
        $verified = 0;

        foreach ($required as $type) {
            $record = $byType[$type] ?? null;
            if ($record?->file_path) {
                $uploaded++;
            }
            if ($record?->status === 'verified') {
                $verified++;
            }
        }

        $total = count($required);

        return [
            'required_total'   => $total,
            'uploaded'         => $uploaded,
            'verified'         => $verified,
            'complete'         => $total > 0 && $uploaded === $total,
            'all_verified'     => $total > 0 && $verified === $total,
        ];
    }

    public function syncPrimaryCityFromRecord(): void
    {
        $primary = $this->cities()->where('is_primary', true)->first()
            ?? $this->cities()->orderBy('city')->first();

        if ($primary) {
            $this->update([
                'city' => $primary->city,
                'country_code' => $primary->country_code,
            ]);
        }
    }

    public function locationLabel(): string
    {
        return $this->city.', '.strtoupper($this->country_code);
    }

    public function coversCityCountry(string $city, string $countryCode): bool
    {
        $city = strtolower(trim($city));
        $countryCode = strtoupper(trim($countryCode));

        if ($city === '' || strlen($countryCode) !== 2) {
            return false;
        }

        if (strtolower(trim($this->city)) === $city && strtoupper($this->country_code) === $countryCode) {
            return true;
        }

        return $this->cities()
            ->whereRaw('LOWER(city) = ?', [$city])
            ->where('country_code', $countryCode)
            ->exists();
    }

    /** @param  iterable<int, array{city: string, country_code: string}>  $locations */
    public function scopeMatchingPropertyLocations(Builder $query, iterable $locations): Builder
    {
        return $query->where(function ($outer) use ($locations) {
            foreach ($locations as $loc) {
                $city = strtolower(trim($loc['city'] ?? ''));
                $countryCode = strtoupper(trim($loc['country_code'] ?? ''));
                if ($city === '' || strlen($countryCode) !== 2) {
                    continue;
                }

                $outer->orWhere(function ($q) use ($city, $countryCode) {
                    $q->whereRaw('LOWER(maintenance_teams.city) = ?', [$city])
                        ->where('maintenance_teams.country_code', $countryCode);
                })->orWhereHas('cities', function ($q) use ($city, $countryCode) {
                    $q->whereRaw('LOWER(city) = ?', [$city])
                        ->where('country_code', $countryCode);
                });
            }
        });
    }

    /** Requests assigned to this team by a landlord (real assignment — not seed shortcuts). */
    public function assignedMaintenanceRequestsQuery(): Builder
    {
        return MaintenanceRequest::query()
            ->where(function ($q) {
                $q->where('maintenance_team_id', $this->id)
                    ->orWhere('assignee_id', $this->owner_id);
            });
    }

    public function averageRating(): ?float
    {
        $avg = $this->reviews_avg_rating ?? $this->reviews()->avg('rating');

        return $avg !== null ? round((float) $avg, 1) : null;
    }

    public function reviewCount(): int
    {
        return (int) ($this->reviews_count ?? $this->reviews()->count());
    }

    /** @return list<string> */
    public function normalizedServices(): array
    {
        $services = $this->services;

        while (is_string($services)) {
            $decoded = json_decode($services, true);
            if (! is_array($decoded)) {
                break;
            }
            $services = $decoded;
        }

        if (! is_array($services)) {
            return [];
        }

        return array_values(array_filter($services, fn ($s) => is_string($s) && $s !== ''));
    }

    /** @return Collection<int, string> */
    public function serviceList(): Collection
    {
        return collect($this->normalizedServices());
    }
}
