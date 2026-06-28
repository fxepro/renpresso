<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Property extends Model implements HasMedia
{
    use HasFactory, HasUuids, InteractsWithMedia, SoftDeletes;

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('photos');
        $this->addMediaCollection('videos');
    }

    protected $keyType  = 'string';
    public    $incrementing = false;

    protected $fillable = [
        'landlord_id', 'name', 'address_line1', 'address_line2',
        'city', 'state_province', 'postal_code', 'country_code',
        'currency_code', 'processor_slug', 'type', 'occupancy_mode', 'unit_capacity', 'unit_slots_meta',
        'bedrooms', 'status', 'rental_mode', 'listing_visibility',
        'sublet_allowed', 'sublet_bg_check_required', 'sublet_landlord_approval_required',
        'base_rent_minor_units', 'rent_minor_units', 'rent_charge_lines',
    ];

    protected function casts(): array
    {
        return [
            'rent_charge_lines'                => 'array',
            'unit_slots_meta'                  => 'array',
            'sublet_allowed'                   => 'boolean',
            'sublet_bg_check_required'         => 'boolean',
            'sublet_landlord_approval_required'=> 'boolean',
        ];
    }

    /**
     * Photos tagged for a licensed slot (unit_seq), or building-level photos when $unitSeq is null.
     *
     * @return \Illuminate\Support\Collection<int, \Spatie\MediaLibrary\MediaCollections\Models\Media>
     */
    public function photosForUnit(?int $unitSeq)
    {
        return $this->getMedia('photos')->filter(function ($m) use ($unitSeq) {
            $u = $m->getCustomProperty('unit_seq');
            if ($unitSeq === null) {
                return $u === null || $u === '' || $u === false;
            }

            return (int) $u === (int) $unitSeq;
        })->values();
    }

    public function activeLeaseForUnitSeq(int $unitSeq): ?Lease
    {
        return $this->leases()
            ->where('status', 'active')
            ->where('unit_seq', $unitSeq)
            ->with('tenant')
            ->first();
    }

    /** @param \Illuminate\Database\Eloquent\Builder<Property> $query */
    public function scopePublicListings($query)
    {
        return $query->where('listing_visibility', 'public');
    }

    /** @param \Illuminate\Database\Eloquent\Builder<Property> $query */
    public function scopeLongTermRentals($query)
    {
        return $query->where('rental_mode', 'long_term');
    }

    /** @param \Illuminate\Database\Eloquent\Builder<Property> $query */
    public function scopeShortTermRentals($query)
    {
        return $query->where('rental_mode', 'short_term');
    }

    public function landlord()   { return $this->belongsTo(User::class, 'landlord_id'); }
    public function leases()          { return $this->hasMany(Lease::class); }
    public function subLeases()       { return $this->hasManyThrough(SubLease::class, Lease::class, 'property_id', 'parent_lease_id'); }
    public function applications()    { return $this->hasMany(\App\Models\Application::class); }
    public function backgroundChecks(){ return $this->hasMany(\App\Models\BackgroundCheck::class); }
    public function documents()  { return $this->morphMany(Document::class, 'documentable'); }
    public function messages()   { return $this->hasMany(Message::class); }

    public function isMultiUnit(): bool
    {
        return $this->occupancy_mode === 'multi';
    }

    public function activeLeases()
    {
        return $this->leases()->where('status', 'active')->orderBy('unit_seq')->orderBy('unit_label');
    }

    public function activeLeaseCount(): int
    {
        return $this->leases()->where('status', 'active')->count();
    }

    /** Licensing: cannot add another active lease when at or over this capacity. */
    public function isAtLicensedUnitCapacity(): bool
    {
        if (! $this->isMultiUnit()) {
            return $this->activeLeaseCount() >= 1;
        }
        if ($this->unit_capacity === null) {
            return false;
        }

        return $this->activeLeaseCount() >= (int) $this->unit_capacity;
    }

    /** Next internal unit sequence for a new lease on a multi-unit property (1-based). */
    public function nextUnitSeq(): int
    {
        $max = (int) $this->leases()->max('unit_seq');

        return $max + 1;
    }

    /** Leases eligible for building notices and tenant messaging. */
    public function messagingLeases()
    {
        return $this->leases()->whereIn('status', ['active', 'draft'])->with('tenant')->orderBy('unit_seq')->orderBy('unit_label');
    }

    public function occupiedUnitCount(): int
    {
        return $this->activeLeaseCount();
    }

    public function occupancySummary(): string
    {
        if (! $this->isMultiUnit()) {
            return $this->displayStatusLabel();
        }

        $occupied = $this->occupiedUnitCount();
        if ($this->unit_capacity) {
            return "{$occupied}/{$this->unit_capacity} units";
        }

        return $occupied === 1 ? '1 unit occupied' : "{$occupied} units occupied";
    }

    public function processorConfig(): array
    {
        return config("countries.{$this->country_code}", []);
    }

    /** Tenant payment rail label — from config/countries.php via country_code (not a DB column). */
    public function paymentMethodLabel(): string
    {
        return (string) ($this->processorConfig()['method'] ?? '—');
    }

    /** Payment processor slug — stored on properties.processor_slug. */
    public function paymentProcessorLabel(): string
    {
        return $this->processor_slug ?: (string) ($this->processorConfig()['processor'] ?? '—');
    }

    public function isOccupied(): bool
    {
        return $this->activeLeaseCount() > 0;
    }

    /** UI label for properties.status (synced from active leases unless archived). */
    public function displayStatusLabel(): string
    {
        return match ($this->status) {
            'active'   => 'Active',
            'archived' => 'Archived',
            default    => 'Vacant',
        };
    }

    public function displayStatusBadgeClass(): string
    {
        return match ($this->status) {
            'active'   => 'badge-green',
            'archived' => 'badge-grey',
            default    => 'badge-grey',
        };
    }

    /** Properties list: single = 1; multi = licensed unit_capacity. */
    public function portfolioUnitsLabel(): string
    {
        if (! $this->isMultiUnit()) {
            return '1';
        }

        return $this->unit_capacity !== null
            ? (string) (int) $this->unit_capacity
            : '—';
    }

    /** List status: single = Active/Vacant; multi = 4/12 (active). */
    public function portfolioStatusLabel(): string
    {
        if (! $this->isMultiUnit()) {
            return $this->displayStatusLabel();
        }

        $active = $this->activeLeaseCount();
        $total = $this->unit_capacity;
        $word = strtolower($this->displayStatusLabel());

        if ($total === null) {
            return $active > 0 ? "{$active} ({$word})" : "0 ({$word})";
        }

        return "{$active}/{$total} ({$word})";
    }

    /** Keep properties.status aligned with active lease count (active / vacant / archived). */
    public function syncStatusFromLeases(): void
    {
        if ($this->status === 'archived') {
            return;
        }

        $next = $this->activeLeaseCount() > 0 ? 'active' : 'vacant';
        if ($this->status !== $next) {
            $this->status = $next;
            $this->saveQuietly();
        }
    }

    /** @return array<string, mixed> Panel / list display payload (single source of truth). */
    public function displayPayload(): array
    {
        return [
            'status_db'           => $this->status,
            'status_label'        => $this->displayStatusLabel(),
            'portfolio_status_label' => $this->portfolioStatusLabel(),
            'status_badge'        => $this->displayStatusBadgeClass(),
            'active_lease_count'  => $this->activeLeaseCount(),
            'payment_processor'   => $this->paymentProcessorLabel(),
            'payment_method'      => $this->paymentMethodLabel(),
            'rent_minor'          => $this->displayMonthlyRentMinor(),
            'rent_display'        => \App\Support\CurrencyDisplay::formatMinor(
                $this->displayMonthlyRentMinor(),
                $this->currency_code
            ),
        ];
    }

    /** Default dynamic charge rows when none saved yet. */
    public static function defaultRentChargeLines(): array
    {
        return [
            ['key' => 'trash', 'label' => 'Trash', 'amount_minor_units' => 0],
            ['key' => 'water_sewer', 'label' => 'Water / sewer', 'amount_minor_units' => 0],
        ];
    }

    /** @return list<array{key: string, label: string, amount_minor_units: int}> */
    public function normalizedRentChargeLines(): array
    {
        $lines = $this->rent_charge_lines;
        if (! is_array($lines) || $lines === []) {
            return self::defaultRentChargeLines();
        }

        return array_values(array_map(function ($line) {
            $label = trim((string) ($line['label'] ?? 'Charge'));
            $key = trim((string) ($line['key'] ?? ''));
            if ($key === '') {
                $key = strtolower(preg_replace('/[^a-z0-9]+/i', '_', $label) ?? 'charge');
                $key = trim($key, '_') ?: 'charge';
            }

            return [
                'key'                => $key,
                'label'              => $label,
                'amount_minor_units' => max(0, (int) ($line['amount_minor_units'] ?? 0)),
            ];
        }, $lines));
    }

    public function calculatedRentTotalMinorUnits(): int
    {
        $total = max(0, (int) ($this->base_rent_minor_units ?? 0));
        foreach ($this->normalizedRentChargeLines() as $line) {
            $total += $line['amount_minor_units'];
        }

        return $total;
    }

    public function syncRentTotal(): void
    {
        $total = $this->calculatedRentTotalMinorUnits();
        $this->rent_minor_units = $total > 0 ? $total : null;
    }

    /** Copy active lease rent onto the property schedule (single-unit). */
    public function syncRentScheduleFromActiveLeases(): void
    {
        if ($this->isMultiUnit()) {
            $total = $this->activeLeasesRentMinorTotal();
            if ($total <= 0) {
                return;
            }
            $this->base_rent_minor_units = $total;
            $this->rent_charge_lines = null;
            $this->syncRentTotal();
            $this->saveQuietly();

            return;
        }

        $lease = $this->relationLoaded('leases')
            ? $this->leases->where('status', 'active')->first()
            : $this->leases()->where('status', 'active')->first();

        if (! $lease || (int) $lease->rent_minor_units <= 0) {
            return;
        }

        $this->base_rent_minor_units = (int) $lease->rent_minor_units;
        $this->rent_charge_lines = null;
        $this->syncRentTotal();
        $this->saveQuietly();
    }

    /** Apply property schedule total to active lease(s) (single-unit only). */
    public function syncActiveLeasesFromRentSchedule(): void
    {
        if ($this->isMultiUnit()) {
            return;
        }

        $total = (int) ($this->rent_minor_units ?? 0);
        if ($total <= 0) {
            return;
        }

        $this->leases()->where('status', 'active')->update(['rent_minor_units' => $total]);
    }

    public function formattedBaseRent(): ?string
    {
        if (! $this->base_rent_minor_units) {
            return null;
        }

        return number_format($this->base_rent_minor_units / 100, 2).' '.$this->currency_code;
    }

    public function formattedTotalRent(): ?string
    {
        if (! $this->rent_minor_units) {
            return null;
        }

        return number_format($this->rent_minor_units / 100, 2).' '.$this->currency_code;
    }

    /** Sum of monthly rent on all active leases (minor units). */
    public function activeLeasesRentMinorTotal(): int
    {
        if ($this->relationLoaded('leases')) {
            return (int) $this->leases->where('status', 'active')->sum('rent_minor_units');
        }

        return (int) $this->leases()->where('status', 'active')->sum('rent_minor_units');
    }

    /** Portfolio list: property schedule when set, else active lease total. */
    public function displayMonthlyRentMinor(): ?int
    {
        $scheduled = (int) ($this->rent_minor_units ?? 0);
        if ($scheduled > 0) {
            return $scheduled;
        }

        $leased = $this->activeLeasesRentMinorTotal();

        return $leased > 0 ? $leased : null;
    }

    public function displayMonthlyRentFromLeases(): bool
    {
        return $this->activeLeasesRentMinorTotal() > 0;
    }

    /** @return array{base_rent: string, charges: list<array{label: string, amount: string}>, total_rent: string, currency: string} */
    public function rentScheduleForDisplay(): array
    {
        $currency = $this->currency_code;
        $fmt = fn (?int $minor) => $minor > 0
            ? number_format($minor / 100, 2).' '.$currency
            : '—';

        return [
            'base_rent'   => $fmt((int) ($this->base_rent_minor_units ?? 0)),
            'charges'     => array_map(fn ($line) => [
                'label'  => $line['label'],
                'amount' => $fmt($line['amount_minor_units']),
            ], $this->normalizedRentChargeLines()),
            'total_rent'  => $fmt((int) ($this->rent_minor_units ?? 0)),
            'currency'    => $currency,
        ];
    }
}
