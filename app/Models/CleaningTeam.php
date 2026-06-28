<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class CleaningTeam extends Model
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
        return $this->belongsToMany(User::class, 'landlord_cleaning_team', 'cleaning_team_id', 'landlord_id')->withTimestamps();
    }

    public function engagedLandlordIds(): Collection
    {
        return $this->engagedLandlords()->pluck('users.id');
    }

    public function isEngagedWithLandlord(string $landlordId): bool
    {
        return $this->engagedLandlordIds()->contains($landlordId);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(CleaningTeamReview::class, 'cleaning_team_id');
    }

    public function cities(): HasMany
    {
        return $this->hasMany(CleaningTeamCity::class, 'cleaning_team_id');
    }

    public function syncPrimaryCityFromRecord(): void
    {
        $primary = $this->cities()->where('is_primary', true)->first()
            ?? $this->cities()->orderBy('city')->first();

        if ($primary) {
            $this->update([
                'city'         => $primary->city,
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
                    $q->whereRaw('LOWER(cleaning_teams.city) = ?', [$city])
                        ->where('cleaning_teams.country_code', $countryCode);
                })->orWhereHas('cities', function ($q) use ($city, $countryCode) {
                    $q->whereRaw('LOWER(city) = ?', [$city])
                        ->where('country_code', $countryCode);
                });
            }
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

    public function serviceList(): Collection
    {
        return collect($this->normalizedServices());
    }
}
